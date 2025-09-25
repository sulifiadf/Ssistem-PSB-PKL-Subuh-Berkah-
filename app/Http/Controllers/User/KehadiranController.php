<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kehadiran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KehadiranController extends Controller
{
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

        // Cek apakah sudah lewat jam 12:00
        if (now()->hour >= 12) {
            return response()->json([
                'success' => false,
                'message' => 'Batas waktu konfirmasi telah berakhir (setelah jam 12:00).'
            ], 422);
        }

        // Cek apakah sudah konfirmasi hari ini
        $sudahKonfirmasi = Kehadiran::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->exists();

        if ($sudahKonfirmasi) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan konfirmasi kehadiran hari ini.'
            ], 422);
        }

        // PERBAIKAN CSRF: Tambahkan validasi tambahan untuk keamanan
        try {
            // Gunakan cache lock untuk prevent race condition
            $lock = Cache::lock('kehadiran_lock_' . $userId, 10);
            
            if ($lock->get()) {
                // Update atau create kehadiran dengan query yang dioptimasi
                $kehadiran = DB::transaction(function () use ($userId, $today, $status) {
                    return Kehadiran::create([
                        'user_id' => $userId,
                        'tanggal' => $today,
                        'status' => $status,
                        'waktu_konfirmasi' => Carbon::now()
                    ]);
                });

                // Clear cache terkait kehadiran
                $this->clearKehadiranCache($userId, $today);

                // Kirim WA di background (jika perlu)
                if (app()->environment('production')) {
                    dispatch(function () use ($userId, $status) {
                        $this->kirimKonfirmasiWA($userId, $status);
                    })->afterResponse();
                }

                return redirect()->back()->with('success', "Kehadiran berhasil dikonfirmasi sebagai {$status}");

                
                $lock->release();
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

    public function getStatusKehadiran()
    {
        $today = Carbon::today();
        $cacheKey = 'kehadiran_status_' . $today->format('Y-m-d');

        // Gunakan cache untuk data yang sama dalam 30 detik
        $kehadirans = Cache::remember($cacheKey, 30, function () use ($today) {
            return Kehadiran::with(['user.rombong' => function($query) {
                    $query->select('rombong_id', 'user_id', 'nama_jualan');
                }])
                ->whereDate('tanggal', $today)
                ->select('user_id', 'status', 'tanggal')
                ->get()
                ->map(function ($item) {
                    return [
                        'rombong_id' => $item->user->rombong->rombong_id ?? null,
                        'user_id' => $item->user_id,
                        'nama' => $item->user->name ?? '',
                        'nama_jualan' => $item->user->rombong->nama_jualan ?? '',
                        'status' => $item->status,
                        'isPast12' => now()->hour >= 12,
                        'isActive' => false // akan dihitung di frontend berdasarkan urutan
                    ];
                });
        });

        return response()->json($kehadirans);
    }

    public function getDashboardData()
    {
        $userId = auth()->id();
        $today = Carbon::today();
        
        // Query yang dioptimasi untuk dashboard
        $data = [
            'jumlahUangKas' => Cache::remember('total_uang_kas', 300, function () {
                return DB::table('kas')->sum('jumlah');
            }),
            
            'totalTetap' => Cache::remember('anggota_tetap_count', 300, function () {
                return User::where('status_anggota', 'tetap')->count();
            }),
            
            'totalSementara' => Cache::remember('anggota_sementara_count', 300, function () {
                return User::where('status_anggota', 'sementara')->count();
            }),
            
            'kehadiranHariIni' => Kehadiran::where('user_id', $userId)
                ->whereDate('tanggal', $today)
                ->select('status', 'tanggal')
                ->first(),
                
            'sudahKonfirmasiHariIni' => Kehadiran::where('user_id', $userId)
                ->whereDate('tanggal', $today)
                ->exists(),
                
            'isLewatJam12' => now()->hour >= 12,

            'buttonKonfirmasiAktif' => $this->isButtonKonfirmasiAktif($userId),
            'buttonAnggotaAktif' => $this->isButtonAnggotaAktif($userId),
        ];

        return $data;
    }

    private function isButtonKonfirmasiAktif($userId)
    {
        $today = Carbon::today();
        
        // Cek apakah sudah konfirmasi
        if (Kehadiran::where('user_id', $userId)->whereDate('tanggal', $today)->exists()) {
            return false;
        }

        // Cek apakah sudah lewat jam 12
        if (now()->hour >= 12) {
            return false;
        }

        // Logika untuk menentukan apakah tombol aktif berdasarkan urutan rombong
        // Implementasi logika bisnis sesuai kebutuhan
        
        return true; // Sementara return true, sesuaikan dengan logika bisnis
    }

    private function isButtonAnggotaAktif($userId)
    {
        // PERBAIKAN: Logika yang lebih akurat untuk tombol anggota
        // User bisa mengajukan selama belum memiliki rombong aktif
        $user = User::with(['rombong' => function($query) {
            $query->where('status', 'active');
        }])->find($userId);
        
        return !$user->rombong || $user->rombong->isEmpty();
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

    private function kirimKonfirmasiWA($userId, $status)
    {
        try {
            $user = User::find($userId, ['user_id', 'phone', 'name']);
            if (!$user || !$user->phone) return false;

            $statusText = $status === 'masuk' ? 'masuk' : 'libur';
            $pesan = "Halo {$user->name}!\n\nKonfirmasi kehadiran berhasil!\nStatus: {$statusText}\nWaktu: " . now()->format('d/m/Y H:i:s');

            Http::timeout(10)->post(env('WHATSAPP_API_URL'), [
                'phone' => $user->phone,
                'message' => $pesan,
                'token' => env('WHATSAPP_API_KEY')
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("WA Error: " . $e->getMessage());
            return false;
        }
    }

    public function cekKehadiranLapak($lapakId)
{
    $rombongs = \App\Models\rombong::where('lapak_id', $lapakId)->orderBy('urutan', 'asc')->get();

    $hasil = [];
    $semuaLibur = true;

    foreach ($rombongs as $index => $rombong) {
        $kehadiran = \App\Models\Kehadiran::where('rombong_id', $rombong->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        if ($kehadiran && $kehadiran->status == 'hadir') {
            $hasil[$rombong->id] = 'aktif'; // hanya rombong ini yang aktif
            $semuaLibur = false;
        } elseif ($index == 0 && (!$kehadiran || $kehadiran->status == 'libur')) {
            // urutan pertama libur, maka aktifkan urutan 2
            if (isset($rombongs[1])) {
                $hasil[$rombongs[1]->id] = 'aktif';
            }
        } else {
            $hasil[$rombong->id] = 'nonaktif';
        }
    }

    return [
        'rombongs' => $hasil,
        'semuaLibur' => $semuaLibur
    ];
}

}