@extends('master.masterAdmin')
@section('title', 'Edit User')
@section('content')

<div class="max-w-3xl mx-auto bg-white shadow-xl rounded-2xl p-8 mt-8">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Edit Data User</h2>

    @if(session('success'))
        <div class="p-3 mb-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-3 mb-4 bg-red-100 text-red-700 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.user.update', $user->user_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block mb-1 font-medium">Nama</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Alamat</label>
            <textarea name="alamat" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>{{ old('alamat', $user->alamat) }}</textarea>
        </div>

        <div>
            <label class="block mb-1 font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">No. Telepon</label>
            <input type="text" name="no_telp" value="{{ old('no_telp', $user->no_telp) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Nama Jualan</label>
            <input type="text" name="nama_jualan" value="{{ old('nama_jualan', $user->nama_jualan) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
        </div>

        <div>
            <label class="block mb-1 font-medium">Foto Rombong</label><br>
            @if($user->foto_rombong)
                <img src="{{ asset('storage/'.$user->foto_rombong) }}" class="w-24 h-24 object-cover rounded-lg mb-2">
            @endif
            <input type="file" name="foto_rombong" class="w-full border rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Status</label>
            <select name="status" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
                <option value="tetap" {{ $user->status == 'tetap' ? 'selected' : '' }}>Tetap</option>
                <option value="sementara" {{ $user->status == 'sementara' ? 'selected' : '' }}>Sementara</option>
                <option value="pending" {{ $user->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approve" {{ $user->status == 'approve' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $user->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.user.index') }}" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
            <button type="submit" class="px-4 py-2 rounded-lg bg-yellow-600 text-white hover:bg-yellow-700">Update</button>
        </div>
    </form>
</div>
@endsection
