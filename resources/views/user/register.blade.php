@extends('master.masterUser')
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
                <div class="w-full" x-data="{ step: 1 }">
                    <form action="{{ route('user.register.store') }}" method="POST"
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

                        <!-- STEP 1 -->
                        <div x-show="step === 1" x-transition>
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium mb-1">Nama Lengkap <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                    minlength="2" maxlength="255"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="block text-sm font-medium mb-1">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="no_telp" class="block text-sm font-medium mb-1">No. WhatsApp <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp', '62') }}"
                                    required placeholder="6281234567890" pattern="^62[0-9]{9,13}$"
                                    title="Format: 62 diikuti 9-13 digit angka"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                @error('no_telp') border-red-500 @enderror">

                                <p class="mt-1 text-sm text-gray-500">
                                    Format: 62 diikuti nomor tanpa angka 0. Contoh: 6281234567890
                                </p>
                                @error('no_telp')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="alamat" class="block text-sm font-medium mb-1">Alamat Rumah <span
                                        class="text-red-500">*</span></label>
                                <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" required
                                    minlength="5" maxlength="255" placeholder="Jl. Contoh No. 123, Kelurahan, Kecamatan"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                @error('alamat') border-red-500 @enderror">
                                @error('alamat')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
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

                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">Minimal 6 karakter</p>
                                    <div id="password-feedback" class="text-sm mt-1" style="display: none;">
                                        <span id="password-length" class="text-red-500">✗ Minimal 6 karakter</span>
                                    </div>
                                </div>

                                @error('password')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="block text-sm font-medium mb-1">
                                    Konfirmasi Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        required minlength="6"
                                        class="w-full px-4 py-2 pr-12 rounded-lg border border-gray-300 
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                    @error('password_confirmation') border-red-500 @enderror">

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

                            <div class="pt-2">
                                <button type="button" @click="validateStep1() && (step = 2)"
                                    class="w-full bg-[#b59356] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#a08347] transition duration-200">
                                    Lanjut ke Pertanyaan
                                </button>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div x-show="step === 2" x-transition>
                            <!-- Pertanyaan 1 - Checkbox -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-3">
                                    Untuk meningkatkan penghasilanmu, Kamu ingin belajar ilmu apa? <span
                                        class="text-red-500">*</span>
                                </label>

                                <div class="space-y-2" id="pertanyaan1-group">
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

                                    <input type="text" placeholder="Lainnya (opsional)" name="pertanyaan1_custom"
                                        id="lainnya" value="{{ old('pertanyaan1_custom') }}" maxlength="50"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                                </div>
                                @error('pertanyaan1')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pertanyaan 2 - Radio -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-3">
                                    Jika ada iuran harian untuk kas. Berapa rupiah yang kamu ikhlaskan untuk dikumpulkan
                                    kepada petugas pengurus? <span class="text-red-500">*</span>
                                </label>

                                <div class="space-y-2" id="pertanyaan2-group">
                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="0" name="pertanyaan2" value="0"
                                            class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                            {{ old('pertanyaan2') == '0' ? 'checked' : '' }}>
                                        <label for="0">Rp 0</label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="1000" name="pertanyaan2" value="1000"
                                            class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                            {{ old('pertanyaan2') == '1000' ? 'checked' : '' }}>
                                        <label for="1000">Rp 1.000</label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="2000" name="pertanyaan2" value="2000"
                                            class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                            {{ old('pertanyaan2') == '2000' ? 'checked' : '' }}>
                                        <label for="2000">Rp 2.000</label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="3000" name="pertanyaan2" value="3000"
                                            class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                            {{ old('pertanyaan2') == '3000' ? 'checked' : '' }}>
                                        <label for="3000">Rp 3.000</label>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="5000" name="pertanyaan2" value="5000"
                                            class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
                                            {{ old('pertanyaan2') == '5000' ? 'checked' : '' }}>
                                        <label for="5000">Rp 5.000</label>
                                    </div>

                                    <!-- Help text untuk radio buttons -->
                                    <p class="text-xs text-gray-500 mt-2 mb-3">
                                        💡 <strong>Tips:</strong> Klik pilihan di atas untuk memilih, klik lagi untuk
                                        membatalkan pilihan
                                    </p>

                                    <input type="text" placeholder="Contoh: 6000, 15000, 25000 (tanpa titik/koma)"
                                        name="pertanyaan2_custom" value="{{ old('pertanyaan2_custom') }}" maxlength="50"
                                        pattern="[0-9]*" title="Hanya angka yang diperbolehkan. Contoh: 6000"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                    focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                    transition-all duration-200 ease-in-out">
                                </div>
                                @error('pertanyaan2')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pertanyaan 3 - Date -->
                            <div class="mb-6">
                                <label for="mulai_jual" class="block text-sm font-medium mb-1">
                                    Sudah berjualan di PSB sejak kapan? <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="mulai_jual" name="mulai_jual" value="{{ old('mulai_jual') }}"
                                    required max="{{ date('Y-m-d') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent
                                @error('mulai_jual') border-red-500 @enderror">
                                @error('mulai_jual')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Pertanyaan 4 - Radio -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium mb-3">
                                    Penjaga lapak? <span class="text-red-500">*</span>
                                </label>

                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <input type="radio" id="sendiri" name="penjaga_stand" value="Saya Sendiri"
                                            required class="rounded border-gray-300 text-[#b59356] focus:ring-[#b59356]"
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
                                @error('penjaga_stand')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tombol -->
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="step = 1"
                                    class="w-1/2 bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-400 transition duration-200">
                                    Kembali
                                </button>
                                <button type="submit" onclick="return validateStep2()"
                                    class="w-1/2 bg-[#b59356] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#a08347] transition duration-200">
                                    Daftar Sekarang
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

    <script>
        // Validasi real-time untuk nomor telepon
        document.addEventListener('DOMContentLoaded', function() {
            const noTelpInput = document.getElementById('no_telp');
            const passwordInput = document.getElementById('password');
            const passwordConfirmInput = document.getElementById('password_confirmation');

            // Format dan validasi nomor telepon
            noTelpInput.addEventListener('input', function(e) {
                let value = e.target.value;

                // Hapus semua karakter non-digit
                value = value.replace(/\D/g, '');

                // Pastikan dimulai dengan 62
                if (value.length > 0 && !value.startsWith('62')) {
                    if (value.startsWith('0')) {
                        value = '62' + value.substring(1);
                    } else if (value.startsWith('8')) {
                        value = '62' + value;
                    } else {
                        value = '62' + value;
                    }
                }

                // Batasi panjang maksimal
                if (value.length > 15) {
                    value = value.substring(0, 15);
                }

                e.target.value = value;

                // Validasi format
                const isValid = /^628[0-9]{8,12}$/.test(value);
                if (value.length >= 3 && !isValid) {
                    e.target.setCustomValidity('Format nomor WhatsApp tidak valid. Contoh: 6281234567890');
                } else {
                    e.target.setCustomValidity('');
                }
            });

            // Validasi konfirmasi password sederhana
            function validatePasswordMatch() {
                if (passwordConfirmInput.value !== passwordInput.value) {
                    passwordConfirmInput.setCustomValidity('Password tidak cocok');
                    return false;
                } else {
                    passwordConfirmInput.setCustomValidity('');
                    return true;
                }
            }

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

        // Validasi Step 1
        function validateStep1() {
            const requiredFields = ['name', 'email', 'no_telp', 'alamat', 'password', 'password_confirmation'];
            let isValid = true;
            let errorMessage = '';

            for (let fieldId of requiredFields) {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    isValid = false;
                    errorMessage = 'Semua field wajib diisi';
                    field.focus();
                    break;
                }
            }

            // Validasi khusus nomor telepon
            const noTelp = document.getElementById('no_telp').value;
            if (!/^628[0-9]{8,12}$/.test(noTelp)) {
                isValid = false;
                errorMessage = 'Format nomor WhatsApp tidak valid. Gunakan format: 6281234567890';
                document.getElementById('no_telp').focus();
            }

            // Validasi password match
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirmation').value;
            if (password !== passwordConfirm) {
                isValid = false;
                errorMessage = 'Konfirmasi password tidak cocok';
                document.getElementById('password_confirmation').focus();
            }

            if (!isValid) {
                alert(errorMessage);
            }

            return isValid;
        }

        // Validasi Step 2
        function validateStep2() {
            let isValid = true;
            let errorMessage = '';

            // Validasi pertanyaan 1
            const pertanyaan1Checkboxes = document.querySelectorAll('input[name="pertanyaan1[]"]:checked');
            const pertanyaan1Custom = document.querySelector('input[name="pertanyaan1_custom"]').value.trim();

            if (pertanyaan1Checkboxes.length === 0 && !pertanyaan1Custom) {
                isValid = false;
                errorMessage =
                    'Pilih minimal satu opsi atau isi kolom lainnya untuk pertanyaan tentang ilmu yang ingin dipelajari';
            }

            // Validasi pertanyaan 2
            const pertanyaan2Radio = document.querySelector('input[name="pertanyaan2"]:checked');
            const pertanyaan2Custom = document.querySelector('input[name="pertanyaan2_custom"]').value.trim();

            if (!pertanyaan2Radio && !pertanyaan2Custom) {
                isValid = false;
                errorMessage = 'Pilih salah satu opsi atau isi kolom lainnya untuk pertanyaan tentang iuran harian';
            }

            // Validasi tanggal mulai jual
            const mulaiJual = document.getElementById('mulai_jual').value;
            if (!mulaiJual) {
                isValid = false;
                errorMessage = 'Tanggal mulai berjualan wajib diisi';
            } else {
                const today = new Date();
                const selectedDate = new Date(mulaiJual);
                if (selectedDate > today) {
                    isValid = false;
                    errorMessage = 'Tanggal mulai berjualan tidak boleh di masa depan';
                }
            }

            // Validasi penjaga stand
            const penjagaStand = document.querySelector('input[name="penjaga_stand"]:checked');
            if (!penjagaStand) {
                isValid = false;
                errorMessage = 'Pilihan penjaga lapak wajib dipilih';
            }

            if (!isValid) {
                alert(errorMessage);
                return false;
            }

            // Konfirmasi sebelum submit
            return confirm('Apakah data yang Anda masukkan sudah benar? Data akan dikirim untuk persetujuan admin.');
        }

        // Perbaikan: Radio button yang bisa di-uncheck dan disable/enable field custom
        document.addEventListener('DOMContentLoaded', function() {
            const pertanyaan2Radios = document.querySelectorAll('input[name="pertanyaan2"]');
            const pertanyaan2Custom = document.querySelector('input[name="pertanyaan2_custom"]');
            let lastChecked = null;

            // Fungsi untuk update state field custom
            function updateCustomFieldState() {
                const anyRadioChecked = Array.from(pertanyaan2Radios).some(radio => radio.checked);

                if (anyRadioChecked) {
                    // Ada radio yang dipilih - disable dan kosongkan custom field
                    pertanyaan2Custom.disabled = true;
                    pertanyaan2Custom.value = '';
                    pertanyaan2Custom.classList.add('bg-gray-100', 'cursor-not-allowed');
                    pertanyaan2Custom.placeholder = 'Pilih radio button di atas atau kosongkan dulu pilihan radio';
                } else {
                    // Tidak ada radio yang dipilih - enable custom field
                    pertanyaan2Custom.disabled = false;
                    pertanyaan2Custom.classList.remove('bg-gray-100', 'cursor-not-allowed');
                    pertanyaan2Custom.placeholder = 'Contoh: 6000, 15000, 25000 (tanpa titik/koma)';
                }
            }

            // Event handler untuk setiap radio button - bisa uncheck
            pertanyaan2Radios.forEach(radio => {
                radio.addEventListener('click', function() {
                    // Jika radio yang sama diklik lagi, uncheck
                    if (lastChecked === this) {
                        this.checked = false;
                        lastChecked = null;
                    } else {
                        lastChecked = this;
                    }
                    updateCustomFieldState();
                });
            });

            // Ketika custom input diisi, uncheck semua radio buttons
            pertanyaan2Custom.addEventListener('input', function() {
                if (this.value.trim() !== '') {
                    pertanyaan2Radios.forEach(radio => {
                        radio.checked = false;
                        lastChecked = null;
                    });
                }
                // Validasi real-time untuk custom input (hanya angka)
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // Inisialisasi state awal
            updateCustomFieldState();

            // Handle form reset
            document.querySelector('form').addEventListener('reset', function() {
                setTimeout(() => {
                    lastChecked = null;
                    updateCustomFieldState();
                }, 0);
            });
        });
    </script>

@endsection
