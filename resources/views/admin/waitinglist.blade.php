@extends('master.masterAdmin')

@section('title', 'Waiting List')
@section('content')

<div class="min-h-screen bg-gray-100 p-4 sm:p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Waiting List Management</h1>
        <p class="text-gray-600">Kelola persetujuan user dan pengajuan anggota lapak</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-green-700 font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-red-700 font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Pending Member Applications Section -->
    @if(isset($pendingRombong) && $pendingRombong->count() > 0)
        <div class="bg-white rounded-2xl shadow-xl mb-8 overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 p-6" style="background: linear-gradient(135deg, #b59356 0%, #CFB47D 100%);">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
                    Pengajuan Anggota Lapak
                </h2>
                <p class="text-white opacity-90 mt-1">User yang ingin bergabung dengan lapak</p>
            </div>
            
            <div class="p-6">
                @foreach($pendingRombong as $rombong)
                    <div class="border border-gray-200 rounded-xl p-4 mb-4 last:mb-0 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-400 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    {{ strtoupper(substr($rombong->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-800">{{ $rombong->user->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $rombong->user->email }}</p>
                                </div>
                            </div>
                            <div class="ml-15 space-y-1">
                                <p class="text-sm text-gray-600"><strong>Lapak:</strong> {{ $rombong->lapak->nama_lapak }}</p>
                                <p class="text-sm text-gray-600"><strong>Nama Usaha:</strong> {{ $rombong->user->rombong->nama_jualan ?? '-' }}</p>
                                <p class="text-sm text-gray-600"><strong>Tanggal Pengajuan:</strong> {{ optional(\Carbon\Carbon::parse($rombong->tanggal_pengajuan ?? null))->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.anggota.approve', $rombong->waiting_list_id) }}">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors duration-200 shadow-md">
                                    Setujui
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.anggota.reject', $rombong->waiting_list_id) }}">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors duration-200 shadow-md">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Waiting List Section -->
    <div class="bg-white rounded-2xl shadow-xl mb-8 overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-600 to-yellow-400 p-6" style="background: linear-gradient(135deg, #b59356 0%, #CFB47D 100%);">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <svg class="w-7 h-7 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                </svg>
                Pending Users
            </h2>
            <p class="text-white opacity-90 mt-1">User yang menunggu persetujuan</p>
        </div>
        
        <div class="p-6">
            @forelse($users as $user)
                <div class="border border-gray-200 rounded-xl p-4 mb-4 last:mb-0 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-400 rounded-full flex items-center justify-center text-white font-bold text-lg" style="background: linear-gradient(135deg, #b59356 0%, #CFB47D 100%);">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg text-gray-800">{{ $user->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('admin.approve', $user->user_id) }}">
                                @csrf
                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors duration-200 shadow-md">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.reject', $user->user_id) }}">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors duration-200 shadow-md">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                    Reject
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
                    <p class="text-gray-500 text-lg">Tidak ada user pending</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@endsection
                                