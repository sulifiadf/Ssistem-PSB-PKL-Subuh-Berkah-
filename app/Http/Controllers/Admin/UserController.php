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
            'alamat'      => 'required|string',
            'email'       => 'required|email|unique:users,email',
            'no_telp'     => 'required|string|max:15',
            'nama_jualan' => 'required|string|max:255',
            'password'    => 'required|string|min:6|confirmed',
            'role'        => 'required|in:user,admin',
            'foto_rombong'=> 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'      => 'required|in:tetap,sementara,pending,approve,rejected',
        ]);

        $data = $request->only([
            'name','alamat','email','no_telp','nama_jualan','status','role'
        ]);
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('foto_rombong')) {
            $data['foto_rombong'] = $request->file('foto_rombong')->store('rombong', 'public');
        }

        User::create($data);

        return redirect()->route('admin.user.index')
            ->with('success', 'User berhasil ditambahkan');
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
            'alamat'      => 'required|string',
            'email'       => 'required|email|unique:users,email,' . $user->user_id. ',user_id',
            'no_telp'     => 'required|string|max:15',
            'nama_jualan' => 'required|string|max:255',
            'foto_rombong'=> 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status'      => 'required|in:tetap,sementara,pending,approve,rejected',
        ]);

        $data = $request->only(['name','alamat','email','no_telp','nama_jualan','status']);

        if ($request->hasFile('foto_rombong')) {
            $data['foto_rombong'] = $request->file('foto_rombong')->store('rombong', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.user.index')
            ->with('success', 'User berhasil diperbarui');
    }

    // Hapus user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.user.index')
            ->with('success', 'User berhasil dihapus');
    }
}
