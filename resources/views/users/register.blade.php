@extends('layout.masterUser')
@section('title', 'Register')

@section('content')

<div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="w-full max-w-6xl mx-auto px-4">
        <!-- Grid layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 items-center gap-12">
            
            <!-- Logo Section -->
            <div class="flex justify-center items-center">
                <img src="{{ asset('img/logo.png') }}" alt="logo" 
                    class="w-48 h-48 lg:w-80 lg:h-80 object-contain">
            </div>
            
            <!-- Form Section -->
            <div class="w-full" x-data="{ step: 1 }">
                <form action="" method="POST" class="space-y-5 bg-white rounded-xl shadow-lg p-6">
                    @csrf
                    
                    <!-- STEP 1 -->
                    <div x-show="step === 1" x-transition>
                        <div>
                            <label for="nama" class="block text-sm font-medium mb-1">Nama</label>
                            <input type="text" id="nama" name="nama" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" id="email" name="email" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div>
                            <label for="no_telp" class="block text-sm font-medium mb-1">No. WA</label>
                            <input type="no_telp" id="no_telp" name="no_telp"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div>
                            <label for="alamat" class="block text-sm font-medium mb-1">Alamat Rumah</label>
                            <input type="alamat" id="alamat" name="alamat"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium mb-1">Password</label>
                            <input type="password" id="password" name="password" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div class="pt-2">
                            <button type="button" 
                                    @click="step = 2"
                                    class="w-full bg-[#b59356] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#a08347] transition duration-200">
                                Lanjut
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2 -->
                    <div x-show="step === 2" x-transition>
                        <div class="space-y-2 mb-4">
                            <label for="pertanyaan1" class="flex items-center space-x-2">Untuk meningkatkan penghasilanmu, Kamu ingin belajar ilmu apa?</label>
                            <input type="checkbox" id="pertanyaan1" name="minat[]" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>Pemasaran</span><br>
                            <input type="checkbox" id="pertanyaan1" name="minat[]" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>Produksi</span>
                            <input type="text" id="pertanyaan1" placeholder="Lainnya" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">                            
                        </div>
                        <div class="space-y-2 mb-4">
                            <label for="pertanyaan2" class="flex items-center space-x-2">Jika ada iuran harian untuk kas. Berapa rupiah yang kamu ikhlaskan untuk dikumpulkan kepada petugas pengurus?</label>
                            <input type="checkbox" id="pertanyaan2" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>RP.0</span>
                            <input type="checkbox" id="pertanyaan1" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>RP.1000</span>
                            <input type="checkbox" id="pertanyaan1" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>RP.2000</span>
                            <input type="checkbox" id="pertanyaan1" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>RP.3000</span>
                            <input type="checkbox" id="pertanyaan1" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>RP.5000</span>
                            <input type="text" id="pertanyaan1" placeholder="Lainnya" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">                            
                        </div>

                        <div class="space-y-2 mb-4">
                            <label for="pertanyaan3" class="flex items-center space-x-2">Sudah berjualan di PSB sejak kapan?</label>
                            <input type="date" id="mulai_jual" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">                            
                        </div>

                        <div class="space-y-2 mb-4">
                            <label for="pertanyaan4" class="flex items-center space-x-2">Penjaga lapak?</label>
                            <input type="checkbox" id="pertanyaan1" name="minat[]" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>Saya Sendiri</span><br>
                            <input type="checkbox" id="pertanyaan1" name="minat[]" 
                                class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]">
                            <span>Karyawan</span>                            
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3 pt-2">
                            <button type="button" 
                                    @click="step = 1"
                                    class="w-1/2 bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-400 transition duration-200">
                                Kembali
                            </button>
                            <button type="submit" 
                                    class="w-1/2 bg-[#b59356] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#a08347] transition duration-200">
                                Register
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js -->
<script src="//unpkg.com/alpinejs" defer></script>

@endsection
