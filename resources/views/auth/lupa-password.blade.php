@extends('master.masterUser')
@section('title', 'lupa password')

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

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    
                        <div>
                            <label for="email" class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" id="email" name="email" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 
                                focus:outline-none focus:ring-2 focus:ring-[#b59356] focus:border-transparent">
                        </div>

                        <!-- Tombol -->
                        <div class="flex gap-3 pt-2">
                            <button type="submit" 
                                    class="w-1/2 bg-[#b59356] text-white font-semibold py-2 px-4 rounded-lg hover:bg-[#a08347] transition duration-200">
                                Send
                            </button>
                        </div>

                        @if (session('status'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mt-4">
                                {{ session('status') }}
                            </div>
                            
                        @endif
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js -->
<script src="//unpkg.com/alpinejs" defer></script>

@endsection
