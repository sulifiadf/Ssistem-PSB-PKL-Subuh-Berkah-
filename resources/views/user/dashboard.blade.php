@extends('master.masterUser')
@section('title', 'dashboard')
@section('content')

<div class="min-h-screen bg-gray-100 flex flex-col" 
        x-data="{ openSidebar: false}">
    <!-- Navbar -->
    <header class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] text-white p-4 flex justify-between items-center shadow-md">
        <img src="{{asset('img/logo2.png')}}" alt="logo" class="w-12 h-12 object-contain">
        <h1 class="text-lg font-bold">Beranda User</h1>
        <a href="user/login">
            <x-heroicon-o-arrow-right-start-on-rectangle class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="#ffff"/>
        </a>
    </header>

    <!-- Konten Utama -->
    <div class="cols-1 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-1 gap-4 mb-4">
            <!-- Card uang kas -->
            <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-blue-600">120</span>
                <x-heroicon-o-currency-dollar class="h-6 w-6" stroke="#b59356"/>
                <span class="text-sm font-semibold text-gray-500">Jumlah Uang Kas</span>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg-grid-cols-2 gap-4 mb-4">
            {{-- anggota --}}
            <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-blue-600">{{$totalTetap}}</span>
                <x-heroicon-o-users class="h-6 w-6" stroke="#b59356"/>
                <span class="text-sm font-semibold text-gray-500">Anggota Tetap</span>
            </div>
            <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-blue-600">{{$totalSementara}}</span>
                <x-heroicon-o-users class="h-6 w-6" stroke="#b59356"/>
                <span class="text-sm font-semibold text-gray-500">Anggota Sementara</span>
            </div>
        </div>

        {{-- lapak --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h3 class="font-semibold text-lg mb-2">Lapak Anggota</h3>

            {{-- check apakah user sudah memiliki rombong/menjadi anggota? --}}
            @if ($buttonAnggota)
                <button 
                    class="bg-blue-400 text-white text-sm rounded-lg px-2 py-2 mb-3"
                    onclick="document.getElementById('formTambah').classList.toggle('hidden')">
                    + anggota
                </button>
            @endif

            <div class="flex gap-4 overflow-x-auto pb-2">
                @foreach ($lapaks as $lapak)
                    <div class="flex flex-col min-w-[150px] border rounded-lg shadow p-3">
                        <h4 class="text-sm font-semibold text-center mb-2">{{ $lapak->nama_lapak }}</h4>

                        @if ($lapak->rombongs->count() > 0)
                            @foreach ($lapak->rombongs as $rombong)
                                @if ($rombong->user && $rombong->user->status == 'approve')
                                    @php
                                        $today = \Carbon\Carbon::today()->toDateString();
                                        $kehadiran = \App\Models\Kehadiran::where('user_id', $rombong->user->user_id)
                                            ->whereDate('tanggal', $today)
                                            ->first();

                                        if ($kehadiran && $kehadiran->pesan_wa_terkirim) {
                                            if ($kehadiran->status == 'masuk') {
                                                $warnaButton = 'bg-green-500 hover:bg-green-600';
                                                $statusText = 'Masuk';
                                            } elseif ($kehadiran->status == 'libur') {
                                                $warnaButton = 'bg-red-500 hover:bg-red-600';
                                                $statusText = 'Libur';
                                            } else {
                                                $warnaButton = 'bg-[#CFB47D] hover:bg-[#b89e65]';
                                                $statusText = 'Menunggu Konfirmasi';
                                            }
                                        } else {
                                            $warnaButton = 'bg-[#CFB47D] hover:bg-[#b89e65]';
                                            $statusText = 'Standby';
                                        }
                                    @endphp

                                    <div class="flex flex-col items-center min-w-[120px] mb-2">
                                        <button class="{{ $warnaButton }} text-white text-sm font-medium rounded-lg px-3 py-2 shadow transition min-h-[40px] w-full text-center"
                                                title="{{ $rombong->user->name }} - {{ $statusText }}"
                                                data-user-id="{{ $rombong->user->id }}"
                                                data-lapak-id="{{ $lapak->lapak_id }}">
                                            <span class="block truncate">{{ $rombong->nama_jualan }}</span>
                                            <small class="block text-xs opacity-75 truncate">{{ $rombong->user->name }}</small>
                                            <small class="block text-xs font-bold">{{ $statusText }}</small>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <p class="text-gray-500 text-sm text-center">Belum ada anggota</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>



        {{-- form tambah anggota --}}
        <div id="formTambah" class="hidden bg-white rounded-lg shadow-lg p-4 mb-4">
            <form id="rombongForm" action="{{route('user.rombong.store')}}" method="POST" class="space-y-4">
                @csrf
                <div class="mb-2">
                    <label class="block text-sm font-medium">Pilih Lapak</label>
                    <select name="lapak_id" id="lapak_id" class="form-control py-2 px-2 border rounded-lg">
                        <option value="">-- Pilih Lapak --</option>
                        @foreach ($lapaks as $lapak)
                            <option value="{{ $lapak->lapak_id }}">{{ $lapak->nama_lapak }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium">Nama Usaha</label>
                    
                    @if(Auth::user()->rombong && Auth::user()->rombong->nama_jualan)
                        {{-- Sudah ada nama usaha di profil --}}
                        <input 
                            type="text" 
                            name="nama_jualan" 
                            id="nama_jualan" 
                            class="w-full border rounded-lg px-3 py-2" 
                            value="{{ Auth::user()->rombong->nama_jualan }}">
                    @else
                        {{-- Belum melengkapi profil --}}
                        <input 
                            type="text" 
                            name="nama_jualan" 
                            id="nama_jualan" 
                            class="w-full border rounded-lg px-3 py-2 bg-gray-100 cursor-not-allowed" 
                            value="" 
                            placeholder="Lengkapi profile terlebih dahulu" 
                            readonly>
                        <p class="text-sm text-red-500 mt-1">⚠ Silakan lengkapi profil Anda terlebih dahulu.</p>
                    @endif
                </div>

                <button type="submit" class="bg-blue-400 text-white px-2 py-2 rounded-lg">Kirim</button>
            </form>
            <div id="responseMessage" class="mt-2 text-sm"></div>
        </div>

        {{-- history kehadiran --}}
        <div class="bg-white rounded-lg shadow p-4 mb-4">
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
                                Belum ada history
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        {{-- sidebar --}}
    <div class="fixed inset-0 bg-grey bg-opacity-50 z-40"
        x-show="openSidebar"
        x-transition.opacity
        @click="openSidebar = false">
    </div>

    {{-- aside --}}
    <aside 
        class="fixed top-0 left-0 w-64 h-full bg-white shadow-lg z-50 transform -translate-x-full transition-transform"
        :class="{'translate-x-0': openSidebar}">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="font-bold text-lg">Menu</h2>
            <button @click="openSidebar = false" class="text-gray-600 hover:text-black">&times;</button>
        </div>
        <nav class="p-4 space-y-3">
            <a href="/user/dashboard" class="block text-gray-700 hover:text-[#b59356]">Beranda</a>
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
    setInterval(updateButtonColors, 30000); // update setiap 30 detik

    document.addEventListener('DOMContentLoaded', function() {
        updateButtonColors(); 
    });

    //fungsi update warna button
    function updateButtonColors(){
        fetch('api/kehadiran/status')
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const button = document.querySelector('button[data-user-id="${item.user_id}"]');
                    if(button){
                        button.className = '${item.warnaButton} text-white text-sm font-medium rounded-lg px-3 py-2 shadow hover:bg-[#b89e65] transition min-h-[40px] w-full text-center';

                        const statusMap = {
                            'masuk': 'Masuk',
                            'libur': 'Libur',
                            null: 'Menunggu Konfirmasi',
                        };

                        const statusText = statusMap[item.status] || 'Stanby';
                        const smallElement = button.querySelector('small:last-child');
                        if(smallElement){
                            smallElement.textContent = statusText;
                        }

                        //update title
                        button.title = '${item.nama_jualan} - ${statusText}';

                        if(item.status === null && item.pesan_wa_terkirim){
                            button.classList.add('animate-pulse');
                        } else {
                            button.classList.remove('animate-pulse');
                        }
                    }
                });
            })
            .catch(err => console.error('Error updating button colors:', error));
    }

    document.getElementById('rombongForm').addEventListener('submit', function(e) {
        e.preventDefault(); // cegah reload halaman

        let form = e.target;
        let formData = new FormData(form);

        fetch(form.action, {
            method: form.method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            let msgBox = document.getElementById('responseMessage');
            if (data.success) {
                msgBox.innerHTML = `<span class="text-green-600">${data.message}</span>`;
                form.reset(); // reset input
            } else {
                msgBox.innerHTML = `<span class="text-red-600">${data.message ?? 'Terjadi kesalahan'}</span>`;
            }
        })
        .catch(err => {
            document.getElementById('responseMessage').innerHTML = 
                `<span class="text-red-600">Gagal mengirim data</span>`;
        });
    });
</script>



<!-- Alpine.js -->
<script src="//unpkg.com/alpinejs" defer></script>
@endsection



