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

        //cek apakah user sudah punya anggota
        $buttonAnggota = false;
        if($userRombong && !empty($userRombong->nama_jualan)) {
            $userHasAnggota = waitingList::where('user_id', $currentUserId)->exists();

            $buttonAnggota = !$userHasAnggota; //jika ada di waiting list, berarti sudah punya anggota
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
