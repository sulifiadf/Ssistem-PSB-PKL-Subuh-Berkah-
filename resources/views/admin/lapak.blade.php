@extends('master.masterAdmin')
@section('title', 'Lapak Anggota')
@section('content')

<div class="min-h-screen bg-gray-100">
    <div class="container mx-auto px-4 py-8">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Kelola Lapak Anggota</h1>
            <p class="text-gray-600">Kelola lapak dan anggota dalam sistem</p>
        </div>

        <!-- Alert -->
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Button Tambah Lapak -->
        <div class="mb-6">
            <button onclick="document.getElementById('formTambah').classList.toggle('hidden')"
                class="bg-[#b59356] text-white px-4 py-2 rounded-lg shadow hover:bg-[#a08347] transition duration-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Lapak
            </button>
        </div>

        <!-- Form Tambah Lapak -->
        <div id="formTambah" class="hidden bg-white rounded-xl shadow-lg p-6 mb-8">
            <form action="{{ route('admin.lapak.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lapak</label>
                    <input type="text" name="nama_lapak" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Pilih Rombong</label>
                    <select name="rombong_ids[]" multiple class="w-full border rounded px-3 py-2">
                        @foreach($rombongs as $rombong)
                            <option value="{{ $rombong->rombong_id }}">{{ $rombong->nama_jualan }} ({{ $rombong->user->name ?? 'Tanpa User' }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500">Gunakan CTRL / CMD untuk memilih lebih dari satu.</p>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
            </form>
        </div>

        <!-- Tabel Lapak -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-black">Detail Lapak & Anggota</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Nama Lapak</th>
                            <th class="px-6 py-4 text-left font-semibold">Anggota</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lapaks as $lapak)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $lapak->nama_lapak }}</div>
                            </td>

                            <!-- Anggota -->
                            <td class="px-6 py-4">
                                @if($lapak->rombongs->count() > 0)
                                    @foreach($lapak->rombongs as $rombong)
                                        <div class="text-sm text-gray-900">
                                            {{ $rombong->nama_jualan }} 
                                            ({{ $rombong->user->name ?? 'Tanpa User' }})
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-gray-500 text-sm">Belum ada anggota</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                @if($lapak->rombongs->count() > 0)
                                    @foreach($lapak->rombongs as $rombong)
                                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                                            {{ $rombong->jenis == 'tetap' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($rombong->jenis) }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="text-gray-500 text-sm">-</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-6 py-4 flex gap-2">
                                <button type="button" onclick="document.getElementById('formEdit-{{ $lapak->lapak_id }}').classList.toggle('hidden')" class="text-blue-600 hover:underline">Edit</button>
                                <form method="POST" action="{{ route('admin.lapak.destroy', $lapak->lapak_id) }}" onsubmit="return confirm('Yakin hapus lapak ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Inline Form Edit -->
                        <tr id="formEdit-{{ $lapak->lapak_id }}" class="hidden bg-gray-50">
                            <td colspan="4" class="p-4">
                                <form action="{{ route('admin.lapak.update', $lapak->lapak_id) }}" method="POST" class="space-y-4 bg-white p-4 rounded shadow">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Nama Lapak</label>
                                        <input type="text" name="nama_lapak" value="{{ $lapak->nama_lapak }}" class="w-full border rounded px-3 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Pilih Anggota</label>
                                        <select name="rombong_ids[]" multiple class="w-full border rounded px-3 py-2">
                                            @foreach($rombongs as $rombong)
                                                <option value="{{ $rombong->rombong_id }}" {{ $lapak->rombongs->contains('rombong_id', $rombong->rombong_id) ? 'selected' : '' }}>
                                                    {{ $rombong->nama_jualan }} ({{ $rombong->user->name ?? 'Tanpa User' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                                        <button type="button" onclick="document.getElementById('formEdit-{{ $lapak->lapak_id }}').classList.add('hidden')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-500">Belum ada lapak</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection
