<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\kehadiran;
use App\Models\User;
use Carbon\Carbon;

class KehadiranController extends Controller
{
    public function konfirmasi (Request $request)
    {
        $userId = $request->user_id;
        $status = strtolower(trim($request->status));

        if(!in_array($status, ['masuk', 'libur'])) {
            return response()->json(['message' => 'Status tidak valid. Gunakan "masuk" atau "libur".'], 400);
        }

        $statusFinal = $status === 'masuk' ? 'masuk' : 'libur';

        kehadiran::updateOrCreate(
            ['user_id' => $userId, 'tanggal' => Carbon::today()],
            ['status' => $statusFinal, 'waktu_konfirmasi' => Carbon::now()]
        );

        $user = User::find($userId);
        $namaUser = $user ? $user->name : 'User';

        return response()->json([
            'message' => "Kehadiran untuk {$namaUser} pada tanggal " . Carbon::today()->toDateString() . " telah dikonfirmasi sebagai '{$statusFinal}'."], 200);
    }

    public function konfirmasiViaWA(Request $request)
    {
        $phone = $request->phone;
        $message = strtolower (trim($request->message));

        $user = User::where('no_telp', $phone)
            ->where('status', 'approve')
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan atau belum diapprove.'], 404);
        }

        //cek apakah user memiliki record kehadiran hari ini 
        $kehadiran= kehadiran:: where('user_id', $user->user_id)
            ->whereDate('tanggal', Carbon::today())
            ->where('pesan_wa_terkirim', true) //hanya yang sudah dikirim pesan WA
            ->first();

        if (!$kehadiran) {
            return response()->json(['message' => 'Tidak ada permintaan konfirmasi kehadiran untuk hari ini.'], 400);
        }

        $status = null;
        if (str_contains($message, 'masuk')) {
            $status = 'masuk';
        } elseif (str_contains($message, 'libur')) {
            $status = 'libur';
        }

        if (!$status) {
            // Kirim pesan panduan jika format salah
            $this->kirimPesanPanduan($phone, $user->name);
            return response()->json(['message' => 'Format pesan tidak valid.'], 400);
        }

        // Update kehadiran
        $kehadiran->update([
            'status' => $status,
            'waktu_konfirmasi' => Carbon::now(),
        ]);

        // Kirim konfirmasi balik via WA
        $this->kirimKonfirmasiBalik($phone, $user->name, $status);

        return response()->json([
            'message' => "Konfirmasi {$user->name} berhasil: {$status}",
            'status' => $status
        ], 200);
    }

    private function kirimPesanPanduan($phone, $name)
    {
        $pesan = "Halo {$nama}!\n\n";
        $pesan .= "Format pesan Anda tidak dikenali.\n";
        $pesan .= "Silakan balas dengan:\n";
        $pesan .= "- MASUK (jika Anda akan berjualan)\n";
        $pesan .= "- LIBUR (jika Anda tidak berjualan)\n\n";
    }

    private function kirimKonfirmasiBalik($phone, $name, $status)
    {
        $statusText = $status === 'masuk' ? 'masuk' : 'libur';
        $pesan = "Halo {$name}!\n\n";
        $pesan .= "Status kehadiran Anda hari ini: {$statusText}\n";
        $pesan .= "Waktu konfirmasi: " . Carbon::now()->format('H:i:s') . "\n\n";
        $pesan .= "Terima kasih sudah konfirmasi tepat waktu! 🙏";
    }

    public function getStatusKehadiran()
    {
        $today = Carbon::today();
        $kehadirans = kehadiran::with('user')
                            ->whereDate('tanggal', $today)
                            ->get();

        $data = [];
        foreach ($kehadirans as $kehadiran) {
            $data[] = [
                'user_id' => $kehadiran->user_id,
                'nama' => $kehadiran->user->name,
                'status' => $kehadiran->status,
                'waktu_konfirmasi' => $kehadiran->waktu_konfirmasi,
                'warna_button' => $this->getWarnaButton($kehadiran->status)
            ];
        }

        return response()->json($data);
    }

    private function getWarnaButton($status)
    {
        switch ($status) {
            case 'masuk':
                return 'bg-green-500 hover:bg-green-600';
            case 'libur':
                return 'bg-red-500 hover:bg-red-600';
            default:
                return 'bg-[#CFB47D] hover:bg-[#b89e65]';
        }
    }

    private function kirimWAResponse($phone, $message)
    {
        try {
            $apiUrl = env('WHATSAPP_API_URL');
            $apiKey = env('WHATSAPP_API_KEY');

            $response = Http::post($apiUrl, [
                'phone' => $phone,
                'message' => $message,
                'token' => $apiKey
            ]);

            if ($response->successful()){
                $data = $response->json();
                if ($data['status']?? false){
                    \Log::info("Pesan WA berhasil dikirim", ['phone' => $phone]);
                    return true;
                }
            }

            \Log::error("Wablas failed", ['response' => $response->body()]);
            return false;

        } catch (\Exception $e) {
        \Log::error("Wablas error: " . $e->getMessage());
        return false;
    }
    }
}
