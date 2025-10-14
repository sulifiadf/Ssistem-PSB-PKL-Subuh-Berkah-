<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\rombong;
use App\Models\Lapak;
use App\Models\WaitingList;
use App\Models\kehadiran;
use App\Http\Controllers\User\KehadiranController;

class RombongController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'lapak_id'    => 'required|exists:lapaks,lapak_id',
            'nama_jualan' => 'required|string|max:255',
            'foto_rombong' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('foto_rombong')) {
            
            $path = $request->file('foto_rombong')->store('rombong', 'public');
        }

        // PRIORITAS: Validasi menggunakan KehadiranController dulu (menentukan apakah user bisa mengajukan)
        $kehadiranController = new KehadiranController();
        $validation = $kehadiranController->validatePengajuanAnggota($request->lapak_id, auth()->id());
        
        if (!$validation['bisa_diajukan']) {
            return response()->json([
                'success' => false,
                'message' => $validation['pesan'] ?? 'Tidak dapat mengajukan ke lapak ini'
            ]);
        }

        // cek jika sudah mengirim pengajuan pending ke lapak yang sama
        $existingPengajuan = WaitingList::where('user_id', auth()->id())
            ->where('lapak_id', $request->lapak_id)
            ->where('status', 'pending')
            ->first();
            
        if ($existingPengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah mengirim pengajuan ke lapak ini yang sedang dalam proses persetujuan.'
            ]);
        }

        // buat pengajuan baru ke waiting list
        WaitingList::create([
            'user_id'          => auth()->id(),
            'lapak_id'         => $request->lapak_id,
            'nama_jualan'      => $request->nama_jualan,
            'tanggal pengajuan'=> now(),  // ✅ gunakan snake_case (tanpa spasi)
            'status'           => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dikirim ke waiting list.'
        ]);
    }

    public function update(Request $request, $id)
{
    $rombong = rombong::findOrFail($id);

    $request->validate([
        'nama_jualan'   => 'required|string|max:255',
        'jenis'         => 'required|string|max:255',
        'foto_rombong'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $path = $rombong->foto_rombong;
    if ($request->hasFile('foto_rombong')) {
        $path = $request->file('foto_rombong')->store('rombong', 'public');
    }

    $rombong->update([
        'nama_jualan'  => $request->nama_jualan,
        'jenis'        => $request->jenis,
        'foto_rombong' => $path,
    ]);

    return redirect()->back()->with('success', 'Rombong berhasil diperbarui.');
}



}
