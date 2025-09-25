<?php

namespace App\Http\Controllers\User;

use App\Models\Lapak;
use App\Models\Rombong;
use App\Models\Keuangan;
use App\Models\Kehadiran;
use App\Models\WaitingList;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentUserId = $user->user_id;
        $today = Carbon::today();

        $totalTetap = Rombong::where('jenis', 'tetap')->count();
        $totalSementara = Rombong::where('jenis', 'sementara')->count();
        $jumlahUangKas = Keuangan::where('jenis', 'pemasukan')->sum('jumlah');

        $lapaks = Lapak::with(['rombongs' => function($query) {
            $query->orderBy('urutan', 'asc')->with('user');
        }])->get();

        $approvedLapakIds = WaitingList::where('user_id', $currentUserId)
            ->where('status', 'disetujui')
            ->pluck('lapak_id')
            ->toArray();

        $userRombong = Rombong::where('user_id', $currentUserId)->first();
        $userHasAnggota = WaitingList::where('user_id', $currentUserId)->exists();

        $kehadiranHariIni = Kehadiran::where('user_id', $currentUserId)
            ->whereDate('tanggal', $today)
            ->first();

        $sudahKonfirmasiHariIni = $kehadiranHariIni !== null;
        $isLewatJam12 = now()->hour >= 20;

        $buttonKonfirmasiAktif = false;
        $buttonAnggotaAktif = false;
        $rombongAktifSekarang = null;
        $semuaRombongLibur = true;

        foreach ($lapaks as $lapak) {
            $rombongAktifLapak = null;
            $adaRombongMasuk = false;
            $semuaRombongLapakLibur = true;

            foreach ($lapak->rombongs as $rombong) {
                if ($rombong->user) {
                    $kehadiranRombong = Kehadiran::where('user_id', $rombong->user->user_id)
                        ->whereDate('tanggal', $today)
                        ->first();

                    $statusRombong = $kehadiranRombong->status ?? null;

                    if ($statusRombong === 'masuk') {
                        $adaRombongMasuk = true;
                        $semuaRombongLapakLibur = false;
                        $semuaRombongLibur = false;
                        break;
                    }

                    if ($rombong->urutan == 1) {
                        if (!$statusRombong && !$isLewatJam12) {
                            $rombongAktifLapak = $rombong;
                        }
                    } else {
                        $prev = $lapak->rombongs->where('urutan', $rombong->urutan - 1)->first();
                        if ($prev) {
                            $prevKehadiran = Kehadiran::where('user_id', $prev->user_id)
                                ->whereDate('tanggal', $today)
                                ->first();

                            if ($prevKehadiran && $prevKehadiran->status === 'libur') {
                                $batas = Carbon::parse($prevKehadiran->waktu_konfirmasi)->addMinutes(30);
                                if (!$statusRombong && now()->lte($batas)) {
                                    $rombongAktifLapak = $rombong;
                                }
                            }
                        }
                    }

                    if ($statusRombong !== 'libur') {
                        $semuaRombongLapakLibur = false;
                        $semuaRombongLibur = false;
                    }

                    if ($rombongAktifLapak && $rombong->user->user_id == $currentUserId) {
                        $rombongAktifSekarang = $rombong;
                        $buttonKonfirmasiAktif = true;
                    }
                }
            }

            if ($semuaRombongLapakLibur && $userRombong && $userRombong->lapak_id !== $lapak->id) {
                $buttonAnggotaAktif = true;
            }
        }

        if (!$userHasAnggota && $userRombong && !$userRombong->lapak_id) {
            $buttonAnggotaAktif = true;
        }

        $historyKehadiran = Kehadiran::where('user_id', $currentUserId)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('user.dashboard', compact(
            'lapaks', 
            'approvedLapakIds',
            'totalTetap',
            'totalSementara',
            'jumlahUangKas',
            'userRombong',
            'buttonKonfirmasiAktif',
            'buttonAnggotaAktif',
            'userHasAnggota',
            'historyKehadiran',
            'sudahKonfirmasiHariIni',
            'kehadiranHariIni',
            'isLewatJam12',
            'rombongAktifSekarang'
        ));
    }
}
