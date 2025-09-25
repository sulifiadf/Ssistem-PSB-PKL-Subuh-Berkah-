@extends('master.masterUser')
@section('title', 'Konfirmasi Kehadiran')
@section('content')

<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4" x-data="{ openModal: false, message: '' }">

    <h1 class="text-2xl font-bold mb-6">Konfirmasi Kehadiran Hari Ini</h1>

    <div class="flex gap-4 mb-6">
        <!-- Tombol Masuk -->
        <button 
            @click="konfirmasi('masuk')"
            class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold shadow"
        >
            Konfirmasi Masuk
        </button>

        <!-- Tombol Libur -->
        <button 
            @click="konfirmasi('libur')"
            class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold shadow"
        >
            Konfirmasi Libur
        </button>
    </div>

    <!-- Modal -->
    <div 
        x-show="openModal" 
        x-transition
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
            <h2 class="text-lg font-bold mb-4">✅ Konfirmasi Berhasil</h2>
            <p class="mb-4" x-text="message"></p>
            <button 
                @click="openModal=false" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg"
            >
                Tutup
            </button>
        </div>
    </div>

</div>

@section('scripts')
<script>
    function konfirmasi(status) {
        // form data
        let formData = new FormData();
        formData.append('status', status);

        // kirim ke backend
        fetch("{{ route('user.kehadiran.konfirmasi') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                // tampilkan modal sukses
                Alpine.store('konfirmasi').message = "Status kehadiran Anda: " + data.status;
                Alpine.store('konfirmasi').openModal = true;
            } else {
                Alpine.store('konfirmasi').message = data.message ?? 'Gagal konfirmasi';
                Alpine.store('konfirmasi').openModal = true;
            }
        })
        .catch(err => {
            Alpine.store('konfirmasi').message = 'Terjadi kesalahan koneksi';
            Alpine.store('konfirmasi').openModal = true;
        });
    }

    document.addEventListener('alpine:init', () => {
        Alpine.store('konfirmasi', {
            openModal: false,
            message: ''
        });
        Alpine.data('konfirmasi', () => ({
            openModal: Alpine.store('konfirmasi').openModal,
            message: Alpine.store('konfirmasi').message,
            konfirmasi
        }));
    });
</script>

<script src="//unpkg.com/alpinejs" defer></script>
@endsection
