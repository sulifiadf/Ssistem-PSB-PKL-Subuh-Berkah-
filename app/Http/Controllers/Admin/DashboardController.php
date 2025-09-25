<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Lapak;
use App\Models\rombong;
use App\Models\keuangan;
use App\Models\WaitingList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class DashboardController extends Controller
{
    public function index()
    {
        $users = User::where('status', 'pending')->get();

        $totalTetap = rombong::where('jenis', 'tetap')->count();
        $totalSementara = rombong::where('jenis', 'sementara')->count();
        $jumlahUangKas = keuangan::where('jenis', 'pemasukan')->sum('jumlah');

        $lapaks = Lapak::with(['rombongs' => function($query) {
            $query->with(['user' => function($userQuery) {
                $userQuery->where('status', 'disetujui');
            }]);
        }])->get();

        $pendingAnggota = WaitingList::with(['user', 'lapak'])
            ->where('status', 'pending')
            ->get();

        return view('admin.dashboard', compact('users', 'lapaks', 'totalTetap', 'totalSementara', 'pendingAnggota', 'jumlahUangKas'));
    }

}
