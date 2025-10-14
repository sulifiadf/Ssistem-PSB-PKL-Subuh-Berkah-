@extends('master.masterUser')
@section('title', 'Rekap Keuangan')
@section('content')

    <div class="min-h-screen bg-gray-100 flex flex-col pb-16">
        <!-- Navbar -->
        <header
            class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] text-white p-4 flex justify-between items-center shadow-md">
            <div class="flex items-center gap-3">
                <a href="{{ route('user.dashboard') }}" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold">Rekap Keuangan</h1>
            </div>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <x-heroicon-o-arrow-right-start-on-rectangle class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24"
                    stroke="#ffff" />
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </header>

        <!-- Konten Utama -->
        <div class="flex-1 overflow-y-auto p-4">

            {{-- Tabel Data Keuangan --}}
            <div class="bg-white rounded-lg shadow p-4 mb-20">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                    <h3 class="font-semibold text-lg">Data Keuangan</h3>

                    {{-- Filter Pencarian --}}
                    <form method="GET" action="{{ route('user.keuangan') }}" class="flex flex-col md:flex-row gap-2">
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                            class="border rounded-lg px-3 py-2 text-sm" placeholder="Pilih tanggal">
                        <button type="submit"
                            class="bg-[#b59356] hover:bg-[#CFB47D] text-white px-4 py-2 rounded-lg text-sm">
                            Filter
                        </button>
                        <a href="{{ route('user.keuangan') }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm text-center">
                            Reset
                        </a>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse">
                        <thead>
                            <tr class="border-b border-gray-400">
                                <th class="text-center font-semibold text-gray-400 px-2 py-3">#</th>
                                <th class="text-center font-semibold text-gray-400 px-2 py-3">Tanggal</th>
                                <th class="text-center font-semibold text-gray-400 px-2 py-3">Jenis</th>
                                <th class="text-center font-semibold text-gray-400 px-2 py-3">Jumlah</th>
                                <th class="text-center font-semibold text-gray-400 px-2 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($keuangans as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="text-center py-3 px-2 text-sm">
                                        {{ ($keuangans->currentPage() - 1) * $keuangans->perPage() + $index + 1 }}
                                    </td>
                                    <td class="text-center py-3 px-2 text-sm">
                                        @php
                                            $tanggal = \Carbon\Carbon::parse($item->tanggal);
                                            $hariIndonesia = [
                                                'Sunday' => 'Minggu',
                                                'Monday' => 'Senin',
                                                'Tuesday' => 'Selasa',
                                                'Wednesday' => 'Rabu',
                                                'Thursday' => 'Kamis',
                                                'Friday' => 'Jumat',
                                                'Saturday' => 'Sabtu',
                                            ];
                                            $hari = $hariIndonesia[$tanggal->format('l')] ?? $tanggal->format('l');
                                        @endphp
                                        <div class="font-medium">{{ $tanggal->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $hari }}</div>
                                    </td>
                                    <td class="text-center py-3 px-2">
                                        @if ($item->jenis == 'pemasukan')
                                            <span
                                                class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">
                                                Pemasukan
                                            </span>
                                        @else
                                            <span
                                                class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">
                                                Pengeluaran
                                            </span>
                                        @endif
                                    </td>
                                    <td
                                        class="text-center py-3 px-2 text-sm font-semibold {{ $item->jenis == 'pemasukan' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $item->jenis == 'pemasukan' ? '+' : '-' }} Rp
                                        {{ number_format($item->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center py-3 px-2 text-sm">
                                        {{ $item->keterangan ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500 py-8">
                                        @if (request('tanggal'))
                                            Tidak ada data keuangan untuk tanggal {{ request('tanggal') }}
                                        @else
                                            Belum ada data keuangan
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($keuangans->hasPages())
                    <div class="mt-4">
                        {{ $keuangans->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
