@extends('master.masterUser')
@section('title', 'dashboard')
@section('content')

    <div class="min-h-screen bg-gray-100 flex flex-col pb-16" x-data="{ openSidebar: false }"&gt; <!-- Navbar -->
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
        <div class="flex-1 overflow-y-auto p-4">
            {{-- Ringkasan Keuangan --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-3 gap-4 mb-4">
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-lg font-semibold text-green-800">Total Pemasukan</h3>
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-lg font-semibold text-red-800">Total Pengeluaran</h3>
                    <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <h3 class="text-lg font-semibold text-blue-800">Saldo</h3>
                    <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</p>
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
                    <span class="text-sm font-semibold text-gray-500 text-center">Anggota Sementara</span>
                </div>
            </div>

            {{-- lapak + model --}}
            <div x-data="{ open: false, foto: '', nama: '', pemilik: '', status: '', totalMasuk: 0, totalLibur: 0 }">

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

                            {{-- Status Badge --}}
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full mb-3"
                                :class="{
                                    'bg-green-100 text-green-800': status === 'MASUK',
                                    'bg-red-100 text-red-800': status === 'LIBUR',
                                    'bg-yellow-100 text-yellow-800': status === 'KONFIRMASI SEKARANG',
                                    'bg-gray-100 text-gray-800': status === 'MENUNGGU GILIRAN'
                                }"
                                x-text="status">
                            </span>

                            {{-- Count Statistics --}}
                            <div class="flex justify-center gap-4 mt-3 pt-3 border-t border-gray-200">
                                <div class="text-center">
                                    <div class="text-green-600 font-bold text-lg" x-text="totalMasuk"></div>
                                    <div class="text-xs text-gray-500">Total Masuk</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-red-600 font-bold text-lg" x-text="totalLibur"></div>
                                    <div class="text-xs text-gray-500">Total Libur</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Lapak --}}
                <div class="bg-white rounded-lg shadow p-4 mb-4">
                    <h3 class="font-semibold text-lg mb-2">Lapak Anggota</h3>

                    <div class="flex gap-2 overflow-x-auto pb-2">
                        @foreach ($lapaks as $lapak)
                            <div class="flex flex-col min-w-[110px] max-w-[120px] border rounded-lg shadow p-2"
                                data-lapak-id="{{ $lapak->lapak_id }}" ondragover="event.preventDefault();"
                                ondrop="dropRombong(event, {{ $lapak->lapak_id }})">

                                <h4 class="text-xs font-semibold text-center mb-1 bg-gray-100 p-1 rounded">
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

                                                // Hitung total masuk dan libur sepanjang waktu
                                                $totalMasuk = \App\Models\kehadiran::where('user_id', $item->user_id)
                                                    ->where('status', 'masuk')
                                                    ->count();

                                                $totalLibur = \App\Models\kehadiran::where('user_id', $item->user_id)
                                                    ->where('status', 'libur')
                                                    ->count();

                                                $urutanText = '#' . ($index + 1);

                                                // Default status
                                                $warnaButton = 'bg-gray-300';
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
                                                        $warnaButton = 'bg-red-400';
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
                                                                $prevKehadiran = \App\Models\kehadiran::where(
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
                                                            $warnaButton = 'bg-gray-300';
                                                            $statusText = 'MENUNGGU GILIRAN';
                                                            $badgeColor = 'bg-gray-100 text-gray-800';
                                                            $isDisabled = true;
                                                        }
                                                    } else {
                                                        $warnaButton = 'bg-gray-300';
                                                        $statusText = 'SUDAH ADA YANG MASUK';
                                                        $badgeColor = 'bg-gray-100 text-gray-800';
                                                        $isDisabled = true;
                                                    }
                                                }

                                                $fotoRombong = $item->foto_rombong
                                                    ? asset('storage/' . $item->foto_rombong)
                                                    : asset('img/no-image.png');
                                            @endphp

                                            <div class="flex flex-col items-center min-w-[90px] mb-2" draggable="true"
                                                ondragstart="dragRombong(event, {{ $item->rombong_id }}, {{ $lapak->lapak_id }})"
                                                data-user-id="{{ $item->user_id }}">

                                                {{-- Badge urutan --}}
                                                <span
                                                    class="text-xs {{ $badgeColor }} px-1 py-0.5 rounded-full mb-1 font-semibold">
                                                    {{ $urutanText }}
                                                </span>

                                                {{-- Tombol Rombong - Modal selalu bisa dibuka untuk melihat detail --}}
                                                <button
                                                    class="{{ $warnaButton }} text-white text-xs font-medium rounded-lg px-2 py-1.5 shadow transition min-h-[45px] w-full text-center hover:scale-105 cursor-pointer"
                                                    title="{{ $item->user_name }} - {{ $statusText }}"
                                                    @click="
                                                        open = true; 
                                                        foto = '{{ $fotoRombong }}'; 
                                                        nama = '{{ strtoupper($item->nama_jualan) }}';
                                                        pemilik = '{{ $item->user->name }}';
                                                        status = '{{ $statusText }}';
                                                        totalMasuk = {{ $totalMasuk }};
                                                        totalLibur = {{ $totalLibur }}
                                                    "
                                                    data-rombong-id="{{ $item->rombong_id }}">
                                                    <span
                                                        class="block truncate text-xs">{{ strtoupper($item->nama_jualan) }}</span>
                                                    <small
                                                        class="block text-xs opacity-75 truncate">{{ $item->user_name }}</small>

                                                    {{-- Count Masuk & Libur --}}
                                                    <div class="flex justify-center gap-1 mt-1 mb-1">
                                                        <span
                                                            class="text-xs bg-green-600 bg-opacity-90 px-1 py-0.5 rounded text-white font-bold shadow-sm">
                                                            ✓ {{ $totalMasuk }}
                                                        </span>
                                                        <span
                                                            class="text-xs bg-red-600 bg-opacity-90 px-1 py-0.5 rounded text-white font-bold shadow-sm">
                                                            ✗ {{ $totalLibur }}
                                                        </span>
                                                    </div>

                                                    <small
                                                        class="block text-xs font-bold status-text">{{ $statusText }}</small>
                                                </button>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-gray-500 text-xs text-center">Belum ada anggota</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- form tambah anggota --}}
            @if ($buttonAnggotaAktif)
                @if (!Auth::user()->rombong || !Auth::user()->rombong->nama_jualan)
                    {{-- User belum lengkapi profil - tampilkan pesan dan link profil --}}
                    <div class="bg-blue-50 rounded-lg shadow-lg p-4 mb-4 border border-blue-200">
                        <h3 class="font-semibold text-lg mb-3 text-blue-800">📝 Lengkapi Profil Dulu</h3>
                        <div class="bg-blue-100 p-3 rounded-lg mb-3">
                            <p class="text-blue-700 text-sm">
                                <strong>Untuk mengajukan ke lapak manapun, Anda harus lengkapi profil terlebih
                                    dahulu:</strong>
                            </p>
                            <ul class="list-disc list-inside text-blue-600 text-xs mt-2 space-y-1">
                                <li>Buat rombong dengan nama usaha</li>
                                <li>Upload foto rombong (opsional)</li>
                                <li>Setelah itu bisa mengajukan ke lapak manapun</li>
                            </ul>
                        </div>
                        <a href="{{ route('user.profile.edit') }}"
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg w-full hover:bg-blue-600 transition inline-block text-center">
                            📝 Lengkapi Profil Sekarang
                        </a>
                    </div>
                @else
                    {{-- User sudah punya rombong - tampilkan form pengajuan normal --}}
                    <div id="formTambah" class="bg-white rounded-lg shadow-lg p-4 mb-4">
                        <h3 class="font-semibold text-lg mb-3">Ajukan Anggota Baru</h3>

                        {{-- Info kondisi pengajuan --}}
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm text-blue-700">
                                    <p class="font-medium mb-1">📢 Sistem Rebutan Lapak Kosong:</p>
                                    <ul class="space-y-1 text-xs">
                                        <li>• <strong>SEMUA USER</strong> bisa mengajukan ketika ada lapak yang semua
                                            anggotanya
                                            libur</li>
                                        <li>• Sistem rebutan: yang pertama disetujui admin akan jadi <strong>anggota
                                                sementara</strong></li>
                                        <li>• Manfaatkan kesempatan ketika lapak kosong untuk mendapat giliran jualan</li>
                                        <li>• Cek status kehadiran anggota lapak sebelum mengajukan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <form id="rombongForm" action="{{ route('user.rombong.store') }}" method="POST" <div
                            id="lapakErrorMsg" class="text-red-600 text-sm mb-2" style="display:none;">
                    </div>
                    class="space-y-4">
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
                                class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed" value=""
                                placeholder="Lengkapi profile terlebih dahulu" readonly required>
                            <p class="text-sm text-red-500 mt-1">⚠ Silakan lengkapi profil Anda terlebih dahulu.
                            </p>
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
        @endif

        {{-- tombol konfirmasi kehadiran --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4 text-center">
            <h3 class="font-semibold text-lg mb-2">Konfirmasi Kehadiran Hari Ini</h3>

            @if ($showBatasWaktu)
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-3">
                    <p class="font-semibold">⏰ Batas Waktu Absensi</p>
                    <p class="text-sm">{{ $statusKonfirmasi['pesan'] }}</p>
                </div>
            @endif

            @if (!$sudahKonfirmasiHariIni && $buttonKonfirmasiAktif)
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                    <p class="text-green-700 font-semibold">✅ Giliran Anda Sekarang!</p>
                    <p class="text-sm text-green-600">{{ $statusKonfirmasi['pesan'] }}</p>
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
                </form>
            @elseif (!$buttonKonfirmasiAktif && !$sudahKonfirmasiHariIni)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-blue-700 font-semibold">⏳
                        {{ $statusKonfirmasi['status'] === 'menunggu_giliran' ? 'Menunggu Giliran' : 'Status Kehadiran' }}
                    </p>
                    <p class="text-sm text-blue-600">{{ $statusKonfirmasi['pesan'] }}</p>
                </div>
            @else
                <p class="text-gray-600">
                    ✅ Kehadiran sudah dikonfirmasi hari ini
                    @if ($kehadiranHariIni)
                        <span
                            class="font-semibold {{ $kehadiranHariIni->status == 'masuk' ? 'text-green-600' : 'text-red-600' }}">
                            ({{ ucfirst($kehadiranHariIni->status) }})
                        </span>
                        @if ($isLewatJam12 && $kehadiranHariIni && $kehadiranHariIni->status == 'libur')
                            <br><span class="text-sm text-yellow-600">(Auto-generated: Batas waktu absensi telah
                                lewat)</span>
                        @endif
                    @else
                        <span class="font-semibold text-gray-600">(Status tidak tersedia)</span>
                    @endif
                </p>
            @endif
        </div>

        {{-- history kehadiran --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="font-semibold text-lg">Rekap Kehadiran (7 Hari Terakhir)</span>
                <a href="{{ route('user.history') }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                    Lihat Semua →
                </a>
            </div>
            <div class="overflow-x-auto mt-2">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="border-b border-gray-400">
                            <th class="text-center font-semibold text-gray-400 px-2 py-2">Hari/Tanggal</th>
                            <th class="text-center font-semibold text-gray-400 px-2 py-2">Waktu Konfirmasi</th>
                            <th class="text-center font-semibold text-gray-400 px-2 py-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($historyKehadiran as $item)
                            <tr>
                                <td class="text-center py-2 px-2 text-sm">
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
                                    {{ $hari }}, {{ $tanggal->format('d M Y') }}
                                </td>
                                <td class="text-center py-2 px-2 text-sm">
                                    @if ($item->waktu_konfirmasi)
                                        {{ \Carbon\Carbon::parse($item->waktu_konfirmasi)->format('H:i') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="text-center py-2 px-2">
                                    @if ($item->status == 'masuk')
                                        <span class="text-green-600 font-semibold text-sm">Masuk</span>
                                    @elseif ($item->status == 'libur')
                                        <span class="text-red-600 font-semibold text-sm">Libur</span>
                                    @else
                                        <span class="text-yellow-600 font-semibold text-sm">Menunggu Konfirmasi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-500 py-4">
                                    Belum ada history kehadiran
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- rekapan keuangan (read-only untuk user) --}}
        <div class="bg-white rounded-lg shadow p-4 mb-20">
            <div class="flex justify-between items-center mb-4">
                <span class="font-semibold text-lg">Rekapan Keuangan</span>
            </div>

            {{-- Tabel Keuangan --}}
            <div class="overflow-x-auto mt-2">
                <table class="table-auto w-full border-collapse">
                    <thead>
                        <tr class="border-b border-gray-400">
                            <th class="text-center font-semibold text-gray-400 px-2 py-2">Tanggal</th>
                            <th class="text-center font-semibold text-gray-400 px-2 py-2">Jenis</th>
                            <th class="text-center font-semibold text-gray-400 px-2 py-2">Jumlah</th>
                            <th class="text-center font-semibold text-gray-400 px-2 py-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($keuangans as $item)
                            <tr>
                                <td class="text-center py-2 px-2 text-sm">
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
                                    {{ $hari }}, {{ $tanggal->format('d M Y') }}
                                </td>
                                <td class="text-center py-2 px-2">
                                    @if ($item->jenis == 'pemasukan')
                                        <span
                                            class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            Pemasukan
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            Pengeluaran
                                        </span>
                                    @endif
                                </td>
                                <td
                                    class="text-center py-2 px-2 text-sm font-semibold {{ $item->jenis == 'pemasukan' ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="text-center py-2 px-2 text-sm">
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-gray-500 py-4">
                                    Belum ada data keuangan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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
                <a href="{{ route('user.profile.edit') }}" class="block text-gray-700 hover:text-[#b59356]">Profile</a>
                <a href="{{ route('user.history') }}" class="block text-gray-700 hover:text-[#b59356]">Rekap
                    Kehadiran</a>
                <a href="{{ route('user.keuangan') }}" class="block text-gray-700 hover:text-[#b59356]">Rekap
                    Keuangan</a>
            </nav>
        </aside>
    </div>

    <!-- Bottom Navigation (mobile) -->
    <nav class="bg-white border-t p-2 flex justify-around fixed bottom-0 w-full">
        <a href="{{ route('user.dashboard') }}" class="flex flex-col items-center text-gray-500">
            <x-heroicon-o-home class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
            </x-heroicon-o-home>
            <span class="text-xs">Beranda</span>
        </a>
        <a href="{{ route('user.profile.edit') }}" class="flex flex-col items-center text-gray-500">
            <x-heroicon-o-user class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
            </x-heroicon-o-user>
            <span class="text-xs">Profil</span>
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
            // Validasi AJAX sebelum submit form pengajuan rombong (sistem rebutan)
            document.addEventListener('DOMContentLoaded', function() {
                const lapakSelect = document.getElementById('lapak_id');
                const formRombong = document.getElementById('rombongForm');
                const lapakErrorMsg = document.getElementById('lapakErrorMsg');
                if (lapakSelect && formRombong) {
                    lapakSelect.addEventListener('change', function() {
                        const lapakId = this.value;
                        if (!lapakId) {
                            lapakErrorMsg.style.display = 'none';
                            return;
                        }
                        fetch(`/user/validate-pengajuan/${lapakId}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.success === false && data.pesan && data.pesan.includes(
                                        'sudah ada anggota yang masuk')) {
                                    lapakErrorMsg.textContent = data.pesan;
                                    lapakErrorMsg.style.display = 'block';
                                } else {
                                    lapakErrorMsg.style.display = 'none';
                                }
                            })
                            .catch(() => {
                                lapakErrorMsg.textContent = 'Gagal cek status lapak.';
                                lapakErrorMsg.style.display = 'block';
                            });
                    });
                    formRombong.addEventListener('submit', function(e) {
                        if (lapakErrorMsg.style.display === 'block') {
                            e.preventDefault();
                            showNotification('error', lapakErrorMsg.textContent);
                        }
                    });
                }
            });
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
                                        'text-white text-xs font-medium rounded-lg px-2 py-1.5 shadow transition min-h-[45px] w-full text-center';
                                    let warnaButton = 'bg-gray-300';
                                    let statusText = item.statusText ?? 'MENUNGGU';
                                    let isDisabled = true;

                                    if (item.status === 'masuk') {
                                        warnaButton = 'bg-green-500 hover:bg-green-600';
                                        statusText = 'MASUK';
                                    } else if (item.status === 'libur') {
                                        warnaButton = 'bg-red-400';
                                        statusText = 'LIBUR';
                                    } else if (item.isActive && !item.isPast12) {
                                        warnaButton = 'bg-yellow-500 hover:bg-yellow-600 animate-pulse';
                                        statusText = 'KONFIRMASI SEKARANG';
                                        isDisabled = false;
                                    } else {
                                        warnaButton = 'bg-gray-300';
                                        statusText = item.isPast12 ? 'BATAS WAKTU HABIS' :
                                            'MENUNGGU GILIRAN';
                                    }

                                    // SOLUSI FINAL: Gunakan CSS custom properties tanpa mengubah DOM
                                    // Ini tidak akan mengganggu Alpine.js sama sekali

                                    let bgColor, textColor;
                                    if (item.status === 'masuk') {
                                        bgColor = 'rgb(34, 197, 94)'; // bg-green-500
                                        textColor = 'white';
                                    } else if (item.status === 'libur') {
                                        bgColor = 'rgb(248, 113, 113)'; // bg-red-400  
                                        textColor = 'white';
                                    } else if (item.isActive && !item.isPast12) {
                                        bgColor = 'rgb(234, 179, 8)'; // bg-yellow-500
                                        textColor = 'white';
                                    } else {
                                        bgColor = 'rgb(209, 213, 219)'; // bg-gray-300
                                        textColor = 'rgb(107, 114, 128)'; // text-gray-500
                                    }

                                    // Set CSS custom properties tanpa mengubah class atau struktur DOM
                                    button.style.setProperty('--button-bg', bgColor);
                                    button.style.setProperty('--button-text', textColor);
                                    button.style.backgroundColor = bgColor;
                                    button.style.color = textColor;

                                    // Modal selalu bisa dibuka - tidak disable button
                                    // button.disabled = !!isDisabled; // REMOVED - modal harus selalu bisa dibuka

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
