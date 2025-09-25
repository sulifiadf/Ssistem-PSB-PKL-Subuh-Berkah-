<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotifikasiPersetujuan
{
    protected string $token;
    protected string $url;
    protected string $adminPhone;

    public function __construct()
    {
        $this->token      = env('WHATSAPP_API_KEY');
        $this->url        = env('WHATSAPP_API_URL', 'https://app.wablas.com/api/send-message');
        $this->adminPhone = env('ADMIN_PHONE');
    }

    public function notifyAdmin(string $message): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
            'secret'        => env('WHATSAPP_WEBHOOK_SECRET'),
        ])->post($this->url, [
            'phone'   => $this->adminPhone,
            'message' => $message,
        ]);

        return $response->json(); // biar langsung return JSON
    }
}
