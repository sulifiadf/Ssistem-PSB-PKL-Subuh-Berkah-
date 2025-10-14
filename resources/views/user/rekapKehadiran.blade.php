@extends('master.masterUser')
@section('title', 'Rekap Kehadiran')
@section('content')

    <div class="min-h-screen bg-gray-100 flex flex-col" x-data="{ openSidebar: false, showChart: false }">
        <!-- Navbar -->
        <header
            class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] text-white p-4 flex justify-between items-center shadow-md">
            <div class="flex items-center gap-3">
                <a href="{{ route('user.dashboard') }}" class="text-white hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold">Rekap Kehadiran</h1>
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

            {{-- Filter Form --}}
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">🔍 Filter Kehadiran</h3>

                <form method="GET" action="{{ route('user.history') }}" class="space-y-4">
                    {{-- Filter Type Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Filter:</label>
                        <div class="flex gap-4">
                            <label class="flex items-center">
                                <input type="radio" name="filter_type" value="range"
                                    {{ $filterType == 'range' ? 'checked' : '' }} class="mr-2"
                                    onchange="toggleFilterInputs()">
                                <span class="text-sm">Rentang Tanggal</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="filter_type" value="month"
                                    {{ $filterType == 'month' ? 'checked' : '' }} class="mr-2"
                                    onchange="toggleFilterInputs()">
                                <span class="text-sm">Per Bulan</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="filter_type" value="year"
                                    {{ $filterType == 'year' ? 'checked' : '' }} class="mr-2"
                                    onchange="toggleFilterInputs()">
                                <span class="text-sm">Per Tahun</span>
                            </label>
                        </div>
                    </div>

                    {{-- Filter Inputs --}}
                    <div class="grid md:grid-cols-3 gap-4">
                        <div id="range-inputs" class="md:col-span-2 {{ $filterType != 'range' ? 'hidden' : '' }}">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai:</label>
                                    <input type="date" name="start_date" value="{{ $startDate }}"
                                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Selesai:</label>
                                    <input type="date" name="end_date" value="{{ $endDate }}"
                                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <div id="month-input" class="{{ $filterType != 'month' ? 'hidden' : '' }}">
                            <label class="block text-sm font-medium text-gray-700">Pilih Bulan:</label>
                            <input type="month" name="month" value="{{ $month }}"
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div id="year-input" class="{{ $filterType != 'year' ? 'hidden' : '' }}">
                            <label class="block text-sm font-medium text-gray-700">Pilih Tahun:</label>
                            <select name="year"
                                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                                @for ($i = now()->year; $i >= 2020; $i--)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>
                                        {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit"
                                class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition font-semibold w-full">
                                🔍 Filter Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $totalMasuk }}</div>
                    <div class="text-sm text-gray-500">Total Masuk</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $totalLibur }}</div>
                    <div class="text-sm text-gray-500">Total Libur</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $totalHari }}</div>
                    <div class="text-sm text-gray-500">Total Hari</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $persentaseKehadiran }}%</div>
                    <div class="text-sm text-gray-500">Persentase Hadir</div>
                </div>
            </div>

            {{-- Chart Toggle Button --}}
            @if ($dataPerBulan->count() > 0)
                <div class="mb-4">
                    <button @click="showChart = !showChart"
                        class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition">
                        <span x-text="showChart ? '📊 Sembunyikan Grafik' : '📈 Tampilkan Grafik'"></span>
                    </button>
                </div>

                {{-- Chart Container --}}
                <div x-show="showChart" x-transition class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">📊 Grafik Kehadiran Per Bulan</h3>
                    <div class="overflow-x-auto">
                        <div class="flex items-end gap-4 min-w-full" style="height: 200px;">
                            @foreach ($dataPerBulan as $data)
                                <div class="flex-1 min-w-[80px] text-center">
                                    <div class="flex flex-col items-center justify-end h-40 gap-1">
                                        {{-- Bar Masuk --}}
                                        @if ($data['masuk'] > 0)
                                            <div class="bg-green-500 rounded-t text-white text-xs font-bold flex items-center justify-center"
                                                style="height: {{ $data['total'] > 0 ? ($data['masuk'] / max($dataPerBulan->pluck('total')->toArray())) * 120 : 0 }}px; min-height: 20px;">
                                                {{ $data['masuk'] }}
                                            </div>
                                        @endif
                                        {{-- Bar Libur --}}
                                        @if ($data['libur'] > 0)
                                            <div class="bg-red-500 rounded-b text-white text-xs font-bold flex items-center justify-center"
                                                style="height: {{ $data['total'] > 0 ? ($data['libur'] / max($dataPerBulan->pluck('total')->toArray())) * 120 : 0 }}px; min-height: 20px;">
                                                {{ $data['libur'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-600 mt-2 font-medium">{{ $data['bulan'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-center gap-4 mt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-green-500 rounded"></div>
                            <span class="text-sm text-gray-600">Masuk</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-red-500 rounded"></div>
                            <span class="text-sm text-gray-600">Libur</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Data Table --}}
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">📋 Detail Kehadiran</h3>
                    <p class="text-sm text-gray-600">
                        Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hari/Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu Konfirmasi</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($kehadiranData as $index => $item)
                                <tr
                                    class="hover:bg-gray-50 {{ $item->tanggal == now()->format('Y-m-d') ? 'bg-blue-50' : '' }}">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
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
                                            $isToday = $tanggal->isToday();
                                        @endphp
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $hari }}{{ $isToday ? ' (Hari Ini)' : '' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $tanggal->format('d F Y') }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        @if ($item->waktu_konfirmasi)
                                            {{ \Carbon\Carbon::parse($item->waktu_konfirmasi)->format('H:i') }}
                                            <div class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($item->waktu_konfirmasi)->format('d/m/Y') }}
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if ($item->status == 'masuk')
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                ✅ Masuk
                                            </span>
                                        @elseif($item->status == 'libur')
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                ❌ Libur
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                ⏳ Menunggu Konfirmasi
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-2">📅</div>
                                        <div class="font-medium">Tidak ada data kehadiran</div>
                                        <div class="text-sm">Silakan pilih periode yang berbeda</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="fixed inset-0 bg-grey bg-opacity-50 z-40" x-show="openSidebar" x-transition.opacity
            @click="openSidebar = false"></div>

        {{-- Aside --}}
        <aside
            class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-50 transform -translate-x-full transition-transform"
            :class="{ 'translate-x-0': openSidebar }">
            <div class="p-4 border-b flex justify-between items-center">
                <h2 class="font-bold text-lg">Menu</h2>
                <button @click="openSidebar = false" class="text-gray-600 hover:text-black">&times;</button>
            </div>
            <nav class="p-4 space-y-3">
                <a href="{{ route('user.dashboard') }}" class="block text-gray-700 hover:text-[#b59356]">Beranda</a>
                <a href="/user/profile" class="block text-gray-700 hover:text-[#b59356]">Profile</a>
                <a href="/user/lapak" class="block text-gray-700 hover:text-[#b59356]">Lapak</a>
                <a href="/user/history"
                    class="block text-gray-700 hover:text-[#b59356] font-semibold text-[#b59356]">History</a>
            </nav>
        </aside>
    </div>

    @section('scripts')
        <script>
            function toggleFilterInputs() {
                const filterType = document.querySelector('input[name="filter_type"]:checked').value;

                // Hide all inputs
                document.getElementById('range-inputs').classList.add('hidden');
                document.getElementById('month-input').classList.add('hidden');
                document.getElementById('year-input').classList.add('hidden');

                // Show selected input
                switch (filterType) {
                    case 'range':
                        document.getElementById('range-inputs').classList.remove('hidden');
                        break;
                    case 'month':
                        document.getElementById('month-input').classList.remove('hidden');
                        break;
                    case 'year':
                        document.getElementById('year-input').classList.remove('hidden');
                        break;
                }
            }

            // Initialize filter inputs on page load
            document.addEventListener('DOMContentLoaded', function() {
                toggleFilterInputs();
            });
        </script>

        <!-- Alpine.js -->
        <script src="//unpkg.com/alpinejs" defer></script>
    @endsection

@endsection
