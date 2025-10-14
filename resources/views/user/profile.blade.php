@extends('master.masterUser')
@section('title', 'profile')
@section('content')

    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('user.dashboard') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Edit Profile</h2>
                <p class="text-gray-600">Lengkapi informasi profil Anda untuk pengalaman yang lebih baik</p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">

                    {{-- Error and Success Messages --}}
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.
                            <ul class="mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @csrf
                    @method('PUT')

                    <!-- Photo Upload Section -->
                    <div class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] px-6 py-4">
                        <h3 class="text-lg font-semibold text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Upload Foto
                        </h3>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Foto Rombong -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Foto Rombong</label>
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition-colors">

                                <!-- Current Image Display -->
                                @if (isset($rombong) && $rombong->foto_rombong && file_exists(storage_path('app/public/' . $rombong->foto_rombong)))
                                    <div id="current_image_rombong">
                                        <img src="{{ asset('storage/' . $rombong->foto_rombong) }}"
                                            class="w-full h-32 object-cover rounded-lg mb-3" alt="Foto Rombong">
                                        <p class="text-xs text-gray-500">Foto saat ini</p>
                                    </div>
                                @else
                                    <!-- Show upload area if no image -->
                                    <div class="text-gray-400 text-sm">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor"
                                            fill="none" viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p>Foto rombong belum diupload</p>
                                    </div>
                                @endif

                                <!-- Preview New Image -->
                                <img id="preview_rombong" class="hidden w-full h-32 object-cover rounded-lg mb-3"
                                    alt="Preview Rombong">

                                <!-- Upload Area (Hidden by default, shown when edit mode) -->
                                <div class="space-y-3 upload-area" id="upload_area_rombong" style="display: none;">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <input type="file" name="foto_rombong" id="foto_rombong" class="hidden"
                                        accept="image/*">
                                    <label for="foto_rombong" class="cursor-pointer">
                                        <span class="block text-sm text-gray-600">Klik untuk upload</span>
                                        <span class="block text-xs text-gray-500">PNG, JPG, GIF up to 2MB</span>
                                    </label>
                                    <button type="button"
                                        class="w-full bg-[#CFB47D] text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#b59356] transition-colors"
                                        id="openCameraProfile">
                                        📷 Ambil dari Kamera
                                    </button>
                                </div>

                                <!-- Action Buttons -->
                                <div class="hidden space-y-2" id="action_buttons_rombong">
                                    <div class="flex space-x-2">
                                        <button type="button"
                                            class="flex-1 bg-green-500 text-white px-3 py-1 rounded text-xs font-medium hover:bg-green-600"
                                            id="confirm_rombong">
                                            ✓ OK
                                        </button>
                                        <button type="button"
                                            class="flex-1 bg-red-500 text-white px-3 py-1 rounded text-xs font-medium hover:bg-red-600"
                                            id="cancel_rombong">
                                            ✗ Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Tetangga Kanan -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Foto Tetangga Kanan</label>
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-green-400 transition-colors">

                                <!-- Current Image Display -->
                                @if (isset($rombong) && $rombong->foto_tetangga_kanan)
                                    @php
                                        $imagePath = storage_path('app/public/' . $rombong->foto_tetangga_kanan);
                                        $imageExists = file_exists($imagePath);
                                        $imageUrl = asset('storage/' . $rombong->foto_tetangga_kanan);
                                    @endphp

                                    @if ($imageExists)
                                        <div id="current_image_kanan">
                                            <img src="{{ $imageUrl }}" class="w-full h-32 object-cover rounded-lg mb-3"
                                                alt="Tetangga Kanan"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <p class="text-xs text-gray-500">Foto saat ini</p>

                                            <!-- Fallback message jika gambar gagal load -->
                                            <div style="display: none;" class="text-yellow-600 text-xs">
                                                <p>⚠️ Foto tidak dapat dimuat</p>
                                                <p class="text-xs">Path: {{ $rombong->foto_tetangga_kanan }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <!-- File ada di database tapi tidak ada di storage -->
                                        <div class="text-yellow-600 text-sm mb-3">
                                            <svg class="mx-auto h-8 w-8 text-yellow-500 mb-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                </path>
                                            </svg>
                                            <p>⚠️ Foto tetangga kanan terdaftar tapi file hilang</p>
                                            <p class="text-xs text-gray-500">Silakan upload ulang foto</p>

                                            @if (config('app.debug'))
                                                <details class="text-xs mt-2">
                                                    <summary class="cursor-pointer">Debug Info</summary>
                                                    <p>DB Path: {{ $rombong->foto_tetangga_kanan }}</p>
                                                    <p>Storage Path: {{ $imagePath }}</p>
                                                    <p>File Exists: {{ $imageExists ? 'Yes' : 'No' }}</p>
                                                </details>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <!-- Show upload area if no image -->
                                    <div class="text-gray-400 text-sm">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor"
                                            fill="none" viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p>Foto tetangga kanan belum diupload</p>
                                    </div>
                                @endif

                                <!-- Preview New Image -->
                                <img id="preview_tetangga_kanan" class="hidden w-full h-32 object-cover rounded-lg mb-3"
                                    alt="Preview Tetangga Kanan">

                                <!-- Upload Area -->
                                <div class="space-y-3 upload-area" id="upload_area_kanan" style="display: none;">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <input type="file" name="foto_tetangga_kanan" id="foto_tetangga_kanan"
                                        class="hidden" accept="image/*">
                                    <label for="foto_tetangga_kanan" class="cursor-pointer">
                                        <span class="block text-sm text-gray-600">Klik untuk upload</span>
                                        <span class="block text-xs text-gray-500">PNG, JPG, GIF up to 2MB</span>
                                    </label>
                                    <button type="button"
                                        class="w-full bg-[#CFB47D] text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#b59356] transition-colors"
                                        id="openCameraRight">
                                        📷 Ambil dari Kamera
                                    </button>
                                </div>

                                <!-- Action Buttons -->
                                <div class="hidden space-y-2" id="action_buttons_kanan">
                                    <div class="flex space-x-2">
                                        <button type="button"
                                            class="flex-1 bg-green-500 text-white px-3 py-1 rounded text-xs font-medium hover:bg-green-600"
                                            id="confirm_kanan">
                                            ✓ OK
                                        </button>
                                        <button type="button"
                                            class="flex-1 bg-red-500 text-white px-3 py-1 rounded text-xs font-medium hover:bg-red-600"
                                            id="cancel_kanan">
                                            ✗ Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Foto Tetangga Kiri -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Foto Tetangga Kiri</label>
                            <div
                                class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-purple-400 transition-colors">

                                <!-- Current Image Display -->
                                @if (isset($rombong) && $rombong->foto_tetangga_kiri)
                                    @php
                                        $imagePath = storage_path('app/public/' . $rombong->foto_tetangga_kiri);
                                        $imageExists = file_exists($imagePath);
                                        $imageUrl = asset('storage/' . $rombong->foto_tetangga_kiri);
                                    @endphp

                                    @if ($imageExists)
                                        <div id="current_image_kiri">
                                            <img src="{{ $imageUrl }}"
                                                class="w-full h-32 object-cover rounded-lg mb-3" alt="Foto Tetangga Kiri"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <p class="text-xs text-gray-500">Foto saat ini</p>

                                            <!-- Fallback message jika gambar gagal load -->
                                            <div style="display: none;" class="text-yellow-600 text-xs">
                                                <p>⚠️ Foto tidak dapat dimuat</p>
                                                <p class="text-xs">Path: {{ $rombong->foto_tetangga_kiri }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <!-- File ada di database tapi tidak ada di storage -->
                                        <div class="text-yellow-600 text-sm mb-3">
                                            <svg class="mx-auto h-8 w-8 text-yellow-500 mb-1" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
                                                </path>
                                            </svg>
                                            <p>⚠️ Foto tetangga kiri terdaftar tapi file hilang</p>
                                            <p class="text-xs text-gray-500">Silakan upload ulang foto</p>

                                            @if (config('app.debug'))
                                                <details class="text-xs mt-2">
                                                    <summary class="cursor-pointer">Debug Info</summary>
                                                    <p>DB Path: {{ $rombong->foto_tetangga_kiri }}</p>
                                                    <p>Storage Path: {{ $imagePath }}</p>
                                                    <p>File Exists: {{ $imageExists ? 'Yes' : 'No' }}</p>
                                                </details>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <!-- Show upload area if no image -->
                                    <div class="text-gray-400 text-sm">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" stroke="currentColor"
                                            fill="none" viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p>Foto tetangga kiri belum diupload</p>
                                    </div>
                                @endif

                                <!-- Preview New Image -->
                                <img id="preview_tetangga_kiri" class="hidden w-full h-32 object-cover rounded-lg mb-3"
                                    alt="Preview Tetangga Kiri">

                                <!-- Upload Area -->
                                <div class="space-y-3 upload-area" id="upload_area_kiri" style="display: none;">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <input type="file" name="foto_tetangga_kiri" id="foto_tetangga_kiri"
                                        class="hidden" accept="image/*">
                                    <label for="foto_tetangga_kiri" class="cursor-pointer">
                                        <span class="block text-sm text-gray-600">Klik untuk upload</span>
                                        <span class="block text-xs text-gray-500">PNG, JPG, GIF up to 2MB</span>
                                    </label>
                                    <button type="button"
                                        class="w-full bg-[#CFB47D] text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-[#b59356] transition-colors"
                                        id="openCameraLeft">
                                        📷 Ambil dari Kamera
                                    </button>
                                </div>

                                <!-- Action Buttons -->
                                <div class="hidden space-y-2" id="action_buttons_kiri">
                                    <div class="flex space-x-2">
                                        <button type="button"
                                            class="flex-1 bg-green-500 text-white px-3 py-1 rounded text-xs font-medium hover:bg-green-600"
                                            id="confirm_kiri">
                                            ✓ OK
                                        </button>
                                        <button type="button"
                                            class="flex-1 bg-red-500 text-white px-3 py-1 rounded text-xs font-medium hover:bg-red-600"
                                            id="cancel_kiri">
                                            ✗ Ganti
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Data Section -->
                    <div class="border-t border-gray-200">
                        <!-- Data Pribadi Section -->
                        <div class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Data Pribadi
                            </h3>
                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama Lengkap -->
                            <div class="space-y-2">
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="text" name="name" id="name"
                                        value="{{ old('name', $user->name ?? '') }}"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Masukkan nama lengkap Anda" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="email" name="email" id="email"
                                        value="{{ old('email', $user->email ?? '') }}"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="nama@email.com" required>
                                </div>
                            </div>

                            <!-- No HP -->
                            <div class="space-y-2">
                                <label for="no_telp" class="block text-sm font-medium text-gray-700">
                                    No WhatsApp <span class="text-red-500">*</span>
                                    <span class="text-xs text-gray-500">(Format: 62xxxxxxxxxx)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="text" name="no_telp" id="no_telp"
                                        value="{{ old('no_telp', $user->no_telp ?? '') }}"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('no_telp') border-red-500 @enderror"
                                        placeholder="62812xxxxxxxx" pattern="^62[0-9]{9,13}$"
                                        title="Format: 62 diikuti 9-13 digit angka" maxlength="15" required
                                        x-data="{
                                            phone: '{{ old('no_telp', $user->no_telp ?? '') }}',
                                            formatPhone() {
                                                let value = this.phone.replace(/\D/g, '');
                                                if (value && !value.startsWith('62')) {
                                                    if (value.startsWith('08')) {
                                                        value = '62' + value.substring(1);
                                                    } else if (value.startsWith('8')) {
                                                        value = '62' + value;
                                                    } else if (!value.startsWith('62')) {
                                                        value = '62' + value;
                                                    }
                                                }
                                                this.phone = value;
                                                $el.value = value;
                                                this.validatePhone();
                                            },
                                            validatePhone() {
                                                const pattern = /^62[0-9]{9,13}$/;
                                                const isValid = pattern.test(this.phone);
                                                $el.setCustomValidity(isValid ? '' : 'Format nomor WhatsApp harus: 62 diikuti 9-13 digit angka');
                                                $el.classList.toggle('border-red-500', !isValid);
                                                $el.classList.toggle('border-green-500', isValid && this.phone.length > 0);
                                            }
                                        }" x-model="phone" @input="formatPhone()"
                                        @blur="formatPhone()">

                                    <!-- Status Icon -->
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <svg x-show="phone && /^62[0-9]{9,13}$/.test(phone)"
                                            class="h-5 w-5 text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <svg x-show="phone && !/^62[0-9]{9,13}$/.test(phone)" class="h-5 w-5 text-red-500"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                @error('no_telp')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror

                                <!-- Help Text -->
                                <p class="text-xs text-gray-500">
                                    Contoh: 6281234567890 (gunakan kode negara 62 untuk Indonesia)
                                </p>
                            </div>

                            <!-- Alamat -->
                            <div class="space-y-2">
                                <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-3 flex items-start pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <textarea name="alamat" id="alamat" rows="3"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                                        placeholder="Masukkan alamat lengkap Anda" required>{{ old('alamat', $user->alamat ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Usaha Section -->
                    <div class="border-t border-gray-200">
                        <div class="bg-gradient-to-r from-[#b59356] to-[#CFB47D] px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                Data Usaha
                            </h3>
                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama Jualan -->
                            <div class="space-y-2">
                                <label for="nama_jualan" class="block text-sm font-medium text-gray-700">Nama
                                    Jualan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="text" name="nama_jualan" id="nama_jualan"
                                        value="{{ old('nama_jualan', $user->rombong->nama_jualan ?? '') }}"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Masukkan nama jualan Anda">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Change Password Section -->
                    <div class="border-t border-gray-200">
                        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                Ganti Password
                            </h3>
                            <p class="text-red-100 text-sm mt-1">Kosongkan jika tidak ingin mengubah password</p>
                        </div>

                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password Lama -->
                            <div class="space-y-2">
                                <label for="current_password" class="block text-sm font-medium text-gray-700">Password
                                    Lama</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="password" name="current_password" id="current_password"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Masukkan password lama">
                                </div>
                            </div>

                            <!-- Password Baru -->
                            <div class="space-y-2">
                                <label for="new_password" class="block text-sm font-medium text-gray-700">Password
                                    Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                            </path>
                                        </svg>
                                    </div>
                                    <input type="password" name="new_password" id="new_password" minlength="6"
                                        class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 @error('new_password') border-red-500 @enderror"
                                        placeholder="Masukkan password baru">

                                    <!-- Toggle Password Visibility -->
                                    <button type="button" id="toggleNewPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-gray-800">
                                        <svg id="eyeIconNew" class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        <svg id="eyeSlashIconNew" class="h-5 w-5 hidden" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21">
                                            </path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Simple Password Feedback -->
                                <div id="profile_password_feedback" class="text-sm mt-1" style="display: none;">
                                    <span id="profile_password_length" class="text-red-500">✗ Minimal 6 karakter</span>
                                </div>

                                @error('new_password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Konfirmasi Password Baru -->
                            <div class="md:col-span-2 space-y-2">
                                <label for="new_password_confirmation"
                                    class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="password" name="new_password_confirmation"
                                        id="new_password_confirmation"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Ulangi password baru">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-center">
                            @if ($rombong && $rombong->exists)
                                <button type="button" id="btnEdit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    Edit Profil
                                </button>
                                <button type="submit" id="btnSave"
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 hidden">
                                    <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan Perubahan
                                </button>
                            @else
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                                    <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan Profile
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- =================== MODAL KAMERA =================== --}}
    <div class="fixed inset-0 z-50 hidden" id="cameraModal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-75 transition-opacity"></div>

        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full mx-auto">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 rounded-t-2xl">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Ambil Foto
                    </h3>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div class="rounded-lg overflow-hidden bg-gray-100">
                        <video id="cameraStream" autoplay playsinline class="w-full h-auto max-h-64 object-cover"></video>
                    </div>
                    <canvas id="cameraCanvas" class="hidden"></canvas>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button type="button" id="capturePhoto"
                            class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Ambil Foto
                        </button>
                        <button type="button" id="closeCamera"
                            class="flex-1 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // =================== EDIT MODE CONTROL ===================
            const btnEdit = document.getElementById("btnEdit");
            const btnSave = document.getElementById("btnSave");
            const formInputs = document.querySelectorAll(
                "form input[type='text'], form input[type='tel'], form textarea");
            const uploadAreas = document.querySelectorAll(".upload-area");

            // Initially disable form inputs and hide upload areas
            if (btnEdit) {
                // Disable all form inputs
                formInputs.forEach(input => input.setAttribute("disabled", true));

                // Hide all upload areas
                uploadAreas.forEach(area => area.style.display = "none");

                btnEdit.addEventListener("click", function() {
                    // Enable all form inputs
                    formInputs.forEach(input => input.removeAttribute("disabled"));

                    // Show all upload areas
                    uploadAreas.forEach(area => area.style.display = "block");

                    // Toggle buttons
                    btnEdit.classList.add("hidden");
                    btnSave.classList.remove("hidden");

                    // Show upload areas for images that don't have current images
                    showUploadAreasForEmptyImages();
                });
            } else {
                // If no edit button (new profile), show upload areas immediately
                uploadAreas.forEach(area => area.style.display = "block");
                showUploadAreasForEmptyImages();
            }

            // Function to show upload areas for images without current photos
            function showUploadAreasForEmptyImages() {
                const imageConfigs = [{
                        currentImageId: 'current_image_rombong',
                        uploadAreaId: 'upload_area_rombong'
                    },
                    {
                        currentImageId: 'current_image_kanan',
                        uploadAreaId: 'upload_area_kanan'
                    },
                    {
                        currentImageId: 'current_image_kiri',
                        uploadAreaId: 'upload_area_kiri'
                    }
                ];

                imageConfigs.forEach(config => {
                    const currentImage = document.getElementById(config.currentImageId);
                    const uploadArea = document.getElementById(config.uploadAreaId);

                    // Show upload area if no current image exists OR if current image is hidden
                    if (uploadArea) {
                        if (!currentImage || currentImage.style.display === 'none' || !currentImage
                            .querySelector('img')) {
                            uploadArea.style.display = "block";
                        }
                    }
                });
            }

            // =================== IMAGE PREVIEW SETUP ===================
            const configs = [{
                    inputId: 'foto_rombong',
                    previewId: 'preview_rombong',
                    uploadAreaId: 'upload_area_rombong',
                    actionButtonsId: 'action_buttons_rombong',
                    confirmId: 'confirm_rombong',
                    cancelId: 'cancel_rombong',
                    currentImageId: 'current_image_rombong'
                },
                {
                    inputId: 'foto_tetangga_kanan',
                    previewId: 'preview_tetangga_kanan',
                    uploadAreaId: 'upload_area_kanan',
                    actionButtonsId: 'action_buttons_kanan',
                    confirmId: 'confirm_kanan',
                    cancelId: 'cancel_kanan',
                    currentImageId: 'current_image_kanan'
                },
                {
                    inputId: 'foto_tetangga_kiri',
                    previewId: 'preview_tetangga_kiri',
                    uploadAreaId: 'upload_area_kiri',
                    actionButtonsId: 'action_buttons_kiri',
                    confirmId: 'confirm_kiri',
                    cancelId: 'cancel_kiri',
                    currentImageId: 'current_image_kiri'
                }
            ];

            configs.forEach(cfg => setupImagePreview(cfg));

            function setupImagePreview({
                inputId,
                previewId,
                uploadAreaId,
                actionButtonsId,
                confirmId,
                cancelId,
                currentImageId
            }) {
                const input = document.getElementById(inputId);
                const preview = document.getElementById(previewId);
                const uploadArea = document.getElementById(uploadAreaId);
                const actionButtons = document.getElementById(actionButtonsId);
                const confirmBtn = document.getElementById(confirmId);
                const cancelBtn = document.getElementById(cancelId);
                const currentImage = document.getElementById(currentImageId);

                if (!input || !preview || !uploadArea || !actionButtons || !confirmBtn || !cancelBtn) {
                    console.error('Elemen tidak ditemukan untuk:', inputId);
                    return;
                }

                input.addEventListener('change', function() {
                    if (!input.files || !input.files[0]) return;
                    const file = input.files[0];

                    // Validate file
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file terlalu besar! Maksimal 2MB.');
                        input.value = '';
                        return;
                    }
                    if (!file.type.startsWith('image/')) {
                        alert('File harus berupa gambar!');
                        input.value = '';
                        return;
                    }

                    // Show preview
                    const objectUrl = URL.createObjectURL(file);
                    preview.src = objectUrl;
                    preview.onload = () => URL.revokeObjectURL(objectUrl);

                    // Hide current image and upload area, show preview and action buttons
                    if (currentImage) currentImage.style.display = 'none';
                    uploadArea.style.display = 'none';
                    preview.classList.remove('hidden');
                    actionButtons.classList.remove('hidden');
                });

                cancelBtn.addEventListener('click', function() {
                    // Reset everything
                    input.value = '';
                    preview.src = '';
                    preview.classList.add('hidden');
                    actionButtons.classList.add('hidden');

                    // Show current image if exists, otherwise show upload area
                    if (currentImage) {
                        currentImage.style.display = 'block';
                    } else {
                        uploadArea.style.display = 'block';
                    }
                });

                confirmBtn.addEventListener('click', function() {
                    // Hide action buttons, keep preview visible
                    actionButtons.classList.add('hidden');
                });
            }

            // =================== CAMERA FUNCTIONALITY ===================
            let cameraStream = null;
            let currentInputTarget = null;

            function openCamera(inputId) {
                currentInputTarget = document.getElementById(inputId);
                if (!currentInputTarget) {
                    alert('Target input tidak ditemukan untuk kamera.');
                    return;
                }

                const cameraModal = document.getElementById("cameraModal");
                cameraModal.classList.remove("hidden");

                navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'environment'
                        }
                    })
                    .then(function(stream) {
                        cameraStream = stream;
                        const video = document.getElementById("cameraStream");
                        video.srcObject = stream;
                        video.play().catch(() => {});
                    })
                    .catch(function(err) {
                        console.error('Error accessing camera:', err);
                        alert('Tidak dapat mengakses kamera. Pastikan browser diizinkan mengakses kamera.');
                        closeCamera();
                    });
            }

            function closeCamera() {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(track => track.stop());
                    cameraStream = null;
                }
                const cameraModal = document.getElementById("cameraModal");
                cameraModal.classList.add("hidden");
                const video = document.getElementById("cameraStream");
                if (video) video.srcObject = null;
            }

            // Camera button event listeners
            document.getElementById("openCameraProfile")?.addEventListener("click", function() {
                openCamera("foto_rombong");
            });
            document.getElementById("openCameraLeft")?.addEventListener("click", function() {
                openCamera("foto_tetangga_kiri");
            });
            document.getElementById("openCameraRight")?.addEventListener("click", function() {
                openCamera("foto_tetangga_kanan");
            });

            document.getElementById("closeCamera")?.addEventListener("click", closeCamera);

            document.getElementById("capturePhoto")?.addEventListener("click", function() {
                const video = document.getElementById("cameraStream");
                const canvas = document.getElementById("cameraCanvas");
                if (!video || !canvas || !currentInputTarget) {
                    console.error('Video/canvas/target input tidak ditemukan');
                    closeCamera();
                    return;
                }

                // Set canvas size to match video
                canvas.width = video.videoWidth || 1280;
                canvas.height = video.videoHeight || 720;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                canvas.toBlob(function(blob) {
                    if (!blob) {
                        alert('Gagal membuat gambar dari kamera.');
                        return;
                    }
                    const file = new File([blob], 'camera-photo.jpg', {
                        type: 'image/jpeg'
                    });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    currentInputTarget.files = dataTransfer.files;

                    // Trigger change event to update preview
                    const event = new Event('change', {
                        bubbles: true
                    });
                    currentInputTarget.dispatchEvent(event);
                }, 'image/jpeg', 0.85);

                closeCamera();
            });

            // Close modal when clicking backdrop
            document.getElementById("cameraModal")?.addEventListener("click", function(e) {
                if (e.target === this) closeCamera();
            });

            // =================== FORM VALIDATION ===================
            const form = document.querySelector('form[action*="profile.update"]');

            // Form submission validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    let isValid = true;
                    const errors = [];

                    // Validate required fields
                    const requiredFields = [{
                            id: 'name',
                            name: 'Nama Lengkap',
                            min: 2
                        },
                        {
                            id: 'alamat',
                            name: 'Alamat',
                            min: 5
                        },
                        {
                            id: 'no_telp',
                            name: 'No WhatsApp',
                            pattern: /^62[0-9]{9,13}$/
                        },
                        {
                            id: 'email',
                            name: 'Email',
                            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
                        }
                    ];

                    requiredFields.forEach(field => {
                        const input = document.getElementById(field.id);
                        if (input) {
                            const value = input.value.trim();

                            if (!value) {
                                errors.push(`${field.name} wajib diisi.`);
                                input.classList.add('border-red-500');
                                isValid = false;
                            } else if (field.min && value.length < field.min) {
                                errors.push(`${field.name} minimal ${field.min} karakter.`);
                                input.classList.add('border-red-500');
                                isValid = false;
                            } else if (field.pattern && !field.pattern.test(value)) {
                                if (field.id === 'no_telp') {
                                    errors.push(
                                        'Format nomor WhatsApp harus: 62 diikuti 9-13 digit angka.'
                                    );
                                } else if (field.id === 'email') {
                                    errors.push('Format email tidak valid.');
                                }
                                input.classList.add('border-red-500');
                                isValid = false;
                            } else {
                                input.classList.remove('border-red-500');
                                input.classList.add('border-green-500');
                            }
                        }
                    });

                    // Password validation if changing password
                    const currentPassword = document.getElementById('current_password');
                    const newPassword = document.getElementById('new_password');
                    const confirmPassword = document.getElementById('new_password_confirmation');

                    if (currentPassword && newPassword && confirmPassword) {
                        if (newPassword.value || confirmPassword.value || currentPassword.value) {
                            if (!currentPassword.value) {
                                errors.push('Password lama harus diisi untuk mengubah password.');
                                currentPassword.classList.add('border-red-500');
                                isValid = false;
                            }

                            if (!newPassword.value) {
                                errors.push('Password baru harus diisi.');
                                newPassword.classList.add('border-red-500');
                                isValid = false;
                            } else if (newPassword.value.length < 6) {
                                errors.push('Password baru minimal 6 karakter.');
                                newPassword.classList.add('border-red-500');
                                isValid = false;
                            }

                            if (newPassword.value !== confirmPassword.value) {
                                errors.push('Konfirmasi password tidak cocok.');
                                confirmPassword.classList.add('border-red-500');
                                isValid = false;
                            }

                            if (currentPassword.value && newPassword.value && currentPassword.value ===
                                newPassword.value) {
                                errors.push('Password baru harus berbeda dengan password lama.');
                                newPassword.classList.add('border-red-500');
                                isValid = false;
                            }
                        }
                    }

                    // File validation
                    const fileInputs = ['foto_rombong', 'foto_tetangga_kanan', 'foto_tetangga_kiri'];
                    fileInputs.forEach(inputId => {
                        const input = document.getElementById(inputId);
                        if (input && input.files.length > 0) {
                            const file = input.files[0];
                            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg',
                                'image/gif'
                            ];
                            const maxSize = 2 * 1024 * 1024; // 2MB

                            if (!allowedTypes.includes(file.type)) {
                                errors.push(
                                    `${inputId.replace('_', ' ')} harus berformat jpeg, png, jpg, atau gif.`
                                );
                                isValid = false;
                            }

                            if (file.size > maxSize) {
                                errors.push(`${inputId.replace('_', ' ')} maksimal 2MB.`);
                                isValid = false;
                            }
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();

                        // Show errors
                        let errorHtml =
                            '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
                        errorHtml += '<strong>Terdapat kesalahan:</strong><ul class="mt-2">';
                        errors.forEach(error => {
                            errorHtml += `<li>• ${error}</li>`;
                        });
                        errorHtml += '</ul></div>';

                        const existingError = document.querySelector('.bg-red-100');
                        if (existingError) {
                            existingError.remove();
                        }

                        form.insertAdjacentHTML('afterbegin', errorHtml);

                        // Scroll to top to show errors
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                });

                // Real-time validation on input
                ['name', 'alamat', 'email', 'current_password', 'new_password_confirmation'].forEach(fieldId => {
                    const input = document.getElementById(fieldId);
                    if (input) {
                        input.addEventListener('input', function() {
                            this.classList.remove('border-red-500', 'border-green-500');

                            if (this.value.trim()) {
                                this.classList.add('border-green-500');
                            }
                        });
                    }
                });

                // Special handling for new_password with simple validation
                const newPasswordInput = document.getElementById('new_password');
                const passwordFeedbackDiv = document.getElementById('profile_password_feedback');

                if (newPasswordInput && passwordFeedbackDiv) {
                    newPasswordInput.addEventListener('input', function() {
                        this.classList.remove('border-red-500', 'border-green-500');

                        if (this.value.length > 0) {
                            passwordFeedbackDiv.style.display = 'block';
                            const isValid = validateProfilePasswordLength(this.value);

                            if (isValid) {
                                this.classList.add('border-green-500');
                                this.setCustomValidity('');
                            } else {
                                this.classList.add('border-red-500');
                                this.setCustomValidity('Password minimal 6 karakter');
                            }
                        } else {
                            passwordFeedbackDiv.style.display = 'none';
                            this.setCustomValidity('');
                        }
                    });
                }

                // Toggle new password visibility
                const toggleNewPassword = document.getElementById('toggleNewPassword');
                if (toggleNewPassword) {
                    toggleNewPassword.addEventListener('click', function() {
                        const passwordField = document.getElementById('new_password');
                        const eyeIcon = document.getElementById('eyeIconNew');
                        const eyeSlashIcon = document.getElementById('eyeSlashIconNew');

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
                }
            }

            // Fungsi validasi password length sederhana untuk profile
            function validateProfilePasswordLength(password) {
                const isValid = password.length >= 6;

                const lengthCheck = document.getElementById('profile_password_length');
                if (lengthCheck) {
                    if (isValid) {
                        lengthCheck.className = 'text-green-500';
                        lengthCheck.textContent = '✓ Minimal 6 karakter';
                    } else {
                        lengthCheck.className = 'text-red-500';
                        lengthCheck.textContent = '✗ Minimal 6 karakter';
                    }
                }

                return isValid;
            }

        }); // end DOMContentLoaded
    </script>
@endsection
