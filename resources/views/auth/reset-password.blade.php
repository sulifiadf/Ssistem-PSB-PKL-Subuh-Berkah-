@extends('master.masterUser')
@section('title', 'reset password')

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
                <div class="w-full" x-data="{ step: 1 }">
                    <form action="/auth/reset-password-direct" method="POST"
                        class="space-y-5 bg-white rounded-xl shadow-lg p-6">
                        @csrf

                        <!-- Success Message -->
                        @if (session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Error Messages -->
                        @if (session('error'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <strong>Terdapat kesalahan:</strong>
                                <ul class="mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-4 text-center">
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">Reset Password</h2>
                            <p class="text-gray-600">Masukkan email dan password baru Anda</p>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" required value="{{ old('email') }}"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                            focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                            @error('email') border-red-500 @enderror"
                                placeholder="Masukkan email anda">
                            @error('email')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium mb-1">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password" name="password" required minlength="6"
                                    class="w-full px-4 py-2 pr-12 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                @error('password') border-red-500 @enderror"
                                    placeholder="Masukkan password baru anda">

                                <!-- Toggle Password Visibility -->
                                <button type="button" id="togglePassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                    <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    <svg id="eyeSlashIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                                        </path>
                                    </svg>
                                </button>
                            </div>

                            <div id="password-feedback" class="text-sm mt-1" style="display: none;">
                                <span id="password-length" class="text-red-500">✗ Minimal 6 karakter</span>
                            </div>

                            @error('password')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium mb-1">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" id="password_confirmation" name="password_confirmation" required
                                    minlength="6"
                                    class="w-full px-4 py-2 pr-12 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                @error('password_confirmation') border-red-500 @enderror"
                                    placeholder="Masukkan kembali password baru anda">

                                <!-- Toggle Password Confirmation Visibility -->
                                <button type="button" id="togglePasswordConfirm"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                    <svg id="eyeIconConfirm" class="h-5 w-5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                    <svg id="eyeSlashIconConfirm" class="h-5 w-5 hidden" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                                        </path>
                                    </svg>
                                </button>
                            </div>

                            <div id="password-match-feedback" class="text-sm mt-1" style="display: none;">
                                <span id="password-match" class="text-red-500">✗ Password tidak cocok</span>
                            </div>

                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3 pt-4">
                            <button type="submit"
                                class="w-full bg-[#b59356] text-white font-semibold py-3 px-4 rounded-lg hover:bg-[#a08347] transition duration-200">
                                Reset Password
                            </button>
                        </div>

                        <div class="text-center pt-4">
                            <a href="{{ route('user.login') }}" class="text-[#b59356] hover:text-[#a08347] font-medium">
                                ← Kembali ke Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');

            // Toggle password visibility
            document.getElementById('togglePassword').addEventListener('click', function() {
                const passwordField = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                const eyeSlashIcon = document.getElementById('eyeSlashIcon');

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

            // Toggle password confirmation visibility
            document.getElementById('togglePasswordConfirm').addEventListener('click', function() {
                const passwordField = document.getElementById('password_confirmation');
                const eyeIcon = document.getElementById('eyeIconConfirm');
                const eyeSlashIcon = document.getElementById('eyeSlashIconConfirm');

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

            // Validasi password length sederhana
            function validatePasswordLength(password) {
                const isValid = password.length >= 6;

                const lengthCheck = document.getElementById('password-length');
                const feedback = document.getElementById('password-feedback');

                if (password.length > 0) {
                    feedback.style.display = 'block';
                    if (isValid) {
                        lengthCheck.className = 'text-green-500';
                        lengthCheck.textContent = '✓ Minimal 6 karakter';
                    } else {
                        lengthCheck.className = 'text-red-500';
                        lengthCheck.textContent = '✗ Minimal 6 karakter';
                    }
                } else {
                    feedback.style.display = 'none';
                }

                return isValid;
            }

            // Validasi password match feedback
            function updatePasswordMatchFeedback() {
                const isMatch = passwordInput.value === passwordConfirmInput.value;
                const matchCheck = document.getElementById('password-match');
                const feedback = document.getElementById('password-match-feedback');

                if (passwordConfirmInput.value.length > 0) {
                    feedback.style.display = 'block';
                    if (isMatch) {
                        matchCheck.className = 'text-green-500';
                        matchCheck.textContent = '✓ Password cocok';
                    } else {
                        matchCheck.className = 'text-red-500';
                        matchCheck.textContent = '✗ Password tidak cocok';
                    }
                } else {
                    feedback.style.display = 'none';
                }
            }

            // Validasi konfirmasi password
            function validatePasswordMatch() {
                if (passwordConfirmInput.value !== passwordInput.value) {
                    passwordConfirmInput.setCustomValidity('Password tidak cocok');
                    return false;
                } else {
                    passwordConfirmInput.setCustomValidity('');
                    return true;
                }
            }

            // Event listeners untuk password
            passwordInput.addEventListener('input', function() {
                const isValid = validatePasswordLength(this.value);

                if (this.value.length > 0 && !isValid) {
                    this.setCustomValidity('Password minimal 6 karakter');
                } else {
                    this.setCustomValidity('');
                }

                // Update password match feedback jika konfirmasi sudah diisi
                if (passwordConfirmInput.value) {
                    updatePasswordMatchFeedback();
                    validatePasswordMatch();
                }
            });

            passwordConfirmInput.addEventListener('input', function() {
                updatePasswordMatchFeedback();
                validatePasswordMatch();
            });
        });
    </script>

@endsection
