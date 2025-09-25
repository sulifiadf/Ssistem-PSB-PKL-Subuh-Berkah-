@extends('master.masterUser')
@section('title', 'dashboard')
@section('content')

    <div class="min-h-screen bg-gray-100 flex flex-col" x-data="{ openSidebar: false }">
        <!-- Navbar -->
        <header
            class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] text-white p-4 flex justify-between items-center shadow-md">
            <img src="{{ asset('img/logo2.png') }}" alt="logo" class="w-12 h-12 object-contain">
            <h1 class="text-lg font-bold">Beranda User</h1>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <x-heroicon-o-arrow-right-start-on-rectangle class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24"
                    stroke="#ffff" />
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </header>

        <!-- Konten Utama -->
        <div class="cols-1 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-1 gap-4 mb-4">
                <!-- Card uang kas -->
                <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($jumlahUangKas) }}</span>
                    <x-heroicon-o-currency-dollar class="h-6 w-6" stroke="#b59356" />
                    <span class="text-sm font-semibold text-gray-500">Jumlah Uang Kas</span>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-2 gap-4 mb-4">
                {{-- anggota --}}
                <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                    <span class="text-2xl font-bold text-blue-600">{{ $totalTetap }}</span>
                    <x-heroicon-o-users class="h-6 w-6" stroke="#b59356" />
                    <span class="text-sm font-semibold text-gray-500">Anggota Tetap</span>
                </div>
                <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                    <span class="text-2xl font-bold text-blue-600">{{ $totalSementara }}</span>
                    <x-heroicon-o-users class="h-6 w-6" stroke="#b59356" />
                    <span class="text-sm font-semibold text-gray-500">Anggota Sementara</span>
                </div>
            </div>

            {{-- lapak + model --}}
            <div x-data="{ open: false, foto: '', nama: '', pemilik: '', status: '' }">

                {{-- detail rombong (modal) --}}
                <div x-show="open" x-transition class="fixed inset-0  bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
                        <button @click="open=false"
                            class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl font-bold">
                            ✕
                        </button>

                        <div class="text-center">
                            <img :src="foto" alt="Foto Rombong"
                                class="w-32 h-32 object-cover mx-auto rounded-lg mb-4 border-2 border-gray-200">
                            <h4 class="font-bold text-lg text-gray-700 mb-2" x-text="nama"></h4>
                            <p class="text-sm text-gray-600 mb-2" x-text="'Pemilik: ' + pemilik"></p>
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800': status === 'masuk',
                                    'bg-red-100 text-red-800': status === 'libur',
                                    'bg-yellow-100 text-yellow-800': status === 'Menunggu Konfirmasi',
                                    'bg-gray-100 text-gray-800': status === 'Standby'
                                }"
                                x-text="status">
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Lapak --}}
                <div class="bg-white rounded-lg shadow p-4 mb-4">
                    <h3 class="font-semibold text-lg mb-2">Lapak Anggota</h3>

                    <div class="flex gap-4 overflow-x-auto pb-2">
                        @foreach ($lapaks as $lapak)
                            <div class="flex flex-col min-w-[220px] border rounded-lg shadow p-3"
                                data-lapak-id="{{ $lapak->lapak_id }}" ondragover="event.preventDefault();"
                                ondrop="dropRombong(event, {{ $lapak->lapak_id }})">

                                <h4 class="text-sm font-semibold text-center mb-2 bg-gray-100 p-2 rounded">
                                    {{ $lapak->nama_lapak }}
                                </h4>

                                <div class="rombong-container">
                                    @if ($lapak->rombongs->count() > 0)
                                        @php
                                            $adaYangMasuk = false;
                                            $rombongAktifDitemukan = false;

                                            // Cek status rombong dalam lapak ini
                                            foreach ($lapak->rombongs as $rombong) {
                                                if ($rombong->user) {
                                                    $kehadiran = \App\Models\kehadiran::where(
                                                        'user_id',
                                                        $rombong->user->user_id,
                                                    )
                                                        ->whereDate('tanggal', now()->toDateString())
                                                        ->first();
                                                    if ($kehadiran && $kehadiran->status == 'masuk') {
                                                        $adaYangMasuk = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp

                                        @foreach ($lapak->rombongs as $index => $item)
                                            @php
                                                $today = now()->toDateString();
                                                $kehadiran = \App\Models\kehadiran::where('user_id', $item->user_id)
                                                    ->whereDate('tanggal', $today)
                                                    ->first();

                                                $urutanText = '#' . ($index + 1);

                                                // Default status
                                                $warnaButton = 'bg-gray-300 cursor-not-allowed';
                                                $statusText = 'STANDBY';
                                                $badgeColor = 'bg-gray-100 text-gray-800';
                                                $isDisabled = true;

                                                // Cek jika ada data kehadiran
                                                if ($kehadiran) {
                                                    if ($kehadiran->status == 'masuk') {
                                                        $warnaButton = 'bg-green-500 hover:bg-green-600';
                                                        $statusText = 'MASUK';
                                                        $badgeColor = 'bg-green-100 text-green-800';
                                                        $isDisabled = true;
                                                    } elseif ($kehadiran->status == 'libur') {
                                                        $warnaButton = 'bg-red-400 cursor-not-allowed';
                                                        $statusText = 'LIBUR';
                                                        $badgeColor = 'bg-red-100 text-red-800';
                                                        $isDisabled = true;
                                                    }
                                                } else {
                                                    // Belum konfirmasi
                                                    if (!$adaYangMasuk) {
                                                        // Cek jika ini rombong pertama yang belum konfirmasi
                                                        $isRombongAktif = true;

                                                        $todayForLoop = now()->toDateString();

                                                        foreach ($lapak->rombongs as $prevIndex => $prevRombong) {
                                                            if ($prevIndex < $index) {
                                                                $prevKehadiran = \App\Models\Kehadiran::where(
                                                                    'user_id',
                                                                    $prevRombong->user_id,
                                                                )
                                                                    ->whereDate('tanggal', $todayForLoop)
                                                                    ->first();
                                                                if (
                                                                    !$prevKehadiran ||
                                                                    $prevKehadiran->status !== 'libur'
                                                                ) {
                                                                    $isRombongAktif = false;
                                                                    break;
                                                                }
                                                            }
                                                        }

                                                        if ($isRombongAktif && !$isLewatJam12) {
                                                            $warnaButton =
                                                                'bg-yellow-500 hover:bg-yellow-600 animate-pulse';
                                                            $statusText = 'KONFIRMASI SEKARANG';
                                                            $badgeColor = 'bg-yellow-100 text-yellow-800';
                                                            $isDisabled = false;
                                                        } else {
                                                            $warnaButton = 'bg-gray-300 cursor-not-allowed';
                                                            $statusText = 'MENUNGGU GILIRAN';
                                                            $badgeColor = 'bg-gray-100 text-gray-800';
                                                            $isDisabled = true;
                                                        }
                                                    } else {
                                                        $warnaButton = 'bg-gray-300 cursor-not-allowed';
                                                        $statusText = 'SUDAH ADA YANG MASUK';
                                                        $badgeColor = 'bg-gray-100 text-gray-800';
                                                        $isDisabled = true;
                                                    }
                                                }

                                                $fotoRombong = $item->foto_rombong
                                                    ? asset('storage/' . $item->foto_rombong)
                                                    : asset('img/no-image.png');
                                            @endphp

                                            <div class="flex flex-col items-center min-w-[120px] mb-3" draggable="true"
                                                ondragstart="dragRombong(event, {{ $item->rombong_id }}, {{ $lapak->lapak_id }})"
                                                data-user-id="{{ $item->user_id }}">

                                                {{-- Badge urutan --}}
                                                <span
                                                    class="text-xs {{ $badgeColor }} px-2 py-1 rounded-full mb-1 font-semibold">
                                                    {{ $urutanText }}
                                                </span>

                                                {{-- Tombol Rombong --}}
                                                <button
                                                    class="{{ $warnaButton }} text-white text-sm font-medium rounded-lg px-3 py-2 shadow transition min-h-[40px] w-full text-center {{ !$isDisabled ? 'hover:scale-105' : '' }}"
                                                    title="{{ $item->user_name }} - {{ $statusText }}"
                                                    @click="
                                                        open = true; 
                                                        foto = '{{ $fotoRombong }}'; 
                                                        nama = '{{ $item->nama_jualan }}';
                                                        pemilik = '{{ $item->name }}';
                                                        status = '{{ $statusText }}'
                                                    "
                                                    data-rombong-id="{{ $item->rombong_id }}"
                                                    {{ $isDisabled ? 'disabled' : '' }}>
                                                    <span class="block truncate">{{ $item->nama_jualan }}</span>
                                                    <small
                                                        class="block text-xs opacity-75 truncate">{{ $item->user_name }}</small>
                                                    <small
                                                        class="block text-xs font-bold status-text">{{ $statusText }}</small>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-sm text-center">Belum ada anggota</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- form tambah anggota --}}
            @if ($buttonAnggotaAktif)
                <div id="formTambah" class="bg-white rounded-lg shadow-lg p-4 mb-4">
                    <h3 class="font-semibold text-lg mb-3">Ajukan Anggota Baru</h3>
                    <form id="rombongForm" action="{{ route('user.rombong.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-medium">Pilih Lapak</label>
                            <select name="lapak_id" id="lapak_id" class="form-control py-2 px-2 border rounded-lg w-full"
                                required>
                                <option value="">-- Pilih Lapak --</option>
                                @foreach ($lapaks as $lapak)
                                    <option value="{{ $lapak->lapak_id }}">{{ $lapak->nama_lapak }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Nama Usaha</label>

                            @if (Auth::user()->rombong && Auth::user()->rombong->nama_jualan)
                                {{-- Sudah ada nama usaha di profil --}}
                                <input type="text" name="nama_jualan" id="nama_jualan"
                                    class="w-full border rounded-lg px-3 py-2"
                                    value="{{ Auth::user()->rombong->nama_jualan }}" required>
                            @else
                                {{-- Belum melengkapi profil --}}
                                <input type="text" name="nama_jualan" id="nama_jualan"
                                    class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed"
                                    value="" placeholder="Lengkapi profile terlebih dahulu" readonly required>
                                <p class="text-sm text-red-500 mt-1">⚠ Silakan lengkapi profil Anda terlebih dahulu.</p>
                            @endif
                        </div>

                        <button type="submit"
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg w-full hover:bg-blue-600 transition">
                            Ajukan Permohonan
                        </button>
                    </form>
                    <div id="responseMessage" class="mt-2 text-sm"></div>
                </div>
            @endif

            {{-- tombol konfirmasi kehadiran --}}
            <div class="bg-white rounded-lg shadow p-4 mb-4 text-center">
                <h3 class="font-semibold text-lg mb-2">Konfirmasi Kehadiran Hari Ini</h3>

                @if ($isLewatJam12)
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-3">
                        <p class="font-semibold">⏰ Batas Waktu Absensi</p>
                        <p class="text-sm">Batas absensi telah berakhir (jam 12:00). Status otomatis menjadi Libur.</p>
                    </div>
                @endif

                @if (!$sudahKonfirmasiHariIni && !$isLewatJam12 && $buttonKonfirmasiAktif)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                        <p class="text-green-700 font-semibold">✅ Giliran Anda Sekarang!</p>
                        <p class="text-sm text-green-600">Silakan konfirmasi kehadiran Anda.</p>
                    </div>

                    @if (session('success'))
                        <div class="bg-green-500 text-white px-4 py-2 rounded mb-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-500 text-white px-4 py-2 rounded mb-3">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form id="kehadiranForm" action="{{ route('user.kehadiran.konfirmasi') }}" method="POST"
                        class="space-y-2">
                        @csrf
                        <input type="hidden" name="status" id="statusInput">

                        <div class="flex gap-2 justify-center">
                            <button type="button"
                                onclick="document.getElementById('statusInput').value='masuk'; this.form.submit();"
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition font-semibold">
                                ✅ Konfirmasi Masuk
                            </button>

                            <button type="button"
                                onclick="document.getElementById('statusInput').value='libur'; this.form.submit();"
                                class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition font-semibold">
                                ❌ Konfirmasi Libur
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Batas konfirmasi: hingga jam 12:00</p>
                    </form>
                @elseif (!$buttonKonfirmasiAktif && !$isLewatJam12 && !$sudahKonfirmasiHariIni)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-blue-700 font-semibold">⏳ Menunggu Giliran</p>
                        <p class="text-sm text-blue-600">Tombol konfirmasi akan aktif ketika giliran Anda tiba.</p>
                    </div>
                @else
                    <p class="text-gray-600">
                        ✅ Kehadiran sudah dikonfirmasi hari ini
                        <span
                            class="font-semibold {{ $kehadiranHariIni->status == 'masuk' ? 'text-green-600' : 'text-red-600' }}">
                            ({{ ucfirst($kehadiranHariIni->status) }})
                        </span>
                        @if ($isLewatJam12 && $kehadiranHariIni->status == 'libur')
                            <br><span class="text-sm text-yellow-600">(Auto-generated: Batas waktu absensi telah
                                lewat)</span>
                        @endif
                    </p>
                @endif
            </div>

            {{-- history kehadiran --}}
            <div class="bg-white rounded-lg shadow p-4 mb-8">
                <span class="font-semibold text-lg">History Kehadiran</span>
                <table class="table-auto w-full border-collapse mt-2">
                    <thead>
                        <tr class="border-b border-gray-400">
                            <th class="text-center font-semibold text-gray-400">Hari/Tanggal</th>
                            <th class="text-center font-semibold text-gray-400">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($historyKehadiran as $item)
                            <tr>
                                <td class="text-center py-2">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->format('l, d M Y') }}
                                </td>
                                <td class="text-center py-2">
                                    @if ($item->status == 'masuk')
                                        <span class="text-green-600 font-semibold">Masuk</span>
                                    @elseif ($item->status == 'libur')
                                        <span class="text-red-600 font-semibold">Libur</span>
                                    @else
                                        <span class="text-yellow-600 font-semibold">Menunggu Konfirmasi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-gray-500 py-2">
                                    Belum ada history kehadiran
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- sidebar --}}
            <div class="fixed inset-0 bg-grey bg-opacity-50 z-40" x-show="openSidebar" x-transition.opacity
                @click="openSidebar = false">
            </div>

            {{-- aside --}}
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
                    <a href="/user/history" class="block text-gray-700 hover:text-[#b59356]">History</a>
                </nav>
            </aside>
        </div>

        <!-- Bottom Navigation (mobile) -->
        <nav class="bg-white border-t p-2 flex justify-around fixed bottom-0 w-full">
            <a href="/user/dashboard" class="flex flex-col items-center text-gray-500">
                <x-heroicon-o-home class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
                </x-heroicon-o-home>
                <span class="text-xs">Beranda</span>
            </a>
            <a href="/user/profile" class="flex flex-col items-center text-gray-500">
                <x-heroicon-o-user class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
                </x-heroicon-o-user>
                <span class="text-xs">Users</span>
            </a>
            <button @click="openSidebar = true" class="flex flex-col items-center text-gray-500">
                <x-heroicon-s-bars-3 class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
                </x-heroicon-s-bars-3>
                <span class="text-xs">Menu</span>
            </button>
        </nav>
    </div>

    @section('scripts')
        <script>
            // Helper: ambil CSRF token dengan fallback
            function getCsrfToken() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (!meta) {
                    console.error(
                        'CSRF token not found: tambahkan <meta name="csrf-token" content="{{ csrf_token() }}"> di layout');
                    return '';
                }
                return meta.getAttribute('content');
            }

            // Pastikan CSRF token ada di head
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const meta = document.createElement('meta');
                meta.name = 'csrf-token';
                meta.content = '{{ csrf_token() }}';
                document.head.appendChild(meta);
            }

            // Fungsi update warna button dengan error handling
            function updateButtonColors() {
                fetchWithTimeout('/api/kehadiran/status', {
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        }
                    }, 10000)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data && Array.isArray(data)) {
                            data.forEach(item => {
                                const buttons = document.querySelectorAll(
                                    `button[data-rombong-id="${item.rombong_id}"]`);
                                buttons.forEach(button => {
                                    if (!button) return;
                                    const baseClasses =
                                        'text-white text-sm font-medium rounded-lg px-3 py-2 shadow transition min-h-[40px] w-full text-center';
                                    let warnaButton = 'bg-gray-300 cursor-not-allowed';
                                    let statusText = item.statusText ?? 'MENUNGGU';
                                    let isDisabled = true;

                                    if (item.status === 'masuk') {
                                        warnaButton = 'bg-green-500 hover:bg-green-600';
                                        statusText = 'MASUK';
                                    } else if (item.status === 'libur') {
                                        warnaButton = 'bg-red-400 cursor-not-allowed';
                                        statusText = 'LIBUR';
                                    } else if (item.isActive && !item.isPast12) {
                                        warnaButton = 'bg-yellow-500 hover:bg-yellow-600 animate-pulse';
                                        statusText = 'KONFIRMASI SEKARANG';
                                        isDisabled = false;
                                    } else {
                                        warnaButton = 'bg-gray-300 cursor-not-allowed';
                                        statusText = item.isPast12 ? 'BATAS WAKTU HABIS' :
                                            'MENUNGGU GILIRAN';
                                    }

                                    button.className =
                                        `${warnaButton} ${baseClasses} ${!isDisabled ? 'hover:scale-105' : ''}`;
                                    button.disabled = !!isDisabled;

                                    const statusElement = button.querySelector('.status-text');
                                    if (statusElement) statusElement.textContent = statusText;

                                    button.title = `${item.nama_jualan ?? ''} - ${statusText}`;

                                    if (!isDisabled) button.classList.add('animate-pulse');
                                    else button.classList.remove('animate-pulse');
                                });
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error updating button colors:', error);
                        setTimeout(updateButtonColors, 5000);
                    });
            }

            // Timeout handling untuk fetch requests
            function fetchWithTimeout(url, options = {}, timeout = 10000) {
                return Promise.race([
                    fetch(url, options),
                    new Promise((_, reject) =>
                        setTimeout(() => reject(new Error('Request timeout')), timeout)
                    )
                ]);
            }

            // Auto-update setiap 30 detik
            setInterval(updateButtonColors, 30000);

            document.addEventListener('DOMContentLoaded', function() {
                updateButtonColors();

                // ---------- Konfirmasi Kehadiran ----------
                const formKehadiran = document.getElementById('kehadiranForm');
                if (formKehadiran) {
                    let inFlightKehadiran = false;

                    formKehadiran.addEventListener('submit', function(e) {
                        e.preventDefault();
                        if (inFlightKehadiran) return;
                        inFlightKehadiran = true;

                        const formData = new FormData(formKehadiran);
                        const submitButton = e.submitter || formKehadiran.querySelector(
                            'button[type="submit"]');

                        // Tambahkan CSRF token secara manual ke formData
                        formData.append('_token', getCsrfToken());

                        fetchWithTimeout(formKehadiran.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            }, 10000)
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().catch(() => {
                                        throw new Error('Network error');
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data && data.success) {
                                    showNotification('success', 'Kehadiran berhasil dikonfirmasi: ' + data
                                        .status);
                                    updateButtonColors();
                                    // Refresh halaman setelah 2 detik untuk update status
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 2000);
                                } else {
                                    showNotification('error', data.message || 'Gagal konfirmasi kehadiran');
                                }
                            })
                            .catch(error => {
                                console.error('Error konfirmasi kehadiran:', error);
                                showNotification('error', 'Terjadi kesalahan saat konfirmasi: ' + error
                                    .message);
                            })
                            .finally(() => {
                                inFlightKehadiran = false;
                            });
                    });
                }

                // ---------- Pengajuan Rombong ----------
                const formRombong = document.getElementById('rombongForm');
                if (formRombong) {
                    let inFlightRombong = false;

                    formRombong.addEventListener('submit', function(e) {
                        e.preventDefault();
                        if (inFlightRombong) return;
                        inFlightRombong = true;

                        const formData = new FormData(formRombong);
                        const msgBox = document.getElementById('responseMessage');

                        // Tambahkan CSRF token secara manual ke formData
                        formData.append('_token', getCsrfToken());

                        fetchWithTimeout(formRombong.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrfToken(),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            }, 10000)
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().catch(() => {
                                        throw new Error('Network error');
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data && data.success) {
                                    msgBox.innerHTML =
                                        `<span class="text-green-600">${data.message}</span>`;
                                    formRombong.reset();
                                    showNotification('success', data.message);

                                    setTimeout(() => {
                                        const formWrap = document.getElementById('formTambah');
                                        if (formWrap) formWrap.classList.add('hidden');
                                        msgBox.innerHTML = '';
                                    }, 2000);
                                } else {
                                    msgBox.innerHTML =
                                        `<span class="text-red-600">${data.message || 'Terjadi kesalahan'}</span>`;
                                    showNotification('error', data.message || 'Terjadi kesalahan');
                                }
                            })
                            .catch(error => {
                                console.error('Error pengajuan rombong:', error);
                                msgBox.innerHTML =
                                    `<span class="text-red-600">Gagal mengirim data: ${error.message}</span>`;
                                showNotification('error', 'Gagal mengirim data: ' + error.message);
                            })
                            .finally(() => {
                                inFlightRombong = false;
                            });
                    });
                }
            });

            // Fungsi untuk menampilkan notifikasi
            function showNotification(type, message) {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                    type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
                }`;
                notification.innerHTML =
                    `<div class="flex items-center"><span class="mr-2">${type === 'success' ? '✓' : '✗'}</span><span>${message}</span></div>`;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 3000);
            }
        </script>

        <!-- Alpine.js -->
        <script src="//unpkg.com/alpinejs" defer></script>
    @endsection
@endsection
