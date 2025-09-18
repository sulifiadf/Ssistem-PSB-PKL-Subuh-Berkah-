<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PerpindahanLapak;
use App\Models\rombong;
use App\Models\Lapak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerpindahanController extends Controller 
{
    public function store(Request $request)
    {
        try {
            // Log request untuk debugging
            Log::info('Perpindahan request:', $request->all());

            $request->validate([
                'rombong_id' => 'required|exists:rombongs,rombong_id',
                'lapak_asal_id' => 'nullable|exists:lapaks,lapak_id',
                'lapak_tujuan_id' => 'required|exists:lapaks,lapak_id',
            ]);

            DB::transaction(function () use ($request) {
                // Create transfer log
                PerpindahanLapak::create([
                    'rombong_id' => $request->rombong_id,
                    'lapak_asal_id' => $request->lapak_asal_id,
                    'lapak_tujuan_id' => $request->lapak_tujuan_id,
                    'tanggal_perpindahan' => now(),
                ]);

                // Update rombong lapak_id
                rombong::where('rombong_id', $request->rombong_id)
                    ->update(['lapak_id' => $request->lapak_tujuan_id]);
            });

            return response()->json(['success' => true, 'message' => 'Perpindahan berhasil']); // Fixed typo: success bukan succes

        } catch (\Exception $e) {
            Log::error('Perpindahan error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}