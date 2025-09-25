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
        $today = now()->toDateString();

        $totalTetap = rombong::where('jenis', 'tetap')->count();
        $totalSementara = rombong::where('jenis', 'sementara')->count();
        $jumlahUangKas = keuangan::where('jenis', 'pemasukan')->sum('jumlah');

        // Ambil semua lapak beserta rombong + user pemiliknya berdasarkan urutan
        $lapaks = Lapak::with(['rombongs' => function($query){
            $query->orderBy('urutan', 'asc');
        }])->with(['rombongs.user'])
            ->get();


        // Ambil daftar lapak yang SUDAH disetujui untuk user ini
        $approvedLapakIds = WaitingList::where('user_id',$currentUserId)
            ->where('status', 'disetujui')
            ->pluck('lapak_id')
            ->toArray();
        
        //ambil rombong user
        $userRombong = rombong::where('user_id', $currentUserId)->first();

        //apakah pernah mengajukan anggota
        $userHasAnggota = waitingList::where('user_id', $currentUserId)->exists();

        $isLewatJam12 = now()->hour >=12;

        $kehadiranHariIni = kehadiran::where('user_id', $currentUserId)
            ->whereDate('tanggal', $today)
            ->first();

        $sudahKonfirmasiHariIni = $kehadiranHariIni !== null;

        if($isLewatJam12 && !$sudahKonfirmasiHariIni){
            $kehadiranHariIni = kehadiran::create([
                'user_id' => $currentUserId,
                'tanggal' => $today,
                'status' => 'libur',
                'keterangan' => 'Auto-generated: Batas waktu absensi telah lewat'
            ]);
            $sudahKonfirmasiHariIni = true;
        }

        //konfirmasi berdasarkan urutan rombong
        $buttonKonfirmasiAktif = false;
        $buttonAnggotaAktif = false;
        $rombongAktifSekarang = null;
        $semuaRombongLibur = true;

        foreach($lapaks as $lapak){
            $rombongAktifLapak = null;
            $adaRombongMasuk = false;
            $semuaRombongLapakLibur = true;

            foreach($lapak->rombongs as $rombong){
                if($rombong->user){
                    $kehadiranRombong = kehadiran::where('user_id', $rombong->user->user_id)
                        ->whereDate('tanggal', $today)
                        ->first();

                        $statusRombong = $kehadiranRombong ? $kehadiranRombong->status : null;

                        //jika rombong ini masuk
                        if($statusRombong === 'masuk'){
                            $adaRombongMasuk = true;
                            $semuaRombongLapakLibur = false;
                            $semuaRombongLibur = false;
                            break;
                        }

                        if(!$statusRombong && !$isLewatJam12){
                            if (!$rombongAktifLapak) {
                                $rombongAktifLapak = $rombong;
                            }

                            if ($rombong->user->user_id == $currentUserId) {
                                $rombongAktifSekarang = $rombongAktifLapak;
                            }
                        }

                        //jika ada rombong yang tidak libur
                        if($statusRombong !== 'libur'){
                            $semuaRombongLapakLibur = false;
                            $semuaRombongLibur = false;
                        }
                }
            }

            //jika ada rombong yang masuk, maka button konfirmasi tidak aktif untuk rombong setelahnya
            if($adaRombongMasuk){
                continue;
            }

            if($rombongAktifLapak && $rombongAktifLapak->user->user_id == $currentUserId){
                $buttonKonfirmasiAktif = true;
            }

            //jika semua rombong di lapak ini libur, button + anggota aktif
            if($semuaRombongLapakLibur){
                $userAnggotaLapak = $lapak->rombongs->contains('user_id', $currentUserId);

                if($userAnggotaLapak){
                    $buttonAnggotaAktif = true;
                }    
            }
        }

        //user bisa mengajuakn anggota jika belum pernh mengajukan
        if(!$userHasAnggota){
            $buttonAnggotaAktif = true;
        }

        // //apakah semua anggota libur
        // $semuaLibur = true;

        // foreach ($lapaks as $lapak) {
        //     foreach ($lapak->rombongs as $rombong) {
        //         if ($rombong->user) {
        //             $kehadiran = kehadiran::where('user_id', $rombong->user->user_id)
        //                 ->whereDate('tanggal', $today)
        //                 ->first();

        //             if (!$kehadiran || $kehadiran->status != 'libur') {
        //                 $semuaLibur = false;
        //                 break 2;
        //             }
        //         }
        //     }
        // }

        // if ($semuaLibur){
        //     $buttonAnggota = true;
        // }

        $historyKehadiran = kehadiran::where('user_id', $currentUserId)
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
