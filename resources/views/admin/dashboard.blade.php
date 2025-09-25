@extends('master.masterAdmin')
@section('title', 'dashboard')
@section('content')

    <div class="min-h-screen bg-gray-100 flex flex-col" x-data="{ openSidebar: false }">
        <!-- Navbar -->
        <header
            class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] text-white p-4 flex justify-between items-center shadow-md">
            <img src="{{ asset('img/logo2.png') }}" alt="logo" class="w-12 h-12 object-contain">
            <h1 class="text-lg font-bold">Beranda Admin</h1>
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
                <div onclick="window.location.href ='{{ route('admin.keuangan.index') }}' "
                    class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($jumlahUangKas) }}</span>
                    <x-heroicon-o-currency-dollar class="h-6 w-6" stroke="#b59356" />
                    <span class="text-sm font-semibold text-gray-500">Jumlah Uang Kas</span>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-2 gap-4 mb-4">
                {{-- anggota --}}
                <div onclick="window.location.href='{{ route('admin.user.index') }}'"
                    class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                    <span class="text-2xl font-bold text-blue-600">{{ $totalTetap }}</span>
                    <x-heroicon-o-users class="h-6 w-6" stroke="#b59356" />
                    <span class="text-sm font-semibold text-gray-500">Anggota Tetap</span>
                </div>
                <div onclick="window.location.href='{{ route('admin.user.index') }}'"
                    class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                    <span class="text-2xl font-bold text-blue-600">{{ $totalSementara }}</span>
                    <x-heroicon-o-users class="h-6 w-6" stroke="#b59356" />
                    <span class="text-sm font-semibold text-gray-500">Anggota Sementara</span>
                </div>
            </div>

            {{--  Lapak + Modal --}}
            <div x-data="{ open: false, foto: '', nama: '', pemilik: '', status: '' }">

                {{-- Detail Rombong (Modal) --}}
                <div x-show="open" x-transition class="fixed inset-0 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative">
                        <button @click="open=false"
                            class="absolute top-2 right-2 text-gray-500 hover:text-red-600 text-xl font-bold">✕</button>

                        <div class="text-center">
                            <img :src="foto" alt="Foto Rombong"
                                class="w-32 h-32 object-cover mx-auto rounded-lg mb-4 border-2 border-gray-200">
                            <h4 class="font-bold text-lg text-gray-700 mb-2" x-text="nama"></h4>
                            <p class="text-sm text-gray-600 mb-2" x-text="'Pemilik: ' + pemilik"></p>
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full"
                                :class="{
                                    'bg-green-100 text-green-800': status === 'MASUK',
                                    'bg-red-100 text-red-800': status === 'LIBUR',
                                    'bg-yellow-100 text-yellow-800': status === 'MENUNGGU KONFIRMASI',
                                    'bg-gray-100 text-gray-800': status === 'STANDBY'
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
                                        @foreach ($lapak->rombongs as $index => $item)
                                            @php
                                                $kehadiran = $kehadiranHariIni[$item->user_id] ?? null;
                                                $urutanText = '#' . ($index + 1);
                                                
                                                // Default status
                                                $warnaButton = 'bg-[#CFB47D] hover:bg-[#b89e65]';
                                                $statusText = 'STANDBY';
                                                $badgeColor = 'bg-gray-100 text-gray-800';
                                                
                                                // Cek jika ada data kehadiran
                                                if ($kehadiran) {
                                                    if ($kehadiran->pesan_wa_terkirim) {
                                                        if ($kehadiran->status == 'masuk') {
                                                            $warnaButton = 'bg-green-500 hover:bg-green-600';
                                                            $statusText = 'MASUK';
                                                            $badgeColor = 'bg-green-100 text-green-800';
                                                        } elseif ($kehadiran->status == 'libur') {
                                                            $warnaButton = 'bg-red-500 hover:bg-red-600';
                                                            $statusText = 'LIBUR';
                                                            $badgeColor = 'bg-red-100 text-red-800';
                                                        } else {
                                                            $warnaButton = 'bg-yellow-500 hover:bg-yellow-600 animate-pulse';
                                                            $statusText = 'MENUNGGU KONFIRMASI';
                                                            $badgeColor = 'bg-yellow-100 text-yellow-800';
                                                        }
                                                    } else {
                                                        if ($kehadiran->status == 'masuk') {
                                                            $warnaButton = 'bg-green-500 hover:bg-green-600';
                                                            $statusText = 'MASUK (WA Belum)';
                                                            $badgeColor = 'bg-green-100 text-green-800';
                                                        } elseif ($kehadiran->status == 'libur') {
                                                            $warnaButton = 'bg-red-500 hover:bg-red-600 ';
                                                            $statusText = 'LIBUR (WA Belum)';
                                                            $badgeColor = 'bg-red-100 text-red-800';
                                                        }
                                                    }
                                                }
                                                
                                                $fotoRombong = $item->foto_rombong
                                                    ? asset('storage/' . $item->foto_rombong)
                                                    : asset('img/no-image.png');
                                            @endphp

                                            <div class="flex flex-col items-center min-w-[120px] mb-3" draggable="true"
                                                ondragstart="dragRombong(event, {{ $item->rombong_id }}, {{ $lapak->lapak_id }})"
                                                data-user-id="{{ $item->user_id }}">

                                                <span class="text-xs {{ $badgeColor }} px-2 py-1 rounded-full mb-1 font-semibold">
                                                    {{ $urutanText }}
                                                </span>

                                                <button
                                                    class="{{ $warnaButton }} text-white text-sm font-medium rounded-lg px-3 py-2 shadow transition min-h-[40px] w-full text-center hover:scale-105"
                                                    title="{{ $item->user_name }} - {{ $statusText }}"
                                                    @click="
                                                        open = true; 
                                                        foto = '{{ $fotoRombong }}'; 
                                                        nama = '{{ $item->nama_jualan }}';
                                                        pemilik = '{{ $item->name }}';
                                                        status = '{{ $statusText }}'
                                                    "
                                                    data-user-id="{{ $item->user_id }}">
                                                    <span class="block truncate">{{ $item->nama_jualan }}</span>
                                                    <small class="block text-xs opacity-75 truncate">{{ $item->user_name }}</small>
                                                    <small class="block text-xs font-bold">{{ $statusText }}</small>
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


            {{-- Waiting List User --}}
            <div onclick="window.location.href='{{ route('admin.waitinglist') }}'"
                class="mt-6 bg-white rounded-lg shadow p-4 mb-6">
                <h2 class="font-semibold mb-2">Waiting List User</h2>
                <ul class="text-sm text-gray-600 space-y-2">
                    @forelse($users as $user)
                        <li class="flex justify-between items-center py-2">
                            <span>{{ $user->name }} ({{ $user->email }})</span>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.approve', $user->user_id) }}">
                                    @csrf
                                    <button class="text-green-500 hover:text-green-700">
                                        <x-heroicon-o-check-circle class="h-6 w-6" />
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.reject', $user->user_id) }}">
                                    @csrf
                                    <button class="text-red-500 hover:text-red-700">
                                        <x-heroicon-o-x-circle class="h-6 w-6" />
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="text-gray-500">Tidak ada user pending.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Waiting List Anggota --}}
            <div onclick="window.location.href='{{ route('admin.waitinglist') }}'"
                class="mt-6 bg-white rounded-lg shadow p-4 mb-6">
                <h2 class="font-semibold mb-2">Waiting List Anggota</h2>
                <ul class="text-sm text-gray-600 space-y-2">
                    @forelse($pendingAnggota as $waiting)
                        <li class="flex justify-between items-center py-2">
                            <span>{{ $waiting->user->name }} ({{ $waiting->user->email }}) - Lapak:
                                {{ $waiting->lapak->nama_lapak }}</span>
                            <div class="flex gap-2">
                                <form method="POST"
                                    action="{{ route('admin.anggota.approve', $waiting->waiting_list_id) }}">
                                    @csrf
                                    <button class="text-green-500 hover:text-green-700">
                                        <x-heroicon-o-check-circle class="h-6 w-6" />
                                    </button>
                                </form>
                                <form method="POST"
                                    action="{{ route('admin.anggota.reject', $waiting->waiting_list_id) }}">
                                    @csrf
                                    <button class="text-red-500 hover:text-red-700">
                                        <x-heroicon-o-x-circle class="h-6 w-6" />
                                    </button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="text-gray-500">Tidak ada anggota pending.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Sidebar --}}
            <div class="fixed inset-0 bg-grey bg-opacity-50 z-40" x-show="openSidebar" x-transition.opacity
                @click="openSidebar = false">
            </div>

            {{-- Aside --}}
            <aside
                class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-50 transform -translate-x-full transition-transform"
                :class="{ 'translate-x-0': openSidebar }">
                <div class="p-4 border-b flex justify-between items-center">
                    <h2 class="font-bold text-lg">Menu</h2>
                    <button @click="openSidebar = false" class="text-gray-600 hover:text-black">&times;</button>
                </div>
                <nav class="p-4 space-y-3">
                    <a href="/admin/dashboard" class="block text-gray-700 hover:text-[#b59356]">Beranda</a>
                    <a href="/admin/user" class="block text-gray-700 hover:text-[#b59356]">User</a>
                    <a href="/admin/lapak" class="block text-gray-700 hover:text-[#b59356]">Lapak</a>
                    <a href="/admin/waitinglist" class="block text-gray-700 hover:text-[#b59356]">Waiting List</a>
                    <a href="/admin/keuangan" class="block text-gray-700 hover:text-[#b59356]">Manajemen Keuangan</a>
                </nav>
            </aside>
        </div>

        <!-- Bottom Navigation (mobile) -->
        <nav class="bg-white border-t p-2 flex justify-around fixed bottom-0 w-full">
            <a href="/admin/dashboard" class="flex flex-col items-center text-gray-500">
                <x-heroicon-o-home class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356" />
                <span class="text-xs">Beranda</span>
            </a>
            <a href="/admin/user" class="flex flex-col items-center text-gray-500">
                <x-heroicon-o-user class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356" />
                <span class="text-xs">Users</span>
            </a>
            <button @click="openSidebar = true" class="flex flex-col items-center text-gray-500">
                <x-heroicon-s-bars-3 class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356" />
                <span class="text-xs">Menu</span>
            </button>
        </nav>

        {{-- Modal Detail Kehadiran --}}
        <div id="modalDetailKehadiran" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 mb-8">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Detail Kehadiran</h3>
                            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div id="detailContent"><!-- isi load via js --></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script untuk admin functions --}}
    <script>
        let draggedEl = null;
        let asalLapak = null;
        let draggedRombongId = null;
        let oldParent = null;

        function dragRombong(event, rombongId, lapakId) {
            draggedEl = event.currentTarget; // Simpan elemen yang di-drag
            asalLapak = lapakId; // Simpan lapak asal
            draggedRombongId = rombongId; // Simpan ID rombong
            oldParent = draggedEl.parentElement; // Simpan parent lama untuk rollback
            event.dataTransfer.setData('rombongId', rombongId);
        }

        function dropRombong(event, lapakTujuan) {
            event.preventDefault();

            let rombongId = event.dataTransfer.getData('rombongId');

            if (draggedEl && asalLapak !== lapakTujuan) {
                //  Update tampilan langsung (optimistic UI)
                const container = event.currentTarget.querySelector('.rombong-container');
                if (container) {
                    container.appendChild(draggedEl);
                }

                // Kirim request ke server
                fetch("{{ route('admin.perpindahan.store') }}", { // pastikan route name benar
                        method: 'POST',
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify({
                            rombong_id: rombongId,
                            lapak_asal_id: asalLapak,
                            lapak_tujuan_id: lapakTujuan,
                        }),
                    })
                    .then(res => {
                        if (!res.ok) throw new Error("Gagal menyimpan ke server");
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('Perpindahan berhasil:', data.message);
                        } else {
                            throw new Error(data.message || "Gagal memindahkan rombong");
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        //rollback tampilan kalau gagal
                        if (oldParent) {
                            oldParent.appendChild(draggedEl);
                        }
                        alert('Perpindahan gagal: ' + err.message);
                    });
            }
        }

        function updateButtonColors() {
            fetch('{{ route("admin.kehadiran.status") }}') // Ganti dengan route web yang sesuai
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {
                        const button = document.querySelector(`button[data-user-id="${item.user_id}"]`);
                        if (button) {
                            // Update warna button
                            button.className = 
                                `${item.warnaButton} text-white text-sm font-medium rounded-lg px-3 py-2 shadow transition min-h-[40px] w-full text-center hover:scale-105`;

                            // Update status text
                            const smallElement = button.querySelector('small:last-child');
                            if (smallElement) {
                                smallElement.textContent = item.statusText;
                            }

                            // Update title
                            button.title = `${item.user_name} - ${item.statusText}`;

                            // Add/remove pulse animation
                            if (item.statusText === 'MENUNGGU KONFIRMASI') {
                                button.classList.add('animate-pulse');
                            } else {
                                button.classList.remove('animate-pulse');
                            }
                        }
                    });
                })
                .catch(error => console.error('Error updating button colors:', error));
        }

        function showDetailKehadiran(userId, userName) {
            fetch(`/api/kehadiran/detail/${userId}`)
                .then(response => response.json())
                .then(data => {
                    const content = `
                <div class="space-y-3">
                    <div class="text-center">
                        <h4 class="font-semibold text-lg">${userName}</h4>
                        <span class="px-3 py-1 rounded-full text-sm ${getStatusBadgeClass(data.status)}">
                            ${getStatusText(data.status)}
                        </span>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal:</span>
                            <span>${data.tanggal}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">WA Terkirim:</span>
                            <span>${data.pesan_wa_terkirim ? '✅ Ya' : '❌ Tidak'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Waktu Konfirmasi:</span>
                            <span>${data.waktu_konfirmasi || '-'}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Keterangan:</span>
                            <span>${data.keterangan || '-'}</span>
                        </div>
                    </div>
                    
                    <div class="pt-3 border-t">
                        <button onclick="kirimWAManual(${userId}, '${userName}')" 
                                class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg text-sm">
                            📱 Kirim WA Manual
                        </button>
                    </div>
                </div>
            `;

                    document.getElementById('detailContent').innerHTML = content;
                    document.getElementById('modalDetailKehadiran').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error loading detail:', error);
                    alert('Gagal memuat detail kehadiran');
                });
        }

        function closeDetailModal() {
            document.getElementById('modalDetailKehadiran').classList.add('hidden');
        }

        function getStatusBadgeClass(status) {
            switch (status) {
                case 'masuk':
                    return 'bg-green-100 text-green-800';
                case 'libur':
                    return 'bg-red-100 text-red-800';
                case null:
                    return 'bg-yellow-100 text-yellow-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        function getStatusText(status) {
            switch (status) {
                case 'masuk':
                    return 'MASUK';
                case 'libur':
                    return 'LIBUR';
                case null:
                    return 'MENUNGGU';
                default:
                    return 'STANDBY';
            }
        }

        function kirimReminderManual() {
            if (confirm('Kirim reminder kehadiran ke semua anggota urutan pertama?')) {
                fetch('/admin/kehadiran/kirim-reminder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        updateButtonColors();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengirim reminder');
                    });
            }
        }

        function setLiburManual() {
            if (confirm('Set libur untuk semua yang belum konfirmasi?')) {
                fetch('/admin/kehadiran/set-libur', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        updateButtonColors();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal set libur manual');
                    });
            }
        }

        function refreshStatus() {
            updateButtonColors();
        }

        function exportKehadiran() {
            window.open('/admin/kehadiran/export', '_blank');
        }

        function kirimWAManual(userId, userName) {
            if (confirm(`Kirim WA reminder ke ${userName}?`)) {
                fetch('/admin/kehadiran/kirim-wa-manual', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            user_id: userId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        closeDetailModal();
                        updateButtonColors();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengirim WA');
                    });
            }
        }

        // Auto refresh setiap 15 detik untuk admin
        setInterval(updateButtonColors, 15000);

        // Update saat halaman dimuat
        document.addEventListener('DOMContentLoaded', updateButtonColors);
    </script>
@endsection
