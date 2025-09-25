@extends('master.masterAdmin')
@section('title', 'Register')

@section('content')

    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="w-full max-w-6xl mx-auto px-4">
            <!-- Grid layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 items-center gap-12">

                <!-- Logo Section -->
                <div class="flex justify-center items-center">
                    <img src="{{ asset('img/logo.png') }}" alt="logo" class="w-48 h-48 lg:w-80 lg:h-80 object-contain">
                </div>

                <!-- Form Section -->
                <div class="w-full">
                    <form action="{{ route('admin.register.store') }}" method="POST"
                        class="space-y-5 bg-white rounded-xl shadow-lg p-6">
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
                            <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp', '62') }}" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">

                            <p class="mt-1 text-sm text-gray-500">Masukkan nomor tanpa angka 0. Contoh: 6281234567890</p>
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
                            <label for="password_confirmation" class="block text-sm font-medium mb-1">Konfirmasi
                                Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="w-full bg-[#b59356] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#a08347] transition duration-200">
                                Register
                            </button>
                        </div>
                </div>
            </div>
        </div>

        <!-- Alpine.js -->
        <script src="//unpkg.com/alpinejs" defer></script>

    @endsection

    <link rel="stylesheet" href="">
