<?php

namespace App\Http\Controllers\User;

use App\Models\Lapak;
use App\Models\rombong;
use App\Models\keuangan;
use App\Models\kehadiran;
use App\Models\WaitingList;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentUserId = $user->user_id;

        // Data dasar dashboard
        $totalTetap = rombong::where('jenis', 'tetap')->count();
        $totalSementara = rombong::where('jenis', 'sementara')->count();
        
        // Data keuangan untuk 3 card
        $totalPemasukan = keuangan::where('jenis', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = keuangan::where('jenis', 'pengeluaran')->sum('jumlah');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        // Data keuangan untuk tabel rekapan (hanya read-only untuk user)
        $keuangans = keuangan::orderBy('tanggal', 'desc')->take(10)->get(); // Ambil 10 data terbaru

        // Ambil semua lapak beserta rombong + user pemiliknya berdasarkan urutan
        $lapaks = Lapak::with(['rombongs' => function($query){
            $query->orderBy('urutan', 'asc');
        }])->with(['rombongs.user'])
            ->get();

        // Tambahkan status konfirmasi untuk setiap rombong
        $kehadiranController = new \App\Http\Controllers\User\KehadiranController();
        foreach($lapaks as $lapak) {
            foreach($lapak->rombongs as $rombong) {
                if($rombong->user) {
                    $statusInfo = $kehadiranController->getStatusKonfirmasi($rombong->user_id);
                    $rombong->statusInfo = $statusInfo;
                }
            }
        }

        // Ambil daftar lapak yang SUDAH disetujui untuk user ini
        $approvedLapakIds = WaitingList::where('user_id',$currentUserId)
            ->where('status', 'disetujui')
            ->pluck('lapak_id')
            ->toArray();

        // Ambil rombong user
        $userRombong = rombong::where('user_id', $currentUserId)->first();

        // Apakah pernah mengajukan anggota
        $userHasAnggota = waitingList::where('user_id', $currentUserId)->exists();

        // Panggil KehadiranController untuk data kehadiran
        $kehadiranController = new \App\Http\Controllers\User\KehadiranController();
        $kehadiranData = $kehadiranController->getDashboardData($currentUserId);

        // Extract individual variables untuk backward compatibility
        $kehadiranHariIni = $kehadiranData['kehadiranHariIni'] ?? null;
        $sudahKonfirmasiHariIni = $kehadiranData['sudahKonfirmasiHariIni'] ?? false;
        $statusKonfirmasi = $kehadiranData['statusKonfirmasi'] ?? [];
        $isLewatJam12 = $kehadiranData['isLewatJam12'] ?? false;
        $showBatasWaktu = $kehadiranData['showBatasWaktu'] ?? false;
        $batasJamUrutan1 = $kehadiranData['batasJamUrutan1'] ?? 12;
        $buttonKonfirmasiAktif = $kehadiranData['buttonKonfirmasiAktif'] ?? false;
        $buttonAnggotaAktif = $kehadiranData['buttonAnggotaAktif'] ?? false;
        $historyKehadiran = $kehadiranData['historyKehadiran'] ?? collect();
        $rombongAktifSekarang = $kehadiranData['rombongAktifSekarang'] ?? null;
        $konfirmasiInfo = $kehadiranData['konfirmasiInfo'] ?? [];

        // Pastikan data tidak null untuk mencegah error di view
        if ($sudahKonfirmasiHariIni && !$kehadiranHariIni) {
            // Jika sudah konfirmasi tapi kehadiranHariIni null, ambil ulang dari database
            $kehadiranHariIni = kehadiran::where('user_id', $currentUserId)
                ->whereDate('tanggal', now()->toDateString())
                ->first();
        }

        return view('user.dashboard', compact(
            'lapaks', 
            'approvedLapakIds',
            'totalTetap',
            'totalSementara',
            'totalPemasukan',
            'totalPengeluaran',
            'saldoAkhir',
            'userRombong',
            'userHasAnggota',
            'kehadiranHariIni',
            'sudahKonfirmasiHariIni',
            'statusKonfirmasi',
            'isLewatJam12',
            'showBatasWaktu',
            'batasJamUrutan1',
            'buttonKonfirmasiAktif',
            'buttonAnggotaAktif',
            'historyKehadiran',
            'rombongAktifSekarang',
            'konfirmasiInfo',
            'keuangans'
        ));
    }
}
