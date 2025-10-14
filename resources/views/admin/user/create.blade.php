@extends('master.masterAdmin')
@section('title', 'Tambah User')
@section('content')

    <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-2xl p-8 mt-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Tambah User Baru</h2>

        @if ($errors->any())
            <div class="p-3 mb-4 bg-red-100 text-red-700 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div>
                <label class="block mb-1 font-medium">Nama</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Alamat</label>
                <textarea name="alamat" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>{{ old('alamat') }}</textarea>
            </div>

            <div>
                <label class="block mb-1 font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">No. Telepon</label>
                <div class="relative">
                    <input type="text" name="no_telp" value="{{ old('no_telp') }}" pattern="^62[0-9]{9,13}$"
                        placeholder="62812xxxxxxxx" title="Format: 62 diikuti 9-13 digit angka"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 @error('no_telp') border-red-500 @enderror"
                        required>
                    @error('no_telp')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">Contoh: 6281234567890 (gunakan kode negara 62)</p>
                </div>
            </div>

            <div>
                <label class="block mb-1 font-medium">Nama Jualan</label>
                <input type="text" name="nama_jualan" value="{{ old('nama_jualan') }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Foto Rombong</label>
                <input type="file" name="foto_rombong" class="w-full border rounded-lg px-3 py-2">
            </div>

            <div>
                <label class="block mb-1 font-medium">Password</label>
                <div class="relative">
                    <input type="password" name="password" minlength="6"
                        class="w-full border rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-yellow-500 @error('password') border-red-500 @enderror"
                        required>
                    <button type="button" onclick="togglePassword('password')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                        <svg id="eye-password" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        <svg id="eye-slash-password" class="h-5 w-5 hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m6.121-6.121A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.05 10.05 0 01-1.563 3.029m-5.858-.908L12 12m-6.121-6.121L12 12">
                            </path>
                        </svg>
                    </button>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">Minimal 6 karakter</p>
                </div>
            </div>

            <div>
                <label class="block mb-1 font-medium">Konfirmasi Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" minlength="6"
                        class="w-full border rounded-lg px-3 py-2 pr-10 focus:ring-2 focus:ring-yellow-500 @error('password_confirmation') border-red-500 @enderror"
                        required>
                    <button type="button" onclick="togglePassword('password_confirmation')"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                        <svg id="eye-password_confirmation" class="h-5 w-5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        <svg id="eye-slash-password_confirmation" class="h-5 w-5 hidden" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m6.121-6.121A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.05 10.05 0 01-1.563 3.029m-5.858-.908L12 12m-6.121-6.121L12 12">
                            </path>
                        </svg>
                    </button>
                    @error('password_confirmation')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block mb-1 font-medium">Status</label>
                <select name="status"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500 @error('status') border-red-500 @enderror"
                    required>
                    <option value="">-- Pilih Status --</option>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approve" {{ old('status') == 'approve' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Status user untuk sistem approval</p>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.user.index') }}"
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Batal</a>
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-yellow-600 text-white hover:bg-yellow-700">Simpan</button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(fieldName) {
            const passwordField = document.querySelector(`input[name="${fieldName}"]`);
            const eyeIcon = document.getElementById(`eye-${fieldName}`);
            const eyeSlashIcon = document.getElementById(`eye-slash-${fieldName}`);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }

        // Real-time validation for phone number
        document.querySelector('input[name="no_telp"]').addEventListener('input', function(e) {
            const value = e.target.value;
            const isValid = /^62[0-9]{9,13}$/.test(value);

            if (value && !isValid) {
                e.target.classList.add('border-red-500');
                e.target.classList.remove('border-green-500');
            } else if (value && isValid) {
                e.target.classList.add('border-green-500');
                e.target.classList.remove('border-red-500');
            } else {
                e.target.classList.remove('border-red-500', 'border-green-500');
            }
        });

        // Real-time validation for password
        document.querySelector('input[name="password"]').addEventListener('input', function(e) {
            const value = e.target.value;
            const isValid = value.length >= 6;

            if (value && !isValid) {
                e.target.classList.add('border-red-500');
                e.target.classList.remove('border-green-500');
            } else if (value && isValid) {
                e.target.classList.add('border-green-500');
                e.target.classList.remove('border-red-500');
            } else {
                e.target.classList.remove('border-red-500', 'border-green-500');
            }
        });

        // Password confirmation validation
        document.querySelector('input[name="password_confirmation"]').addEventListener('input', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = e.target.value;

            if (confirmPassword && password !== confirmPassword) {
                e.target.classList.add('border-red-500');
                e.target.classList.remove('border-green-500');
            } else if (confirmPassword && password === confirmPassword) {
                e.target.classList.add('border-green-500');
                e.target.classList.remove('border-red-500');
            } else {
                e.target.classList.remove('border-red-500', 'border-green-500');
            }
        });
    </script>
@endsection
