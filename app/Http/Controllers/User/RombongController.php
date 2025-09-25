<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Rombong;
use App\Models\Lapak;
use App\Models\WaitingList;

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

        // cek jika sudah mengirim pengajuan sebelumnya
        $existingPengajuan = WaitingList::where('user_id', auth()->id())->first();
        if ($existingPengajuan) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah ada di waiting list.'
            ]);
        }

        // cek rombong user
        $existingRombong = Rombong::where('user_id', auth()->id())->first();
        if (!$existingRombong) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki rombong, lengkapi profil terlebih dahulu.'
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
    $rombong = Rombong::findOrFail($id);

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
