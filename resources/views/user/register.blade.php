@extends('master.masterUser')
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
                <form action="{{route('user.register.store')}}" method="POST" class="space-y-5 bg-white rounded-xl shadow-lg p-6">
                    @csrf
                    
                    <!-- Error Messages -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <!-- STEP 1 -->
                    <div x-show="step === 1" x-transition>
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium mb-1">Nama</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div class="mb-4">
                            <label for="no_telp" class="block text-sm font-medium mb-1">No. WA</label>
                            <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div class="mb-4">
                            <label for="alamat" class="block text-sm font-medium mb-1">Alamat Rumah</label>
                            <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium mb-1">Password</label>
                            <input type="password" id="password" name="password" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
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
                        <!-- Pertanyaan 1 - Checkbox -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-3">Untuk meningkatkan penghasilanmu, Kamu ingin belajar ilmu apa?</label>
                            
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="pemasaran" name="pertanyaan1[]" value="pemasaran"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ in_array('Pemasaran', old('pertanyaan1', [])) ? 'checked' : '' }}>
                                    <label for="pemasaran">Pemasaran</label>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" id="produksi" name="pertanyaan1[]" value="produksi"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ in_array('Produksi', old('pertanyaan1', [])) ? 'checked' : '' }}>
                                    <label for="produksi">Produksi</label>
                                </div>
                                
                                <input type="text" placeholder="Lainnya" name="pertanyaan1_custom" id="lainnya"
                                    value="{{ old('pertanyaan1_custom') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                            </div>
                        </div>

                        <!-- Pertanyaan 2 - Radio -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-3">Jika ada iuran harian untuk kas. Berapa rupiah yang kamu ikhlaskan untuk dikumpulkan kepada petugas pengurus?</label>
                            
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <input type="radio" id="0" name="pertanyaan2" value="0"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ old('pertanyaan2') == '0' ? 'checked' : '' }}>
                                    <label for="0">RP.0</label>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <input type="radio" id="1000" name="pertanyaan2" value="1000"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ old('pertanyaan2') == '1000' ? 'checked' : '' }}>
                                    <label for="1000">RP.1000</label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <input type="radio" id="2000" name="pertanyaan2" value="2000"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ old('pertanyaan2') == '2000' ? 'checked' : '' }}>
                                    <label for="2000">RP.2000</label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <input type="radio" id="3000" name="pertanyaan2" value="3000"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ old('pertanyaan2') == '3000' ? 'checked' : '' }}>
                                    <label for="3000">RP.3000</label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <input type="radio" id="5000" name="pertanyaan2" value="5000"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ old('pertanyaan2') == '5000' ? 'checked' : '' }}>
                                    <label for="5000">RP.5000</label>
                                </div>
                                
                                <input type="text" placeholder="Lainnya" name="pertanyaan2_custom"
                                    value="{{ old('pertanyaan2_custom') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                            </div>
                        </div>

                        <!-- Pertanyaan 3 - Date -->
                        <div class="mb-6">
                            <label for="mulai_jual" class="block text-sm font-medium mb-1">Sudah berjualan di PSB sejak kapan?</label>
                            <input type="date" id="mulai_jual" name="mulai_jual" 
                                value="{{ old('mulai_jual') }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <!-- Pertanyaan 4 - Radio -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium mb-3">Penjaga lapak?</label>
                            
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <input type="radio" id="sendiri" name="penjaga_stand" value="Saya Sendiri" required
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ old('penjaga_stand') == 'Saya Sendiri' ? 'checked' : '' }}>
                                    <label for="sendiri">Saya Sendiri</label>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <input type="radio" id="karyawan" name="penjaga_stand" value="Karyawan"
                                        class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                        {{ old('penjaga_stand') == 'Karyawan' ? 'checked' : '' }}>
                                    <label for="karyawan">Karyawan</label>
                                </div>
                            </div>
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