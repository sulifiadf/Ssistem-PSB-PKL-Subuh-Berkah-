<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\rombong;
use App\Models\Lapak;
use App\Models\WaitingList;

class DashboardController extends Controller
{
    // Dashboard
    public function index()
    {
        $users = User::where('status', 'pending')->get();

        $totalTetap = rombong::where('jenis', 'tetap')->count();
        $totalSementara = rombong::where('jenis', 'sementara')->count();

        $lapaks = Lapak::with(['rombongs' => function($query) {
            $query->with(['user' => function($userQuery) {
                $userQuery->where('status', 'disetujui');
            }]);
        }])->get();

        $pendingAnggota = WaitingList::with(['user', 'lapak'])
            ->where('status', 'pending')
            ->get();

        return view('admin.dashboard', compact('users', 'lapaks', 'totalTetap', 'totalSementara', 'pendingAnggota'));
    }

}
