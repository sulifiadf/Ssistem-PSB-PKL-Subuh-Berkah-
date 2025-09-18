@extends('master.masterAdmin')
@section('tittle', 'Edit Keuangan')
@section('content')

<div class="min-h-screen bg-gray-100 p-4 sm:p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Data Keuangan</h1>

    <form action="{{ route('admin.keuangan.update', $keuangan->keuangan_id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $keuangan->tanggal }}" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block">Jenis</label>
            <select name="jenis" class="border p-2 rounded w-full">
                <option value="pemasukan" {{ $keuangan->jenis == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ $keuangan->jenis == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>

        <div>
            <label class="block">Jumlah</label>
            <input type="number" step="0.01" name="jumlah" value="{{ $keuangan->jumlah }}" class="border p-2 rounded w-full">
        </div>

        <div>
            <label class="block">Keterangan</label>
            <input type="text" name="keterangan" value="{{ $keuangan->keterangan }}" class="border p-2 rounded w-full">
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan Perubahan</button>
        <a href="{{ route('admin.keuangan.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</a>
    </form>
</div>

@endsection
