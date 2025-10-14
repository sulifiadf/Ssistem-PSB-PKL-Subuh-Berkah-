<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Tampilkan semua user
    public function index()
    {
        $allUsers = User::with('rombong')
            ->where('role', 'user')
            ->orderBy('user_id', 'desc')
            ->get();

        return view('admin.user.index', compact('allUsers'));
    }

    // Form tambah user
    public function create()
    {
        return view('admin.user.create');
    }

    // Simpan user baru
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'alamat'      => 'required|string|max:500',
            'email'       => 'required|email|unique:users,email',
            'no_telp'     => 'required|string|regex:/^62[0-9]{9,13}$/|max:15',
            'nama_jualan' => 'required|string|max:255',
            'password'    => 'required|string|min:6|confirmed',
            'foto_rombong'=> 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'      => 'required|in:pending,approve,rejected',
        ], [
            'password.min' => 'Password minimal 6 karakter.',
            'no_telp.regex' => 'Nomor telepon harus dimulai dengan 62 dan diikuti 9-13 digit angka.',
            'status.in' => 'Status harus salah satu dari: pending, approve, rejected.',
        ]);

        try {
            // Create user with role 'user' only (security: admin cannot create other admins)
            $userData = $request->only([
                'name', 'alamat', 'email', 'no_telp', 'status'
            ]);
            $userData['password'] = Hash::make($request->password);
            $userData['role'] = 'user'; // Force role to user for security

            $user = User::create($userData);

            // Create rombong data separately for better data integrity
            $rombongData = [
                'user_id' => $user->user_id,
                'nama_jualan' => $request->nama_jualan,
            ];

            if ($request->hasFile('foto_rombong')) {
                $rombongData['foto_rombong'] = $request->file('foto_rombong')->store('rombong', 'public');
            }

            \App\Models\rombong::create($rombongData);

            return redirect()->route('admin.user.index')
                ->with('success', 'User berhasil ditambahkan dengan aman.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']);
        }
    }

    // Form edit user
    public function edit(User $user)
    {
        return view('admin.user.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'alamat'      => 'required|string|max:500',
            'email'       => 'required|email|unique:users,email,' . $user->user_id. ',user_id',
            'no_telp'     => 'required|string|regex:/^62[0-9]{9,13}$/|max:15',
            'nama_jualan' => 'required|string|max:255',
            'foto_rombong'=> 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'      => 'required|in:pending,approve,rejected',
        ], [
            'no_telp.regex' => 'Nomor telepon harus dimulai dengan 62 dan diikuti 9-13 digit angka.',
            'status.in' => 'Status harus salah satu dari: pending, approve, rejected.',
        ]);

        try {
            // Update user data (prevent role modification for security)
            $userData = $request->only(['name','alamat','email','no_telp','status']);
            $user->update($userData);

            // Update atau create rombong data
            $rombong = $user->rombong ?: new \App\Models\rombong();
            $rombong->user_id = $user->user_id;
            $rombong->nama_jualan = $request->nama_jualan;

            if ($request->hasFile('foto_rombong')) {
                // Hapus foto lama jika ada
                if ($rombong->foto_rombong && \Storage::disk('public')->exists($rombong->foto_rombong)) {
                    \Storage::disk('public')->delete($rombong->foto_rombong);
                }
                $rombong->foto_rombong = $request->file('foto_rombong')->store('rombong', 'public');
            }

            $rombong->save();

            return redirect()->route('admin.user.index')
                ->with('success', 'User berhasil diperbarui dengan aman.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data. Silakan coba lagi.']);
        }
    }

    // Hapus user
    public function destroy(User $user)
    {
        try {
            // Security: Prevent admin from deleting other admin accounts
            if ($user->role === 'admin') {
                return redirect()->route('admin.user.index')
                    ->withErrors(['error' => 'Tidak dapat menghapus akun admin lain untuk keamanan sistem.']);
            }

            // Delete related rombong photos from storage
            if ($user->rombong && $user->rombong->foto_rombong) {
                if (\Storage::disk('public')->exists($user->rombong->foto_rombong)) {
                    \Storage::disk('public')->delete($user->rombong->foto_rombong);
                }
            }

            // Delete user (will cascade delete related data)
            $user->delete();
            
            return redirect()->route('admin.user.index')
                ->with('success', 'User berhasil dihapus dengan aman.');

        } catch (\Exception $e) {
            return redirect()->route('admin.user.index')
                ->withErrors(['error' => 'Terjadi kesalahan saat menghapus user. Silakan coba lagi.']);
        }
    }
}
