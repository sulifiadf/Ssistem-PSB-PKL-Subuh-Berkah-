@extends('master.masterAdmin')
@section('title', 'User Anggota')
@section('content')

<div class="min-h-screen bg-gray-100 p-4 sm:p-6">
    {{-- tombol tambah user --}}
    <div class="flex justify-between items-center mb-4 px-6 pt-4">
        <h2 class="text-xl font-bold text-gray-800">Daftar User</h2>
        <a href="{{ route('admin.user.create') }}" 
        class="bg-[#b59356] text-white px-4 py-2 rounded-lg shadow hover:bg-[#a38346] transition">
            + Tambah User
        </a>
    </div>


    <!-- All Users Table Section -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 p-6" style="background: linear-gradient(135deg, #b59356 0%, #CFB47D 100%);">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Daftar Semua User
            </h2>
            <p class="text-white opacity-90 mt-1">Kelola dan monitor semua user terdaftar</p>
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead style="background-color: #CFB47D;">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nama</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Alamat</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">No Telp</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Nama Jualan</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Foto</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($allUsers as $user)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-600 to-yellow-400 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background: linear-gradient(135deg, #b59356 0%, #CFB47D 100%);">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->alamat }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->no_telp }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->rombong->nama_jualan ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if(optional($user->rombong)->foto_rombong)
                                    <img src="{{ asset('storage/'.optional($user->rombong)->foto_rombong) }}" 
                                        class="w-12 h-12 object-cover rounded-lg shadow-sm">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.user.update', $user->user_id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" onchange="this.form.submit()" 
                                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent shadow-sm" style="--tw-ring-color: #b59356;">
                                        <option value="tetap" {{ $user->status == 'tetap' ? 'selected' : '' }}>Tetap</option>
                                        <option value="sementara" {{ $user->status == 'sementara' ? 'selected' : '' }}>Sementara</option>
                                        <option value="pending" {{ $user->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="rejected" {{ $user->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="approve" {{ $user->status == 'approve' ? 'selected' : '' }}>Approved</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.user.edit', $user->user_id) }}" 
                                        class="text-blue-500 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-all duration-200">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.user.destroy', $user->user_id) }}" method="POST" 
                                            onsubmit="return confirm('Yakin hapus user ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-all duration-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6.5l1.5 1.5A1 1 0 0116 17H4a1 1 0 01-.5-1.866L5 13.5V5zM6 5v9H4a1 1 0 000 2h12a1 1 0 100-2h-2V5H6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                <p class="text-gray-500 text-lg">Belum ada user terdaftar</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden p-6">
            @forelse($allUsers as $user)
                <div class="bg-gray-50 rounded-xl p-4 mb-4 last:mb-0 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-400 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #b59356 0%, #CFB47D 100%);">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm mb-4">
                        <div><span class="font-medium text-gray-600">Alamat:</span> {{ $user->alamat }}</div>
                        <div><span class="font-medium text-gray-600">No Telp:</span> {{ $user->no_telp }}</div>
                        <div><span class="font-medium text-gray-600">Nama Jualan:</span> {{ $user->rombong->nama_jualan ?? '-' }}</div>
                    </div>

                    <div class="flex items-center justify-between">
                        <form action="{{ route('admin.user.update', $user->user_id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()" 
                                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent shadow-sm" style="--tw-ring-color: #b59356;">
                                <option value="tetap" {{ $user->status == 'tetap' ? 'selected' : '' }}>Tetap</option>
                                <option value="sementara" {{ $user->status == 'sementara' ? 'selected' : '' }}>Sementara</option>
                                <option value="pending" {{ $user->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="rejected" {{ $user->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="approve" {{ $user->status == 'approve' ? 'selected' : '' }}>Approved</option>
                            </select>
                        </form>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('admin.user.edit', $user->user_id) }}" 
                                class="text-blue-500 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-all duration-200">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                </svg>
                            </a>
                            <form action="{{ route('admin.user.destroy', $user->user_id) }}" method="POST" 
                                onsubmit="return confirm('Yakin hapus user ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9zM4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6.5l1.5 1.5A1 1 0 0116 17H4a1 1 0 01-.5-1.866L5 13.5V5zM6 5v9H4a1 1 0 000 2h12a1 1 0 100-2h-2V5H6z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-gray-500 text-lg">Belum ada user terdaftar</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection