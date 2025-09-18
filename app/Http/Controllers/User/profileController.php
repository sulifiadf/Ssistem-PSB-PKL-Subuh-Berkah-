<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\rombong; 

class ProfileController extends Controller
{
    // Tampilkan form edit (sekalian show)
    public function edit()
    {
        $user = Auth::user();
        // jika belum punya rombong, kembalikan object kosong supaya blade aman
        $rombong = $user->rombong ?? new Rombong();
        return view('user.profile', compact('user', 'rombong'));
    }

    // Simpan/perbarui profile + rombong
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'no_telp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nama_jualan' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'foto_rombong' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_tetangga_kanan' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'foto_tetangga_kiri' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Ambil atau buat rombong (relasi hasOne)
        $rombong = $user->rombong ?: new Rombong();
        $rombong->user_id = $user->user_id; 
        if (isset($validated['nama_jualan'])) $rombong->nama_jualan = $validated['nama_jualan'];
        if (isset($validated['latitude'])) $rombong->latitude = $validated['latitude'];
        if (isset($validated['longitude'])) $rombong->longitude = $validated['longitude'];

        // Upload/replace foto (hapus file lama jika ada)
        if ($request->hasFile('foto_rombong')) {
            // hapus yang lama
            if ($rombong->foto_rombong && Storage::disk('public')->exists($rombong->foto_rombong)) {
                Storage::disk('public')->delete($rombong->foto_rombong);
            }
            $rombong->foto_rombong = $request->file('foto_rombong')->store('rombong', 'public');
        }

        if ($request->hasFile('foto_tetangga_kanan')) {
            if ($rombong->foto_tetangga_kanan && Storage::disk('public')->exists($rombong->foto_tetangga_kanan)) {
                Storage::disk('public')->delete($rombong->foto_tetangga_kanan);
            }
            $rombong->foto_tetangga_kanan = $request->file('foto_tetangga_kanan')->store('rombong', 'public');
        }

        if ($request->hasFile('foto_tetangga_kiri')) {
            if ($rombong->foto_tetangga_kiri && Storage::disk('public')->exists($rombong->foto_tetangga_kiri)) {
                Storage::disk('public')->delete($rombong->foto_tetangga_kiri);
            }
            $rombong->foto_tetangga_kiri = $request->file('foto_tetangga_kiri')->store('rombong', 'public');
        }

        $rombong->save();

        return redirect()->route('user.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    public function deleteProfileImage()
    {
        $user = Auth::user();
        if ($user->rombong && $user->rombong->foto_rombong) {
            if (Storage::disk('public')->exists($user->rombong->foto_rombong)) {
                Storage::disk('public')->delete($user->rombong->foto_rombong);
            }
            $user->rombong->foto_rombong = null;
            $user->rombong->save();
        }
        return response()->json(['success' => true]);
    }

    public function deleteNeighborImage()
    {
        $user = Auth::user();
        if ($user->rombong) {
            if ($user->rombong->foto_tetangga_kanan && Storage::disk('public')->exists($user->rombong->foto_tetangga_kanan)) {
                Storage::disk('public')->delete($user->rombong->foto_tetangga_kanan);
            }
            if ($user->rombong->foto_tetangga_kiri && Storage::disk('public')->exists($user->rombong->foto_tetangga_kiri)) {
                Storage::disk('public')->delete($user->rombong->foto_tetangga_kiri);
            }

            $user->rombong->foto_tetangga_kanan = null;
            $user->rombong->foto_tetangga_kiri = null;
            $user->rombong->save();
        }
        return response()->json(['success' => true]);
    }

    public function getLocation()
    {
        $user = Auth::user();
        return response()->json([
            'latitude' => $user->rombong->latitude ?? null,
            'longitude' => $user->rombong->longitude ?? null
        ]);
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        $rombong = $user->rombong ?: new Rombong();
        $rombong->user_id = $user->user_id;
        $rombong->latitude = $request->latitude;
        $rombong->longitude = $request->longitude;
        $rombong->save();

        return response()->json(['success' => true]);
    }

    public function settings()
    {
        return view('user.profile');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah.');
    }

    public function uploadBulkImages(Request $request)
    {
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('uploads', 'public');
                // Simpan path jika diperlukan
            }
        }

        return response()->json(['success' => true]);
    }
}
