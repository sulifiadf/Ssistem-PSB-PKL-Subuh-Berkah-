@extends('layout.masterAdmin')
@section('title', 'dashboard')
@section('content')

<div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Navbar -->
    <header class="bg-[#b59356] text-white p-4 flex justify-between items-center shadow-md">
        <img src="{{asset('img/logo2.png')}}" alt="logo" class="w-12 h-12 object-contain">
        <h1 class="text-lg font-bold">Dashboard Admin</h1>
        <x-heroicon-o-arrow-right-start-on-rectangle class="h-8 w-8 mb-1" fill="none" viewBox="0 0 24 24" stroke="#ffff"/>
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
        <div class="grid grid-cols-2 sm:grid-cols-2 lg-grid-cols-2 gap-4 mb-6">
            {{-- anggota --}}
            <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-blue-600">24</span>
                <x-heroicon-o-users class="h-6 w-6" stroke="#b59356"/>
                <span class="text-sm font-semibold text-gray-500">Anggota Tetap</span>
            </div>
            <div class="bg-white rounded-lg shadow p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-blue-600">20</span>
                <x-heroicon-o-users class="h-6 w-6" stroke="#b59356"/>
                <span class="text-sm font-semibold text-gray-500">Anggota Sementara</span>
            </div>
        </div>

        {{-- lapak --}}
            <div class="bg-white rounded-lg shadow p-4">
                    <div x-data="{ 
                        showAll: false, 
                        lapaks: ['Lapak 1', 'Lapak 2', 'Lapak 3', 'Lapak 4', 'Lapak 5', 'Lapak 6', 'Lapak 7'] 
                        }" 
                        class="space-y-2">
        
                <!-- Container tombol -->
                    <div class="flex gap-4 overflow-x-auto pb-2">
                <!-- Tampilkan 4 dulu, sisanya saat 'showAll = true' -->
                    <template x-for="(lapak, index) in (showAll ? lapaks : lapaks.slice(0, 4))" :key="index">
                        <div class="flex flex-col items-center min-w-[100px]">
                            <button 
                                class="bg-[#CFB47D] text-white text-sm font-medium rounded-lg px-4 py-2 shadow hover:bg-[#b89e65] transition">
                                <span x-text="lapak"></span>
                            </button>
                            {{-- button + --}}
                            <button class="mt-2 bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-green-600 shadow">+</button>
                        </div>
            </template>
        </div>
    </div>
    </div>
</div>

<!-- Alpine.js -->
<script src="//unpkg.com/alpinejs" defer></script>


        <!-- Section tambahan -->
        <div class="mt-6 bg-white rounded-lg shadow p-4">
            <h2 class="font-semibold mb-2">Waiting List</h2>
            <ul class="text-sm text-gray-600 space-y-2">
                {{-- daftar wiating list --}}
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
                <li class="flex justify-between items-center">
                    <span>User Budi Registered</span>
                    <div class="flex gap-2">
                        <button class="text-green-500 hover:text-green-700">
                            <x-heroicon-o-check-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-red-500 hover:text-red-700">
                            <x-heroicon-o-x-circle class="h-6 w-6"/>
                        </button>
                        <button class="text-blue-500 hover:text-blue-700">
                            <x-heroicon-o-eye class="h-6 w-6"/>
                        </button>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Bottom Navigation (mobile) -->
    <nav class="bg-white border-t p-2 flex justify-around fixed bottom-0 w-full">
        <a href="#" class="flex flex-col items-center text-gray-500">
            <x-heroicon-o-home class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
            </x-heroicon-o-home>
            <span class="text-xs">Home</span>
        </a>
        <a href="#" class="flex flex-col items-center text-gray-500">
            <x-heroicon-o-user class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
            </x-heroicon-o-user>
            <span class="text-xs">Users</span>
        </a>
        <a href="#" class="flex flex-col items-center text-gray-500">
            <x-heroicon-s-bars-3 class="h-6 w-6 mb-1" fill="#b59356" viewBox="0 0 24 24" stroke="#b59356">
            </x-heroicon-s-bars-3>
            <span class="text-xs">Menu</span>
        </a>
    </nav>
</div>
@endsection
