<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Lapak;
use App\Models\rombong;
use App\Models\keuangan;
use App\Models\kehadiran;
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
        
        // Data keuangan untuk 3 card
        $totalPemasukan = keuangan::where('jenis', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = keuangan::where('jenis', 'pengeluaran')->sum('jumlah');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        $lapaks = Lapak::with(['rombongs' => function($query) {
            $query->with('user')->orderBy('urutan', 'asc');
        }])->get();

        $pendingAnggota = WaitingList::with(['user', 'lapak'])
            ->where('status', 'pending')
            ->get();

        return view('admin.dashboard', compact('users', 'lapaks', 'totalTetap', 'totalSementara', 'pendingAnggota', 'totalPemasukan', 'totalPengeluaran', 'saldoAkhir'));
    }

}
