<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Lapak;
use App\Models\WaitingList;
use App\Models\rombong;
use App\Models\kehadiran;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentUserId = $user->user_id;

        $totalTetap = rombong::where('jenis', 'tetap')->count();
        $totalSementara = rombong::where('jenis', 'sementara')->count();

        // Ambil semua lapak beserta rombong + user pemiliknya
        $lapaks = Lapak::with(['rombongs.user'])->get();


        // Ambil daftar lapak yang SUDAH disetujui untuk user ini
        $approvedLapakIds = WaitingList::where('user_id',$currentUserId)
            ->where('status', 'disetujui')
            ->pluck('lapak_id')
            ->toArray();
        
        //ambil rombong user
        $userRombong = rombong::where('user_id', $currentUserId)->first();

        //apakah pernah mengajukan anggota
        $userHasAnggota = waitingList::where('user_id', $currentUserId)->exists();

        //button + anggota muncul, jika belum pernah mengajukan
        $buttonAnggota = !$userHasAnggota;

        //apakah semua anggota libur
        $today = now()->toDateString();
        $semuaLibur = true;

        foreach ($lapaks as $lapak) {
            foreach ($lapak->rombongs as $rombong) {
                if ($rombong->user) {
                    $kehadiran = kehadiran::where('user_id', $rombong->user->user_id)
                        ->whereDate('tanggal', $today)
                        ->first();

                    if (!$kehadiran || $kehadiran->status != 'libur') {
                        $semuaLibur = false;
                        break 2;
                    }
                }
            }
        }

        if ($semuaLibur){
            $buttonAnggota = true;
        }

        $historyKehadiran = kehadiran::where('user_id', $currentUserId)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('user.dashboard', compact(
            'lapaks', 
            'approvedLapakIds',
            'totalTetap',
            'totalSementara',
            'userRombong',
            'buttonAnggota',
            'userHasAnggota',
            'historyKehadiran'
        ));
    }
}
