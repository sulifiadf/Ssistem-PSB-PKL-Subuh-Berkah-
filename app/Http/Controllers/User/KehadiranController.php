<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\kehadiran;
use App\Models\User;
use App\Models\rombong;
use App\Models\Lapak;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\KehadiranToken;

class KehadiranController extends Controller
{
    // Konstanta waktu untuk konfirmasi
    const BATAS_JAM_URUTAN_1 = 13; // Urutan 1 bisa konfirmasi sampai jam 11:00
    const WINDOW_MENIT_KONFIRMASI = 30; // Urutan >1 punya window 30 menit

    public function konfirmasi(Request $request)
    {
        $userId = Auth::id();
        $status = strtolower(trim($request->input('status')));
        $today = Carbon::today();

        // Validasi status
        if (!in_array($status, ['masuk', 'libur'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Status tidak valid. Hanya boleh: masuk atau libur.'
            ], 422);
        }

        // Cek status konfirmasi menggunakan fungsi utama
        $statusKonfirmasi = $this->getStatusKonfirmasi($userId);
        
        if (!$statusKonfirmasi['bisa_konfirmasi']) {
            return response()->json([
                'success' => false,
                'message' => $statusKonfirmasi['pesan']
            ], 422);
        }

        // PERBAIKAN CSRF: Tambahkan validasi tambahan untuk keamanan
        try {
            // Gunakan cache lock untuk prevent race condition
            $lock = Cache::lock('kehadiran_lock_' . $userId, 10);
            
            if ($lock->get()) {
                // Update atau create kehadiran dengan query yang dioptimasi
                $kehadiran = DB::transaction(function () use ($userId, $today, $status) {
                    return kehadiran::create([
                        'user_id' => $userId,
                        'tanggal' => $today,
                        'status' => $status,
                        'waktu_konfirmasi' => Carbon::now()
                    ]);
                });

                // Clear cache terkait kehadiran
                $this->clearKehadiranCache($userId, $today);

                $lock->release();

                // Kirim WA di background (jika perlu)
                if (app()->environment('production')) {
                    dispatch(function () use ($userId, $status) {
                        \App\Services\NotifikasiKehadiran::kirimNotifikasiSetelahKonfirmasi($userId, $status);
                    })->afterResponse();
                }

                return redirect()->back()->with('success', "Kehadiran berhasil dikonfirmasi sebagai {$status}");
            }
        } catch (\Exception $e) {
            Log::error('Error konfirmasi kehadiran: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengkonfirmasi kehadiran.'
        ], 500);
    }

    /**
     * Fungsi utama untuk menentukan status konfirmasi user
     * Mengatur logika urutan dan waktu konfirmasi yang konsisten
     */
    public function getStatusKonfirmasi($userId)
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        // PRIORITAS TERTINGGI: Cek anggota SEMENTARA yang baru di-acc hari ini
        $userRombong = rombong::where('user_id', $userId)
            ->whereNotNull('lapak_id')
            ->first();
            
        if ($userRombong && $userRombong->jenis === 'sementara') {
            $isAnggotaSementaraBaru = $this->isAnggotaSementaraBaru($userRombong);
            
            if ($isAnggotaSementaraBaru) {
                // Hapus auto-libur yang mungkin sudah dibuat sebelumnya
                kehadiran::where('user_id', $userId)
                    ->whereDate('tanggal', $today)
                    ->where('status', 'libur')
                    ->delete();
                
                // Cek apakah sudah ada yang masuk di lapak ini
                $adaYangMasuk = $this->cekAdaYangMasukDiLapak($userRombong->lapak_id);
                
                if (!$adaYangMasuk) {
                    return [
                        'status' => 'konfirmasi_sekarang',
                        'bisa_konfirmasi' => true,
                        'pesan' => '🆕 Anggota Sementara Baru - Konfirmasi sekarang untuk merebut lapak! (Bebas batas waktu)',
                        'kehadiran' => null
                    ];
                } else {
                    return [
                        'status' => 'sudah_ada_yang_masuk',
                        'bisa_konfirmasi' => false,
                        'pesan' => 'Maaf, sudah ada yang masuk di lapak ini',
                        'kehadiran' => null
                    ];
                }
            }
        }
        
        // Cek apakah user sudah konfirmasi hari ini
        $kehadiranHariIni = kehadiran::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->first();
            
        if ($kehadiranHariIni) {
            // Cek apakah ini auto-libur berdasarkan waktu dan logika urutan
            $isAutoLibur = $this->isAutoLiburKehadiran($userId, $kehadiranHariIni);
            
            if ($isAutoLibur) {
                return [
                    'status' => 'sudah_libur',
                    'bisa_konfirmasi' => false,
                    'pesan' => $this->getAutoLiburMessage($userId),
                    'kehadiran' => $kehadiranHariIni
                ];
            }
            
            return [
                'status' => $kehadiranHariIni->status === 'masuk' ? 'sudah_masuk' : 'sudah_libur',
                'bisa_konfirmasi' => false,
                'pesan' => 'Sudah konfirmasi: ' . $kehadiranHariIni->status,
                'kehadiran' => $kehadiranHariIni
            ];
        }
        
        // Cek apakah user punya rombong di lapak
        if (!$userRombong) {
            $userRombong = rombong::where('user_id', $userId)
                ->whereNotNull('lapak_id')
                ->first();
        }
            
        if (!$userRombong) {
            return [
                'status' => 'belum_punya_urutan',
                'bisa_konfirmasi' => false,
                'pesan' => 'Belum memiliki urutan di lapak',
                'pesan' => 'Lengkapi profile Terlebih dahulu',
                'kehadiran' => null
            ];
        }
        
        // PRIORITAS KHUSUS: Anggota SEMENTARA yang baru di-acc hari ini
        // Mereka bisa langsung konfirmasi untuk "merebut" lapak (tanpa terikat batas waktu)
        if ($userRombong->jenis === 'sementara') {
            $isAnggotaSementaraBaru = $this->isAnggotaSementaraBaru($userRombong);
            
            if ($isAnggotaSementaraBaru) {
                // Cek apakah sudah ada yang masuk di lapak ini
                $adaYangMasuk = $this->cekAdaYangMasukDiLapak($userRombong->lapak_id);
                
                if (!$adaYangMasuk) {
                    return [
                        'status' => 'konfirmasi_sekarang',
                        'bisa_konfirmasi' => true,
                        'pesan' => '🆕 Anggota Sementara Baru - Konfirmasi sekarang untuk merebut lapak! (Bebas batas waktu)',
                        'kehadiran' => null
                    ];
                } else {
                    return [
                        'status' => 'sudah_ada_yang_masuk',
                        'bisa_konfirmasi' => false,
                        'pesan' => 'Maaf, sudah ada yang masuk di lapak ini',
                        'kehadiran' => null
                    ];
                }
            }
        }
        
        // Ambil semua rombong di lapak yang sama, diurutkan berdasarkan ID atau timestamp
        $allRombongsInLapak = rombong::where('lapak_id', $userRombong->lapak_id)
            ->orderBy('rombong_id', 'asc') // Asumsi yang pertama daftar adalah urutan 1
            ->get();
            
        // Cari posisi user dalam lapak
        $userPosition = 0;
        foreach ($allRombongsInLapak as $index => $rombong) {
            if ($rombong->user_id == $userId) {
                $userPosition = $index + 1; // Urutan dimulai dari 1
                break;
            }
        }
        
        if ($userPosition == 0) {
            return [
                'status' => 'belum_punya_urutan',
                'bisa_konfirmasi' => false,
                'pesan' => 'Tidak ditemukan dalam lapak',
                'kehadiran' => null
            ];
        }
        
        // Cek logika berdasarkan urutan
        if ($userPosition == 1) {
            return $this->getStatusUrutanPertama($userId, $userRombong, $now);
        } else {
            return $this->getStatusUrutanLanjutan($userId, $userRombong, $now, $today, $allRombongsInLapak, $userPosition);
        }
    }
    
    /**
     * Logika untuk urutan pertama (urutan 1)
     */
    private function getStatusUrutanPertama($userId, $userRombong, $now)
    {
        // Cek apakah ada yang sudah masuk di lapak ini
        $adaYangMasuk = $this->cekAdaYangMasukDiLapak($userRombong->lapak_id);
        if ($adaYangMasuk) {
            return [
                'status' => 'sudah_ada_yang_masuk',
                'bisa_konfirmasi' => false,
                'pesan' => 'Maaf, sudah ada yang masuk',
                'kehadiran' => null
            ];
        }
        
        // Cek batas waktu jam 11:00
        if ($now->hour >= self::BATAS_JAM_URUTAN_1) {
            // Auto create libur jika lewat batas waktu untuk urutan 1
            $this->autoCreateLibur($userId, 'Otomatis libur - melewati batas waktu urutan 1 (jam ' . self::BATAS_JAM_URUTAN_1 . ':00)');
            
            // Return status libur, bukan melewati_batas_waktu
            return [
                'status' => 'sudah_libur',
                'bisa_konfirmasi' => false,
                'pesan' => 'Otomatis libur karena melewati batas waktu (jam ' . self::BATAS_JAM_URUTAN_1 . ':00)',
                'kehadiran' => kehadiran::where('user_id', $userId)->whereDate('tanggal', Carbon::today())->first()
            ];
        }
        
        return [
            'status' => 'konfirmasi_sekarang',
            'bisa_konfirmasi' => true,
            'pesan' => 'Giliran Anda sekarang! Batas konfirmasi: jam ' . self::BATAS_JAM_URUTAN_1 . ':00',
            'kehadiran' => null
        ];
    }
    
    /**
     * Logika untuk urutan lanjutan (urutan > 1)
     */
    private function getStatusUrutanLanjutan($userId, $userRombong, $now, $today, $allRombongsInLapak, $userPosition)
    {
        // Cek apakah ada yang sudah masuk di lapak ini
        $adaYangMasuk = $this->cekAdaYangMasukDiLapak($userRombong->lapak_id);
        if ($adaYangMasuk) {
            return [
                'status' => 'sudah_ada_yang_masuk',
                'bisa_konfirmasi' => false,
                'pesan' => 'Maaf, sudah ada yang masuk',
                'kehadiran' => null
            ];
        }
        
        // Cek urutan sebelumnya berdasarkan posisi dalam array
        $urutanSebelumnya = $userPosition - 1;
        if ($urutanSebelumnya < 1 || $urutanSebelumnya > $allRombongsInLapak->count()) {
            return [
                'status' => 'menunggu_giliran',
                'bisa_konfirmasi' => false,
                'pesan' => 'Menunggu giliran - urutan sebelumnya tidak ditemukan',
                'kehadiran' => null
            ];
        }
        
        $rombongSebelumnya = $allRombongsInLapak[$urutanSebelumnya - 1]; // Array index dimulai dari 0
        
        $kehadiranSebelumnya = kehadiran::where('user_id', $rombongSebelumnya->user_id)
            ->whereDate('tanggal', $today)
            ->first();
            
        // Jika urutan sebelumnya belum konfirmasi atau bukan libur, masih menunggu giliran
        if (!$kehadiranSebelumnya || $kehadiranSebelumnya->status !== 'libur') {
            return [
                'status' => 'menunggu_giliran',
                'bisa_konfirmasi' => false,
                'pesan' => 'Menunggu urutan sebelumnya konfirmasi libur',
                'kehadiran' => null
            ];
        }
        
        // Urutan sebelumnya sudah libur, sekarang gilirannya
        // LOGIKA YANG BENAR: Window 30 menit dimulai LANGSUNG saat urutan sebelumnya libur
        // BUKAN saat user ini login/akses sistem
        $waktuLiburSebelumnya = Carbon::parse($kehadiranSebelumnya->waktu_konfirmasi);
        $waktuMulaiGiliran = $waktuLiburSebelumnya; // Giliran langsung dimulai saat urutan sebelumnya libur
        $batasWaktu = $waktuMulaiGiliran->copy()->addMinutes(self::WINDOW_MENIT_KONFIRMASI);
        
        // Jika sekarang sudah lewat batas waktu, otomatis libur
        if ($now->gt($batasWaktu)) {
            // Auto create libur karena melewati window 30 menit sejak giliran dimulai
            $this->autoCreateLibur($userId, "Otomatis libur - melewati window 30 menit (giliran: {$waktuMulaiGiliran->format('H:i')} - {$batasWaktu->format('H:i')})");
            
            return [
                'status' => 'sudah_libur',
                'bisa_konfirmasi' => false,
                'pesan' => "Otomatis libur karena melewati window waktu (giliran: {$waktuMulaiGiliran->format('H:i')} - {$batasWaktu->format('H:i')})",
                'kehadiran' => kehadiran::where('user_id', $userId)->whereDate('tanggal', $today)->first()
            ];
        }
        
        // Masih dalam window waktu, bisa konfirmasi
        return [
            'status' => 'konfirmasi_sekarang',
            'bisa_konfirmasi' => true,
            'pesan' => "Giliran Anda! Waktu tersisa sampai: {$batasWaktu->format('H:i')} (dimulai: {$waktuMulaiGiliran->format('H:i')})",
            'kehadiran' => null
        ];
    }
    
    /**
     * Cek apakah ada yang sudah masuk di lapak ini hari ini
     */
    private function cekAdaYangMasukDiLapak($lapakId)
    {
        $today = Carbon::today();
        $rombongsInLapak = \App\Models\rombong::where('lapak_id', $lapakId)->pluck('user_id');
        
        return kehadiran::whereIn('user_id', $rombongsInLapak)
            ->whereDate('tanggal', $today)
            ->where('status', 'masuk')
            ->exists();
    }
    
    /**
     * Auto create record libur dengan keterangan
     */
    private function autoCreateLibur($userId, $keterangan)
    {
        $today = Carbon::today();
        
        kehadiran::updateOrCreate(
            [
                'user_id' => $userId,
                'tanggal' => $today,
            ],
            [
                'status' => 'libur',
                'waktu_konfirmasi' => Carbon::now(),
                'keterangan' => $keterangan
            ]
        );
    }
    
    /**
     * Cek apakah kehadiran libur adalah hasil auto-libur
     */
    private function isAutoLiburKehadiran($userId, $kehadiran)
    {
        if ($kehadiran->status !== 'libur') {
            return false;
        }
        
        // Prioritas: cari rombong yang ada di lapak dulu (sama dengan getStatusKonfirmasi)
        $userRombong = rombong::where('user_id', $userId)
            ->whereNotNull('lapak_id')
            ->first();
            
        // Jika tidak ada di lapak, cari yang tanpa lapak
        if (!$userRombong) {
            $userRombong = rombong::where('user_id', $userId)->first();
        }
        
        if (!$userRombong) {
            return false;
        }
        
        if (!$userRombong->lapak_id) {
            // User tanpa lapak - cek apakah lewat jam batas untuk urutan 1
            $waktuKehadiran = Carbon::parse($kehadiran->waktu_konfirmasi);
            return $waktuKehadiran->hour >= self::BATAS_JAM_URUTAN_1;
        }
        
        // User dalam lapak - cek posisi dan batas waktu
        $allRombongsInLapak = rombong::where('lapak_id', $userRombong->lapak_id)
            ->orderBy('rombong_id', 'asc')
            ->get();
            
        $userPosition = 0;
        foreach ($allRombongsInLapak as $index => $rombong) {
            if ($rombong->user_id == $userId) {
                $userPosition = $index + 1;
                break;
            }
        }
        
        if ($userPosition == 1) {
            // Urutan 1 - auto libur jika lewat jam batas
            $waktuKehadiran = Carbon::parse($kehadiran->waktu_konfirmasi);
            return $waktuKehadiran->hour >= self::BATAS_JAM_URUTAN_1;
        } else {
            // Urutan > 1 - auto libur jika ada urutan sebelumnya yang libur dan lewat 30 menit
            // LOGIK YANG BENAR: 30 menit dihitung dari waktu urutan sebelumnya libur
            $urutanSebelumnya = $userPosition - 1;
            $rombongSebelumnya = $allRombongsInLapak[$urutanSebelumnya - 1];
            
            $kehadiranSebelumnya = kehadiran::where('user_id', $rombongSebelumnya->user_id)
                ->whereDate('tanggal', Carbon::today())
                ->where('status', 'libur')
                ->first();
                
            if ($kehadiranSebelumnya) {
                $waktuLiburSebelumnya = Carbon::parse($kehadiranSebelumnya->waktu_konfirmasi);
                $waktuMulaiGiliran = $waktuLiburSebelumnya; // Giliran dimulai saat urutan sebelumnya libur
                $batasWaktu = $waktuMulaiGiliran->copy()->addMinutes(self::WINDOW_MENIT_KONFIRMASI);
                $waktuKehadiran = Carbon::parse($kehadiran->waktu_konfirmasi);
                
                // Auto-libur jika waktu konfirmasi lewat dari batas waktu giliran
                return $waktuKehadiran->gt($batasWaktu);
            }
        }
        
        return false;
    }
    
    /**
     * Get pesan auto-libur berdasarkan posisi user
     */
    private function getAutoLiburMessage($userId)
    {
        // Prioritas: cari rombong yang ada di lapak dulu (sama dengan getStatusKonfirmasi)
        $userRombong = rombong::where('user_id', $userId)
            ->whereNotNull('lapak_id')
            ->first();
            
        // Jika tidak ada di lapak, cari yang tanpa lapak
        if (!$userRombong) {
            $userRombong = rombong::where('user_id', $userId)->first();
        }
        
        if (!$userRombong || !$userRombong->lapak_id) {
            return 'Otomatis libur karena melewati batas waktu (jam ' . self::BATAS_JAM_URUTAN_1 . ':00)';
        }
        
        // Get position
        $allRombongsInLapak = rombong::where('lapak_id', $userRombong->lapak_id)
            ->orderBy('rombong_id', 'asc')
            ->get();
            
        $userPosition = 0;
        foreach ($allRombongsInLapak as $index => $rombong) {
            if ($rombong->user_id == $userId) {
                $userPosition = $index + 1;
                break;
            }
        }
        
        if ($userPosition == 1) {
            return 'Otomatis libur karena melewati batas waktu (jam ' . self::BATAS_JAM_URUTAN_1 . ':00)';
        } else {
            return 'Otomatis libur karena melewati batas waktu (30 menit sejak giliran dimulai)';
        }
    }

    public function getStatusKehadiran()
    {
        $today = Carbon::today();
        $cacheKey = 'kehadiran_status_' . $today->format('Y-m-d');

        // Gunakan cache untuk data yang sama dalam 30 detik
        $kehadirans = Cache::remember($cacheKey, 30, function () use ($today) {
            return kehadiran::with(['user' => function($query) {
                    $query->select('user_id', 'name');
                }])
                ->whereDate('tanggal', $today)
                ->select('user_id', 'status', 'tanggal', 'waktu_konfirmasi')
                ->get()
                ->map(function ($item) {
                    $user = User::find($item->user_id);
                    $rombong = rombong::where('user_id', $item->user_id)->first();
                    
                    return [
                        'rombong_id' => $rombong->rombong_id ?? null,
                        'user_id' => $item->user_id,
                        'nama' => $user->name ?? '',
                        'nama_jualan' => $rombong->nama_jualan ?? '',
                        'status' => $item->status,
                        'waktu_konfirmasi' => $item->waktu_konfirmasi,
                        'isPast22' => now()->hour >= 13,
                        'lapak_id' => $rombong->lapak_id ?? null
                    ];
                });
        });

        return response()->json($kehadirans);
    }

    public function getDashboardData()
    {
        $userId = auth()->id();
        $today = Carbon::today();
        $now = Carbon::now();
        
        // Dapatkan status konfirmasi menggunakan fungsi utama
        $statusKonfirmasi = $this->getStatusKonfirmasi($userId);
        
        // Tentukan apakah menampilkan pesan "batas waktu absensi"
        // Tampilkan jika status sudah_libur DAN pesan mengandung "Otomatis libur"
        $showBatasWaktu = $statusKonfirmasi['status'] === 'sudah_libur' && 
                        str_contains($statusKonfirmasi['pesan'], 'Otomatis libur');
        
        // Get attendance data and button states
        $data = [
            'kehadiranHariIni' => $statusKonfirmasi['kehadiran'],
            'sudahKonfirmasiHariIni' => $statusKonfirmasi['kehadiran'] !== null,
            'statusKonfirmasi' => $statusKonfirmasi,
            'buttonKonfirmasiAktif' => $this->isButtonKonfirmasiAktif($userId),
            'buttonAnggotaAktif' => $this->isButtonAnggotaAktif($userId),
            'isLewatJam12' => $now->hour >= self::BATAS_JAM_URUTAN_1, // Keep for backward compatibility
            'showBatasWaktu' => $showBatasWaktu, // New logic untuk batas waktu
            'batasJamUrutan1' => self::BATAS_JAM_URUTAN_1, // Tambah konstanta untuk view
            'historyKehadiran' => kehadiran::where('user_id', $userId)
                ->orderBy('tanggal', 'desc')
                ->take(7) // Hanya ambil 7 hari terakhir (1 minggu)
                ->get(),
            'rombongAktifSekarang' => $this->getRombongAktifSekarang($userId)
        ];

        return $data;
    }

    private function isButtonKonfirmasiAktif($userId)
    {
        $statusKonfirmasi = $this->getStatusKonfirmasi($userId);
        
        // Tombol konfirmasi aktif hanya jika status adalah 'konfirmasi_sekarang'
        // dan user benar-benar bisa konfirmasi (bukan melewati batas waktu)
        return $statusKonfirmasi['bisa_konfirmasi'] && 
               $statusKonfirmasi['status'] === 'konfirmasi_sekarang';
    }

    /**
     * Menentukan apakah tombol pengajuan anggota aktif
     * LOGIKA:
     * 1. User baru yang sudah punya rombong tapi belum masuk ke lapak
     * 2. Semua user bisa mengajukan jika ada lapak yang semua anggotanya libur
     */
    private function isButtonAnggotaAktif($userId)
    {
        // Cek apakah user punya rombong
        $userRombong = rombong::where('user_id', $userId)->first();
        
        if (!$userRombong) {
            return false; // Tidak punya rombong sama sekali
        }
        
        // KONDISI 1: User baru yang belum masuk ke lapak
        if (!$userRombong->lapak_id) {
            return true; // User punya rombong tapi belum di lapak
        }
        
        // KONDISI 2: Ada lapak yang semua anggotanya libur
        return $this->hasAnyLapakWithAllMembersLibur();
    }

    /**
     * Cek apakah ada lapak yang semua anggotanya libur hari ini
     * Fungsi ini memungkinkan sistem rebutan untuk lapak yang kosong
     */
    private function hasAnyLapakWithAllMembersLibur()
    {
        $today = now()->toDateString();
        
        // Ambil semua lapak yang punya anggota
        $lapaksWithMembers = \App\Models\Lapak::whereHas('rombongs')->get();
        
        foreach ($lapaksWithMembers as $lapak) {
            if ($this->isAllRombongInLapakLibur($lapak->lapak_id)) {
                return true; // Ada minimal satu lapak yang semua anggotanya libur
            }
        }
        
        return false; // Tidak ada lapak yang semua anggotanya libur
    }

    /**
     * Dapatkan daftar lapak yang tersedia untuk pengajuan anggota
     * LOGIKA: Hanya lapak yang semua anggotanya libur hari ini
     * User bisa rebut lapak yang "kosong" karena semua anggotanya libur
     */
    public function getLapakTersediaUntukPengajuan()
    {
        $today = now()->toDateString();
        $lapakTersedia = [];
        
        // Ambil SEMUA lapak untuk user baru (termasuk yang kosong)
        $allLapaks = \App\Models\Lapak::with('rombongs.user')->get();
        
        foreach ($allLapaks as $lapak) {
            $statusLapak = $this->getStatusLapakUntukPengajuan($lapak->lapak_id);
            
            $lapakTersedia[] = [
                'lapak_id' => $lapak->lapak_id,
                'nama_lapak' => $lapak->nama_lapak,
                'status' => $statusLapak['status'],
                'pesan' => $statusLapak['pesan'],
                'bisa_diajukan' => $statusLapak['bisa_diajukan'],
                'jumlah_anggota' => $lapak->rombongs->count(),
                'tersedia_untuk_rebutan' => $statusLapak['bisa_diajukan'] // Lapak kosong bisa direbut
            ];
        }
        
        return $lapakTersedia;
    }

    /**
     * Dapatkan status lapak untuk pengajuan anggota
     * LOGIKA: Lapak tersedia hanya jika SEMUA anggotanya libur
     */
    private function getStatusLapakUntukPengajuan($lapakId)
    {
        $today = now()->toDateString();
        $rombongs = rombong::where('lapak_id', $lapakId)->with('user')->get();
        
        if ($rombongs->count() == 0) {
            return [
                'status' => 'kosong',
                'pesan' => 'Lapak kosong - tidak ada anggota',
                'bisa_diajukan' => false
            ];
        }
        
        $jumlahMasuk = 0;
        $jumlahLibur = 0;
        $jumlahBelumKonfirmasi = 0;
        
        foreach ($rombongs as $rombong) {
            $kehadiran = kehadiran::where('user_id', $rombong->user_id)
                ->whereDate('tanggal', $today)
                ->first();
                
            if (!$kehadiran) {
                $jumlahBelumKonfirmasi++;
            } elseif ($kehadiran->status === 'masuk') {
                $jumlahMasuk++;
            } elseif ($kehadiran->status === 'libur') {
                $jumlahLibur++;
            }
        }
        
        // ATURAN UTAMA: Jika ada yang masuk, lapak tidak tersedia
        if ($jumlahMasuk > 0) {
            return [
                'status' => 'ada_yang_masuk',
                'pesan' => "Sudah ada yang masuk ({$jumlahMasuk} orang) - Tidak bisa mengajukan",
                'bisa_diajukan' => false
            ];
        }
        
        // Jika semua libur, lapak tersedia untuk pengajuan
        if ($jumlahLibur === $rombongs->count()) {
            return [
                'status' => 'semua_libur',
                'pesan' => "Semua anggota libur ({$jumlahLibur} orang) - Lapak tersedia untuk pengajuan",
                'bisa_diajukan' => true
            ];
        }
        
        // Jika masih ada yang belum konfirmasi
        if ($jumlahBelumKonfirmasi > 0) {
            return [
                'status' => 'menunggu_konfirmasi',
                'pesan' => "Menunggu konfirmasi ({$jumlahBelumKonfirmasi} belum konfirmasi)",
                'bisa_diajukan' => false
            ];
        }
        
        return [
            'status' => 'tidak_tersedia',
            'pesan' => 'Lapak tidak tersedia',
            'bisa_diajukan' => false
        ];
    }

    /**
     * Validasi pengajuan anggota ke lapak tertentu
     * ATURAN:
     * 1. User harus punya rombong
     * 2. Lapak harus semua anggotanya libur (tidak ada yang masuk)
     * 3. User belum ada di lapak tersebut
     */
    public function validatePengajuanAnggota($lapakId, $userId = null)
    {
        if (!$userId) {
            $userId = auth()->id();
        }
        
        // Cek apakah user memiliki rombong
        $userRombong = rombong::where('user_id', $userId)->first();
        if (!$userRombong) {
            return [
                'success' => false,
                'bisa_diajukan' => false,
                'pesan' => 'Lengkapi profil dulu untuk mendaftar sebagai anggota rombong'
            ];
        }

        // PRIORITAS: User baru yang belum punya lapak bisa mengajukan ke lapak manapun (termasuk lapak kosong)
        if (!$userRombong->lapak_id) {
            // CEK: Jika lapak sudah ada anggota yang MASUK hari ini, tolak pengajuan (khusus sistem rebutan)
            $rombongs = rombong::where('lapak_id', $lapakId)->get();
            $today = now()->toDateString();
            $adaYangMasuk = false;
            foreach ($rombongs as $rombong) {
                $kehadiran = \App\Models\kehadiran::where('user_id', $rombong->user_id)
                    ->whereDate('tanggal', $today)
                    ->where('status', 'masuk')
                    ->first();
                if ($kehadiran) {
                    $adaYangMasuk = true;
                    break;
                }
            }
            if ($adaYangMasuk) {
                return [
                    'success' => false,
                    'bisa_diajukan' => false,
                    'pesan' => 'Sorry tidak bisa, sudah ada anggota yang masuk'
                ];
            }
            // Cek apakah user sudah ada di lapak ini
            $existingRombongInLapak = rombong::where('user_id', $userId)
                ->where('lapak_id', $lapakId)
                ->first();

            if ($existingRombongInLapak) {
                return [
                    'success' => false,
                    'bisa_diajukan' => false,
                    'pesan' => 'Anda sudah menjadi anggota di lapak ini'
                ];
            }

            return [
                'success' => true,
                'bisa_diajukan' => true,
                'pesan' => 'User baru bisa mengajukan ke lapak manapun',
                'is_new_user' => true
            ];
        }

        // User sudah punya lapak - ikuti aturan rebutan (hanya lapak yang semua anggotanya libur)
        $statusLapak = $this->getStatusLapakUntukPengajuan($lapakId);

        if (!$statusLapak['bisa_diajukan']) {
            return [
                'success' => false,
                'bisa_diajukan' => false,
                'pesan' => "Tidak bisa mengajukan ke lapak ini: {$statusLapak['pesan']}"
            ];
        }

        // Cek apakah user sudah ada di lapak ini
        $existingRombongInLapak = rombong::where('user_id', $userId)
            ->where('lapak_id', $lapakId)
            ->first();

        if ($existingRombongInLapak) {
            return [
                'success' => false,
                'bisa_diajukan' => false,
                'pesan' => 'Anda sudah menjadi anggota di lapak ini'
            ];
        }

        return [
            'success' => true,
            'bisa_diajukan' => true,
            'pesan' => 'Lapak tersedia untuk pengajuan (sistem rebutan)',
            'status_lapak' => $statusLapak,
            'is_new_user' => false
        ];
    }

    /**
     * Cek apakah semua rombong di lapak sudah libur hari ini
     * Lapak dianggap "kosong" jika semua anggotanya konfirmasi libur
     */
    private function isAllRombongInLapakLibur($lapakId)
    {
        $today = now()->toDateString();
        $rombongs = rombong::where('lapak_id', $lapakId)->get();

        // Jika tidak ada rombong di lapak, return false (lapak kosong tidak ada anggota)
        if ($rombongs->count() == 0) {
            return false;
        }

        foreach ($rombongs as $rombong) {
            $kehadiran = kehadiran::where('user_id', $rombong->user_id)
                ->whereDate('tanggal', $today)
                ->first();

            // Jika belum ada kehadiran, berarti belum semua libur
            if (!$kehadiran) {
                return false;
            }
            
            // Jika ada yang konfirmasi masuk, lapak tidak tersedia
            if ($kehadiran->status === 'masuk') {
                return false;
            }
            
            // Jika status bukan libur, berarti belum semua libur
            if ($kehadiran->status !== 'libur') {
                return false;
            }
        }

        // Semua rombong sudah punya kehadiran dengan status libur
        return true;
    }

    private function clearKehadiranCache($userId, $date)
    {
        $cacheKeys = [
            'kehadiran_status_' . $date->format('Y-m-d'),
            'dashboard_data_' . $userId,
        ];
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Kirim WA konfirmasi dengan link (menggunakan NotifikasiKehadiran service)
     * Method ini untuk kirim link konfirmasi ke user yang gilirannya
     */
    public function kirimKonfirmasiWALink($userId, $tanggal = null)
    {
        try {
            if (!$tanggal) {
                $tanggal = Carbon::today();
            }
            
            // 1. VALIDASI STATUS - hanya kirim jika bisa konfirmasi
            $statusKonfirmasi = $this->getStatusKonfirmasi($userId);
            
            if (!$statusKonfirmasi['bisa_konfirmasi']) {
                return [
                    'success' => false,
                    'message' => "WA tidak dikirim: {$statusKonfirmasi['pesan']}"
                ];
            }
            
            // 2. GUNAKAN SERVICE untuk kirim WA dengan link
            return \App\Services\NotifikasiKehadiran::kirimKonfirmasiWA($userId, $statusKonfirmasi, $tanggal);
            
        } catch (\Exception $e) {
            Log::error('Error kirim WA konfirmasi link: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error sistem: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kirim WA ke semua user yang bisa konfirmasi (sesuai giliran urutan)
     */
    public function processKonfirmasiMassal($tanggal = null)
    {
        if (!$tanggal) {
            $tanggal = Carbon::today();
        }
        
        $results = [];
        
        // Ambil semua lapak dengan rombong
        $lapaks = \App\Models\Lapak::with(['rombongs'])->get();
        
        foreach ($lapaks as $lapak) {
            // Urutkan rombong berdasarkan ID (sesuai dengan getStatusKonfirmasi)
            $rombongs = \App\Models\rombong::where('lapak_id', $lapak->id)
                ->orderBy('rombong_id', 'asc')
                ->get();
            
            foreach ($rombongs as $index => $rombong) {
                if ($rombong->user_id) {
                    $statusInfo = $this->getStatusKonfirmasi($rombong->user_id);
                    
                    // Hanya kirim WA jika status 'konfirmasi_sekarang' dan bisa konfirmasi
                    if ($statusInfo['bisa_konfirmasi'] && $statusInfo['status'] === 'konfirmasi_sekarang') {
                        $result = $this->kirimKonfirmasiWALink($rombong->user_id, $tanggal);
                        
                        $user = User::find($rombong->user_id);
                        $results[] = [
                            'user_id' => $rombong->user_id,
                            'user_name' => $user->name ?? 'Unknown',
                            'lapak' => $lapak->nama_lapak,
                            'urutan' => $index + 1,
                            'status_konfirmasi' => $statusInfo['status'],
                            'result' => $result
                        ];
                        
                        Log::info("Processed WA for user {$rombong->user_id} in lapak {$lapak->nama_lapak}");
                    } else {
                        // Log why WA was not sent
                        $user = User::find($rombong->user_id);
                        $userName = $user ? $user->name : 'Unknown';
                        Log::info("WA not sent to {$userName}: {$statusInfo['pesan']}");
                    }
                }
            }
        }
        
        Log::info("WA massal processing completed", [
            'total_processed' => count($results),
            'date' => $tanggal->format('Y-m-d')
        ]);
        
        return $results;
    }

    public function cekKehadiranLapak($lapakId)
    {
        $rombongs = rombong::where('lapak_id', $lapakId)->orderBy('urutan', 'asc')->get();
        $today = now()->toDateString();
        
        $hasil = [];
        $semuaLibur = true;
        $adaYangMasuk = false;

        foreach ($rombongs as $rombong) {
            $statusKonfirmasi = $this->getStatusKonfirmasi($rombong->user_id);
            
            if ($statusKonfirmasi['status'] === 'sudah_masuk') {
                $hasil[$rombong->rombong_id] = 'aktif';
                $semuaLibur = false;
                $adaYangMasuk = true;
                break; // Jika ada yang masuk, yang lain otomatis nonaktif
            } elseif (in_array($statusKonfirmasi['status'], ['sudah_libur', 'auto_libur'])) {
                $hasil[$rombong->rombong_id] = 'libur';
            } elseif ($statusKonfirmasi['status'] === 'konfirmasi_sekarang') {
                $hasil[$rombong->rombong_id] = 'menunggu';
                $semuaLibur = false;
                break; // Yang pertama bisa konfirmasi adalah yang aktif
            } elseif ($statusKonfirmasi['status'] === 'menunggu_giliran') {
                $hasil[$rombong->rombong_id] = 'nonaktif';
            } elseif ($statusKonfirmasi['status'] === 'sudah_ada_yang_masuk') {
                $hasil[$rombong->rombong_id] = 'nonaktif';
            } else {
                $hasil[$rombong->rombong_id] = 'nonaktif';
            }
        }

        // Set sisanya sebagai nonaktif jika ada yang sudah masuk
        if ($adaYangMasuk) {
            foreach ($rombongs as $rombong) {
                if (!isset($hasil[$rombong->rombong_id])) {
                    $hasil[$rombong->rombong_id] = 'nonaktif';
                }
            }
        }

        return [
            'rombongs' => $hasil,
            'semuaLibur' => $semuaLibur,
            'adaYangMasuk' => $adaYangMasuk
        ];
    }

    /**
     * Proses auto-libur untuk user yang melewati batas waktu
     * Fungsi ini bisa dipanggil oleh cron job untuk memastikan tidak ada yang terlewat
     * LOGIK BARU: Auto-libur berdasarkan waktu absolut, bukan waktu akses user
     */
    public function prosesAutoLibur()
    {
        $today = Carbon::today();
        $now = Carbon::now();
        
        // Proses semua lapak
        $lapaks = \App\Models\Lapak::with(['rombongs' => function($query) {
            $query->orderBy('rombong_id', 'asc');
        }])->get();
        
        foreach ($lapaks as $lapak) {
            $rombongs = $lapak->rombongs;
            
            foreach ($rombongs as $index => $rombong) {
                $userPosition = $index + 1;
                
                // Skip jika sudah ada kehadiran hari ini
                $existingKehadiran = kehadiran::where('user_id', $rombong->user_id)
                    ->whereDate('tanggal', $today)
                    ->first();
                    
                if ($existingKehadiran) {
                    continue;
                }
                
                // Cek apakah sudah ada yang masuk di lapak ini
                $adaYangMasuk = $this->cekAdaYangMasukDiLapak($lapak->lapak_id);
                if ($adaYangMasuk) {
                    continue; // Skip jika sudah ada yang masuk
                }
                
                if ($userPosition == 1) {
                    // Urutan 1: Auto-libur jika lewat jam 13:00
                    if ($now->hour >= self::BATAS_JAM_URUTAN_1) {
                        $this->autoCreateLibur($rombong->user_id, 
                            'Otomatis libur - melewati batas waktu urutan 1 (jam ' . self::BATAS_JAM_URUTAN_1 . ':00)');
                    }
                } else {
                    // Urutan > 1: Cek apakah urutan sebelumnya sudah libur dan lewat 30 menit
                    $rombongSebelumnya = $rombongs[$index - 1];
                    $kehadiranSebelumnya = kehadiran::where('user_id', $rombongSebelumnya->user_id)
                        ->whereDate('tanggal', $today)
                        ->where('status', 'libur')
                        ->first();
                        
                    if ($kehadiranSebelumnya) {
                        $waktuLiburSebelumnya = Carbon::parse($kehadiranSebelumnya->waktu_konfirmasi);
                        $batasWaktu = $waktuLiburSebelumnya->copy()->addMinutes(self::WINDOW_MENIT_KONFIRMASI);
                        
                        if ($now->gt($batasWaktu)) {
                            $this->autoCreateLibur($rombong->user_id, 
                                "Otomatis libur - melewati window 30 menit (giliran: {$waktuLiburSebelumnya->format('H:i')} - {$batasWaktu->format('H:i')})");
                        }
                    }
                }
            }
        }
        
        Log::info('Auto-libur process completed at ' . $now->format('Y-m-d H:i:s'));
    }

    /**
     * API endpoint untuk mendapatkan daftar lapak yang tersedia untuk pengajuan
     */
    public function getAvailableLapaksForPengajuan()
    {
        try {
            $lapakTersedia = $this->getLapakTersediaUntukPengajuan();
            
            return response()->json([
                'success' => true,
                'data' => $lapakTersedia,
                'message' => 'Daftar lapak berhasil diambil'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available lapaks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    /**
     * API endpoint untuk validasi pengajuan anggota
     */
    public function validatePengajuanAnggotaAPI(Request $request)
    {
        $lapakId = $request->input('lapak_id');
        
        if (!$lapakId) {
            return response()->json([
                'success' => false,
                'message' => 'Lapak ID diperlukan'
            ], 422);
        }
        
        try {
            $validation = $this->validatePengajuanAnggota($lapakId);
            
            return response()->json($validation);
        } catch (\Exception $e) {
            Log::error('Error validating pengajuan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }

    private function getRombongAktifSekarang($userId)
    {
        $userRombong = rombong::where('user_id', $userId)->first();
        if (!$userRombong) {
            return null;
        }

        $statusKonfirmasi = $this->getStatusKonfirmasi($userId);
        
        if ($statusKonfirmasi['status'] === 'konfirmasi_sekarang') {
            return $userRombong;
        }
        
        return null;
    }

    /**
     * Konfirmasi kehadiran via WA token
     */
    public function konfirmasiViaWA(Request $request, $token)
    {
        // Validasi token
        $tokenRecord = KehadiranToken::validateToken($token);
        
        if (!$tokenRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kedaluwarsa'
            ], 403);
        }
        
        $userId = $tokenRecord->user_id;
        $status = strtolower(trim($request->input('status')));
        $today = Carbon::today();

        // Validasi status
        if (!in_array($status, ['masuk', 'libur'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Status tidak valid. Hanya boleh: masuk atau libur.'
            ], 422);
        }

        // Gunakan logika konfirmasi yang sama
        $statusKonfirmasi = $this->getStatusKonfirmasi($userId);
        
        if (!$statusKonfirmasi['bisa_konfirmasi']) {
            return response()->json([
                'success' => false,
                'message' => $statusKonfirmasi['pesan']
            ], 422);
        }

        try {
            // Gunakan cache lock untuk prevent race condition
            $lockKey = "konfirmasi_kehadiran_{$userId}_{$today->format('Y-m-d')}";
            
            $result = Cache::lock($lockKey, 10)->block(5, function () use ($userId, $status, $today) {
                // Double-check untuk mencegah race condition
                $existing = kehadiran::where('user_id', $userId)
                    ->whereDate('tanggal', $today)
                    ->first();
                    
                if ($existing) {
                    return [
                        'success' => false,
                        'message' => 'Anda sudah melakukan konfirmasi hari ini dengan status: ' . $existing->status
                    ];
                }
                
                // Buat record kehadiran
                $kehadiran = kehadiran::create([
                    'user_id' => $userId,
                    'tanggal' => $today,
                    'status' => $status,
                    'waktu_konfirmasi' => now(),
                    'keterangan' => 'Konfirmasi via WhatsApp' // Gunakan keterangan daripada via_wa
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Konfirmasi kehadiran berhasil disimpan',
                    'data' => $kehadiran
                ];
            });

            if ($result['success']) {
                // Mark token as used
                $tokenRecord->update(['is_used' => true]);
                
                // Clear cache untuk refresh dashboard
                $this->clearKehadiranCache($userId, $today);
            }

            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Error konfirmasi via WA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Dapatkan data dashboard untuk WA interface
     */
    public function getWADashboardData($token)
    {
        $tokenRecord = KehadiranToken::validateToken($token);
        
        if (!$tokenRecord) {
            return [
                'success' => false,
                'message' => 'Token tidak valid atau sudah kedaluwarsa'
            ];
        }
        
        $userId = $tokenRecord->user_id;
        $user = User::find($userId);
        
        if (!$user) {
            \Log::info('MULAI processKonfirmasiMassal', ['tanggal' => $tanggal ?? now()->toDateString()]);
            $tanggal = now()->toDateString();
            return [
                'success' => false,
                'message' => 'User tidak ditemukan'
            ];
        }
        
        // Gunakan method yang sama untuk mengambil data
        $statusKonfirmasi = $this->getStatusKonfirmasi($userId);
        
        // Tambahkan data rombong untuk compatibility
        $userRombong = \App\Models\rombong::where('user_id', $userId)->with('lapak')->first();
        if ($userRombong) {
            $user->rombong = $userRombong; // Untuk fallback di blade
        }
        
        return [
            'success' => true,
            'data' => [
                'user' => $user,
                'statusKonfirmasi' => $statusKonfirmasi,
                'batasJamUrutan1' => self::BATAS_JAM_URUTAN_1,
                'windowMenitKonfirmasi' => self::WINDOW_MENIT_KONFIRMASI,
                'token' => $token
            ]
        ];
    }

    /**
     * Tampilkan form konfirmasi untuk akses via WA
     */
    public function showKonfirmasiWAForm($token)
    {
        $dashboardData = $this->getWADashboardData($token);
        
        if (!$dashboardData['success']) {
            return view('kehadiran.wa-error', [
                'message' => $dashboardData['message']
            ]);
        }
        
        // Menggunakan konfirmasiKehadiran.blade.php dengan data yang disesuaikan
        $data = $dashboardData['data'];
        $data['token'] = $token; // Pastikan token tersedia untuk routing
        
        return view('user.konfirmasiKehadiran', $data);
    }

    /**
     * Cek apakah anggota sementara adalah anggota baru yang baru di-acc hari ini
     * Logika: Anggota sementara yang created_at atau updated_at nya hari ini
     */
    private function isAnggotaSementaraBaru($userRombong)
    {
        $today = Carbon::today();
        
        // Cek apakah rombong dibuat hari ini (anggota baru)
        $isCreatedToday = Carbon::parse($userRombong->created_at)->isSameDay($today);
        
        // Atau rombong di-update hari ini (kemungkinan baru di-approve dari waiting list)
        $isUpdatedToday = Carbon::parse($userRombong->updated_at)->isSameDay($today);
        
        // Anggota sementara baru jika dibuat/diupdate hari ini
        return $isCreatedToday || $isUpdatedToday;
    }

}
