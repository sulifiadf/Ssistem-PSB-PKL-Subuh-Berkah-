<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HelpersController extends Controller
{
    public function testEnv()
    {
        return response()->json([
            'app_name' => env('APP_NAME'),
            'whatsapp_api_url' => env('WHATSAPP_API_URL'),
            'whatsapp_api_key' => env('WHATSAPP_API_KEY'),
            'env_path' => app()->environmentFilePath(),
            'base_path' => base_path(),
        ]);
    }

    public function testWa(Request $request)
    {
        try {
            // Test nomor WA (bisa dari parameter atau hardcode untuk testing)
            $nomorWa = $request->input('nomor') ?? '628xxxxxxxxxx'; // Ganti dengan nomor WA Anda untuk test
            
            // Pesan test
            $pesan = "🔔 Ini adalah pesan test dari Sistem PSB.\n";
            $pesan .= "Waktu: " . now()->format('Y-m-d H:i:s') . "\n";
            $pesan .= "Jika Anda menerima pesan ini, berarti API WhatsApp berfungsi dengan baik.";

            // Debug info
            $apiUrl = 'https://pati.wablas.com/api/send-message';  // hardcode URL untuk testing
            $apiToken = env('WHATSAPP_API_KEY');

            // Pastikan token ada dan valid
            if (empty($apiToken)) {
                $apiToken = 'OXBjsXPzOPDhjmJ76d995uubCfdmNKX1IVigPhoLsYmsCvxNkEeXxTi';  // Menggunakan token dari .env sebagai fallback
            }

            // Log configuration untuk debugging
            Log::info('WA Config:', [
                'url' => $apiUrl,
                'token_exists' => !empty($apiToken),
                'token_length' => strlen($apiToken ?? ''),
                'token_full' => $apiToken,  // tampilkan token lengkap untuk debugging
                'phone' => $nomorWa
            ]);

            if (empty($apiUrl) || empty($apiToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi WhatsApp belum lengkap',
                    'missing' => [
                        'url' => empty($apiUrl),
                        'token' => empty($apiToken)
                    ]
                ], 500);
            }

            // Log untuk debugging
            Log::info('Debug Values:', [
                'API URL' => $apiUrl,
                'Token' => $apiToken,
                'Phone' => $nomorWa
            ]);

            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => $apiToken])
                ->post($apiUrl, [
                    'phone' => $nomorWa,
                    'message' => $pesan
                ]);            // Log response untuk debugging
            Log::info('WA Test Response:', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            // Cek response
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesan test berhasil dikirim!',
                    'details' => [
                        'nomor' => $nomorWa,
                        'response' => $response->json()
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim pesan test',
                    'error' => $response->body(),
                    'status' => $response->status()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('WA Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saat testing WA',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
