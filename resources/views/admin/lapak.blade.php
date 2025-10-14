@extends('master.masterAdmin')
@section('title', 'Lapak Anggota')
@section('content')

    <style>
        .checkbox-container {
            max-height: 200px;
            overflow-y: auto;
        }

        .anggota-item {
            transition: all 0.2s ease;
        }

        .anggota-item:hover {
            background-color: #f8fafc;
        }
    </style>

    <div class="min-h-screen bg-gray-100">
        <div class="container mx-auto px-4 py-8">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Kelola Lapak Anggota</h1>
                <p class="text-gray-600">Kelola lapak dan anggota dalam sistem</p>
            </div>

            <!-- Alert -->
            @if (session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Button Tambah Lapak -->
            <div class="mb-6">
                <button onclick="document.getElementById('formTambah').classList.toggle('hidden')"
                    class="bg-[#b59356] text-white px-4 py-2 rounded-lg shadow hover:bg-[#a08347] transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Lapak
                </button>
            </div>

            <!-- Form Tambah Lapak -->
            <div id="formTambah" class="hidden bg-white rounded-xl shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold mb-4">Tambah Lapak Baru</h3>
                <form action="{{ route('admin.lapak.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Lapak <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lapak" required
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#b59356]"
                            placeholder="Masukkan nama lapak">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Pilih Anggota (Opsional)</label>
                        <div class="border rounded p-3 max-h-40 overflow-y-auto bg-gray-50">
                            @forelse($rombongs as $rombong)
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" name="rombong_ids[]" value="{{ $rombong->rombong_id }}"
                                        id="rombong_{{ $rombong->rombong_id }}" class="mr-2">
                                    <label for="rombong_{{ $rombong->rombong_id }}" class="text-sm">
                                        {{ $rombong->nama_jualan }} - {{ $rombong->user->name ?? 'Tanpa User' }}
                                        <span class="text-gray-500">({{ ucfirst($rombong->jenis ?? 'tetap') }})</span>
                                    </label>
                                </div>
                            @empty
                                <p class="text-gray-500 text-sm">Belum ada rombong tersedia</p>
                            @endforelse
                        </div>
                        <p class="text-xs text-gray-500 mt-1">💡 Anda bisa membuat lapak kosong dan menambah anggota nanti
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-[#b59356] text-white px-4 py-2 rounded hover:bg-[#a08347] transition duration-200">
                            Simpan Lapak
                        </button>
                        <button type="button" onclick="document.getElementById('formTambah').classList.add('hidden')"
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-200">
                            Batal
                        </button>
                    </div>
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
                            <tr class="border-b-2 border-gray-200">
                                <th class="px-6 py-4 text-left font-semibold">Nama Lapak</th>
                                <th class="px-6 py-4 text-left font-semibold">Anggota</th>
                                <th class="px-6 py-4 text-left font-semibold">Status</th>
                                <th class="px-6 py-4 text-left font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lapaks as $lapak)
                                <tr class="hover:bg-gray-50 border-b-2 border-gray-200">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $lapak->nama_lapak }}</div>
                                    </td>

                                    <!-- Anggota -->
                                    <td class="px-6 py-4">
                                        @if ($lapak->rombongs->count() > 0)
                                            <div class="space-y-1">
                                                @foreach ($lapak->rombongs as $rombong)
                                                    <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                                        <div class="text-sm">
                                                            <span class="font-medium">{{ $rombong->nama_jualan }}</span><br>
                                                            <span
                                                                class="text-gray-600">{{ $rombong->user->name ?? 'Tanpa User' }}</span>
                                                        </div>
                                                        <form method="POST"
                                                            action="{{ route('admin.lapak.remove-anggota') }}"
                                                            class="inline"
                                                            onsubmit="return confirm('Yakin hapus anggota ini dari lapak?')">
                                                            @csrf
                                                            <input type="hidden" name="lapak_id"
                                                                value="{{ $lapak->lapak_id }}">
                                                            <input type="hidden" name="rombong_id"
                                                                value="{{ $rombong->rombong_id }}">
                                                            <button type="submit"
                                                                class="bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-800 w-6 h-6 rounded text-xs flex items-center justify-center transition duration-200"
                                                                title="Hapus anggota">
                                                                ✕
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <!-- Tombol Tambah Anggota -->
                                            <button
                                                onclick="document.getElementById('formTambahAnggota-{{ $lapak->lapak_id }}').classList.toggle('hidden')"
                                                class="mt-2 text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                + Tambah Anggota
                                            </button>
                                        @else
                                            <div class="text-center">
                                                <span class="text-gray-500 text-sm block mb-2">Belum ada anggota</span>
                                                <button
                                                    onclick="document.getElementById('formTambahAnggota-{{ $lapak->lapak_id }}').classList.toggle('hidden')"
                                                    class="bg-blue-500 text-white px-3 py-1 rounded text-xs hover:bg-blue-600">
                                                    + Tambah Anggota
                                                </button>
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        @if ($lapak->rombongs->count() > 0)
                                            <div class="space-y-1">
                                                @foreach ($lapak->rombongs as $rombong)
                                                    <div class="flex items-center">
                                                        <span
                                                            class="px-2 py-1 rounded-full text-xs font-medium 
                                                    {{ $rombong->jenis == 'tetap' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                                            {{ ucfirst($rombong->jenis) }}
                                                        </span>
                                                        @if ($rombong->berlaku_hingga)
                                                            <span class="ml-2 text-xs text-gray-500">
                                                                s/d
                                                                {{ \Carbon\Carbon::parse($rombong->berlaku_hingga)->format('d/m/Y') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-500 text-sm">-</span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-6 py-4 flex gap-2">
                                        <button type="button"
                                            onclick="document.getElementById('formEdit-{{ $lapak->lapak_id }}').classList.toggle('hidden')"
                                            class="text-blue-600 hover:underline">Edit</button>
                                        <form method="POST" action="{{ route('admin.lapak.destroy', $lapak->lapak_id) }}"
                                            onsubmit="return confirm('Yakin hapus lapak ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Form Tambah Anggota (Inline) -->
                                <tr id="formTambahAnggota-{{ $lapak->lapak_id }}"
                                    class="hidden bg-blue-50 border-b-2 border-gray-200">
                                    <td colspan="4" class="p-4">
                                        <div class="bg-white p-4 rounded shadow">
                                            <h4 class="font-medium mb-3">Tambah Anggota ke {{ $lapak->nama_lapak }}</h4>
                                            <form action="{{ route('admin.lapak.add-anggota') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="lapak_id" value="{{ $lapak->lapak_id }}">
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium mb-2">Pilih Anggota:</label>
                                                    <div class="border rounded p-3 max-h-32 overflow-y-auto bg-gray-50">
                                                        @php
                                                            // Filter rombong yang belum ada di lapak ini
                                                            $availableRombongs = $rombongs->whereNotIn(
                                                                'rombong_id',
                                                                $lapak->rombongs->pluck('rombong_id'),
                                                            );
                                                        @endphp
                                                        @forelse($availableRombongs as $rombong)
                                                            <div class="flex items-center mb-2">
                                                                <input type="checkbox" name="rombong_ids[]"
                                                                    value="{{ $rombong->rombong_id }}"
                                                                    id="add_rombong_{{ $lapak->lapak_id }}_{{ $rombong->rombong_id }}"
                                                                    class="mr-2">
                                                                <label
                                                                    for="add_rombong_{{ $lapak->lapak_id }}_{{ $rombong->rombong_id }}"
                                                                    class="text-sm">
                                                                    {{ $rombong->nama_jualan }} -
                                                                    {{ $rombong->user->name ?? 'Tanpa User' }}
                                                                    <span
                                                                        class="text-gray-500">({{ ucfirst($rombong->jenis ?? 'tetap') }})</span>
                                                                </label>
                                                            </div>
                                                        @empty
                                                            <p class="text-gray-500 text-sm">Semua anggota sudah ada di
                                                                lapak atau tidak ada anggota tersedia</p>
                                                        @endforelse
                                                    </div>
                                                </div>
                                                <div class="flex gap-2">
                                                    <button type="submit"
                                                        class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">
                                                        Tambah Anggota
                                                    </button>
                                                    <button type="button"
                                                        onclick="document.getElementById('formTambahAnggota-{{ $lapak->lapak_id }}').classList.add('hidden')"
                                                        class="bg-gray-500 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-600">
                                                        Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Inline Form Edit -->
                                <tr id="formEdit-{{ $lapak->lapak_id }}"
                                    class="hidden bg-gray-50 border-b-2 border-gray-200">
                                    <td colspan="4" class="p-4">
                                        <div class="bg-white p-4 rounded shadow">
                                            <h4 class="font-medium mb-3">Edit {{ $lapak->nama_lapak }}</h4>
                                            <form action="{{ route('admin.lapak.update', $lapak->lapak_id) }}"
                                                method="POST" class="space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Nama Lapak</label>
                                                    <input type="text" name="nama_lapak"
                                                        value="{{ $lapak->nama_lapak }}"
                                                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#b59356]">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-2">Kelola Anggota
                                                        Lapak</label>
                                                    <div class="border rounded p-3 max-h-40 overflow-y-auto bg-gray-50">
                                                        @foreach ($rombongs as $rombong)
                                                            <div class="flex items-center mb-2">
                                                                <input type="checkbox" name="rombong_ids[]"
                                                                    value="{{ $rombong->rombong_id }}"
                                                                    id="edit_rombong_{{ $lapak->lapak_id }}_{{ $rombong->rombong_id }}"
                                                                    {{ $lapak->rombongs->contains('rombong_id', $rombong->rombong_id) ? 'checked' : '' }}
                                                                    class="mr-2">
                                                                <label
                                                                    for="edit_rombong_{{ $lapak->lapak_id }}_{{ $rombong->rombong_id }}"
                                                                    class="text-sm">
                                                                    {{ $rombong->nama_jualan }} -
                                                                    {{ $rombong->user->name ?? 'Tanpa User' }}
                                                                    <span
                                                                        class="text-gray-500">({{ ucfirst($rombong->jenis ?? 'tetap') }})</span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">💡 Centang untuk menambah,
                                                        hilangkan centang untuk menghapus anggota</p>
                                                </div>
                                                <div class="flex gap-3">
                                                    <button type="submit"
                                                        class="bg-[#b59356] text-white px-4 py-2 rounded hover:bg-[#a08347] transition duration-200">
                                                        Simpan Perubahan
                                                    </button>
                                                    <button type="button"
                                                        onclick="document.getElementById('formEdit-{{ $lapak->lapak_id }}').classList.add('hidden')"
                                                        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-200">
                                                        Batal
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
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
