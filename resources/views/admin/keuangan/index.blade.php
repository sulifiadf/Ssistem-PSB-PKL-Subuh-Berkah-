@extends('master.masterAdmin')
@section('tittle', 'Keuangan')
@section('content')

    <div class="min-h-screen bg-gray-100 p-4 sm:p-6">

        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Manajemen Keuangan</h1>
        </div>

        {{-- Ringkasan --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-green-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold text-green-800">Total Pemasukan</h3>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold text-red-800">Total Pengeluaran</h3>
                <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg text-center">
                <h3 class="text-lg font-semibold text-blue-800">Saldo</h3>
                <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Tambah Data --}}
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <h3 class="font-semibold mb-3">Tambah Data Keuangan</h3>
            <form action="{{ route('admin.keuangan.store') }}" method="POST" class="grid grid-cols-4 gap-4">
                @csrf
                <input type="date" name="tanggal" class="border p-2 rounded" required>
                <select name="jenis" class="border p-2 rounded" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
                <input type="number" name="jumlah" step="0.01" class="border p-2 rounded" placeholder="Jumlah"
                    required>
                <input type="text" name="keterangan" class="border p-2 rounded" placeholder="Keterangan">
                <button type="submit"
                    class="col-span-4 bg-[#b59356] hover:bg-[#CFB47D] text-white px-4 py-2 rounded mt-2">Simpan</button>
            </form>
        </div>

        {{-- List Data --}}
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold mb-3">Daftar Keuangan</h3>
            <table class="w-full border">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border p-2">Tanggal</th>
                        <th class="border p-2">Jenis</th>
                        <th class="border p-2">Jumlah</th>
                        <th class="border p-2">Keterangan</th>
                        <th class="border p-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($keuangans as $item)
                        <tr>
                            <td class="border p-2">{{ $item->tanggal }}</td>
                            <td class="border p-2 capitalize">{{ $item->jenis }}</td>
                            <td class="border p-2">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                            <td class="border p-2">{{ $item->keterangan }}</td>
                            <td class="border p-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.keuangan.edit', $item->keuangan_id) }}"
                                    class="inline-block bg-yellow-500 text-white px-3 py-1 rounded ">
                                    Edit
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('admin.keuangan.destroy', $item->keuangan_id) }}" method="POST"
                                    class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded"
                                        onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if ($keuangans->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center p-4">Belum ada data</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
@endsection
