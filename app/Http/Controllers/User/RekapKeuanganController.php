<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\keuangan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RekapKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = keuangan::query();
        
        // Filter berdasarkan tanggal jika ada
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        
        // Urutkan berdasarkan tanggal terbaru
        $keuangans = $query->orderBy('tanggal', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);
        
        return view('user.rekapKeuangan', compact('keuangans'));
    }
}
