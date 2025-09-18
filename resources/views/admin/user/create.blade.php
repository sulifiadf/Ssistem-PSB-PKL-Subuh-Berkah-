@extends('master.masterAdmin')
@section('title', 'Tambah User')
@section('content')

<div class="max-w-3xl mx-auto bg-white shadow-xl rounded-2xl p-8 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Tambah User Baru</h2>

    @if($errors->any())
        <div class="p-3 mb-4 bg-red-100 text-red-700 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Nama</label>
            <input type="text" name="name" value="{{ old('name') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Alamat</label>
            <textarea name="alamat" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>{{ old('alamat') }}</textarea>
        </div>

        <div>
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">No. Telepon</label>
            <input type="text" name="no_telp" value="{{ old('no_telp') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Nama Jualan</label>
            <input type="text" name="nama_jualan" value="{{ old('nama_jualan') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Foto Rombong</label>
            <input type="file" name="foto_rombong" class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Password</label>
            <input type="password" name="password"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Status</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
                <option value="pending">Pending</option>
                <option value="approve">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="tetap">Tetap</option>
                <option value="sementara">Sementara</option>
            </select>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.user.index') }}" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-600 text-white hover:bg-yellow-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
