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
                            <label for="password" class="block text-sm font-medium mb-1">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required minlength="6"
                                    class="w-full px-4 py-2 pr-12 rounded-lg border border-gray-300 
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                    @error('password') border-red-500 @enderror">

                                <!-- Toggle Password Visibility -->
                                <button type="button" id="toggleAdminPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                    <svg id="eyeIconAdmin" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    <svg id="eyeSlashIconAdmin" class="h-5 w-5 hidden" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-600">Minimal 6 karakter</p>
                            @error('password')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium mb-1">
                                Konfirmasi Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    class="w-full px-4 py-2 pr-12 rounded-lg border border-gray-300 
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                    @error('password_confirmation') border-red-500 @enderror">

                                <!-- Toggle Password Confirmation Visibility -->
                                <button type="button" id="toggleAdminPasswordConfirm"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                    <svg id="eyeIconAdminConfirm" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    <svg id="eyeSlashIconAdminConfirm" class="h-5 w-5 hidden" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Toggle admin password visibility
                document.getElementById('toggleAdminPassword').addEventListener('click', function() {
                    const passwordField = document.getElementById('password');
                    const eyeIcon = document.getElementById('eyeIconAdmin');
                    const eyeSlashIcon = document.getElementById('eyeSlashIconAdmin');

                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        eyeIcon.classList.add('hidden');
                        eyeSlashIcon.classList.remove('hidden');
                    } else {
                        passwordField.type = 'password';
                        eyeIcon.classList.remove('hidden');
                        eyeSlashIcon.classList.add('hidden');
                    }
                });

                // Toggle admin password confirmation visibility
                document.getElementById('toggleAdminPasswordConfirm').addEventListener('click', function() {
                    const passwordField = document.getElementById('password_confirmation');
                    const eyeIcon = document.getElementById('eyeIconAdminConfirm');
                    const eyeSlashIcon = document.getElementById('eyeSlashIconAdminConfirm');

                    if (passwordField.type === 'password') {
                        passwordField.type = 'text';
                        eyeIcon.classList.add('hidden');
                        eyeSlashIcon.classList.remove('hidden');
                    } else {
                        passwordField.type = 'password';
                        eyeIcon.classList.remove('hidden');
                        eyeSlashIcon.classList.add('hidden');
                    }
                });
            });
        </script>

    @endsection

    <link rel="stylesheet" href="">
