@extends('master.masterUser')
@section('title', 'Konfirmasi Kehadiran')
@section('content')

    <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-4" x-data="konfirmasi()">

        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
            <h1 class="text-2xl font-bold mb-6 text-center">Konfirmasi Kehadiran</h1>

            <div class="mb-6">
                <p class="text-gray-600 mb-2">Nama: <span class="font-semibold">{{ $user->name }}</span></p>
                <p class="text-gray-600 mb-2">Lapak: <span class="font-semibold">
                        @if ($user->rombongs && $user->rombongs->first() && $user->rombongs->first()->lapak)
                            {{ $user->rombongs->first()->lapak->nama_lapak }}
                        @elseif($user->rombong && $user->rombong->lapak)
                            {{ $user->rombong->lapak->nama_lapak }}
                        @else
                            Lapak tidak ditemukan
                        @endif
                    </span></p>
                <p class="text-gray-600">Tanggal: <span class="font-semibold">{{ date('d/m/Y') }}</span></p>

                @if (isset($statusKonfirmasi))
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-700"><strong>Status:</strong> {{ $statusKonfirmasi['pesan'] }}</p>
                    </div>
                @endif
            </div>

            @if (isset($statusKonfirmasi) && !$statusKonfirmasi['bisa_konfirmasi'])
                <!-- Jika tidak bisa konfirmasi (untuk akses WA) -->
                <div class="text-center p-4 bg-red-50 border border-red-200 rounded-lg mb-6">
                    @if (
                        $statusKonfirmasi['status'] === 'sudah_konfirmasi' ||
                            $statusKonfirmasi['status'] === 'sudah_masuk' ||
                            $statusKonfirmasi['status'] === 'sudah_libur')
                        <p class="text-red-600">✅ Anda sudah melakukan konfirmasi hari ini</p>
                    @elseif($statusKonfirmasi['status'] === 'menunggu_giliran')
                        <p class="text-red-600">⏳ Belum tiba giliran Anda untuk konfirmasi</p>
                    @else
                        <p class="text-red-600">❌ Tidak dapat melakukan konfirmasi saat ini</p>
                    @endif
                </div>
            @else
                <div class="flex gap-4 justify-center" x-show="!status">
                    <!-- Tombol Masuk -->
                    <button @click="konfirmasi('masuk')"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold shadow transition"
                        :disabled="status">
                        Konfirmasi MASUK
                    </button>

                    <!-- Tombol Libur -->
                    <button @click="konfirmasi('libur')"
                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold shadow transition"
                        :disabled="status">
                        Konfirmasi LIBUR
                    </button>
                </div>

                <!-- Status Konfirmasi -->
                <div x-show="status" class="text-center">
                    <div class="mb-4">
                        <template x-if="status === 'masuk'">
                            <div class="text-green-500 font-bold text-xl">✅ Konfirmasi MASUK berhasil</div>
                        </template>
                        <template x-if="status === 'libur'">
                            <div class="text-red-500 font-bold text-xl">❌ Konfirmasi LIBUR berhasil</div>
                        </template>
                    </div>
                    <p class="text-gray-600">Status kehadiran Anda telah tercatat.</p>
                </div>
        </div>
        @endif

        <!-- Modal -->
        <div x-show="openModal" x-transition
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
                <h2 class="text-lg font-bold mb-4">✅ Konfirmasi Berhasil</h2>
                <p class="mb-4" x-text="message"></p>
                <button @click="openModal=false" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Tutup
                </button>
            </div>
        </div>

    </div>

@section('scripts')
    <script>
        function konfirmasi() {
            return {
                openModal: false,
                message: '',
                status: null,
                konfirmasi(type) {
                    let formData = new FormData();
                    formData.append('status', type);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch("{{ isset($token) ? route('kehadiran.wa-konfirmasi.submit', $token) : (isset($user->user_id) ? route('user.kehadiran.konfirmasi', ['userId' => $user->user_id, 'token' => $token ?? '']) : '#') }}", {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.message = "Status kehadiran Anda: " + data.status;
                                this.openModal = true;
                                this.status = type;
                            } else {
                                this.message = data.message || 'Terjadi kesalahan';
                                this.openModal = true;
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            this.message = 'Terjadi kesalahan jaringan';
                            this.openModal = true;
                        });
                }
            }
        }
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
@endsection
