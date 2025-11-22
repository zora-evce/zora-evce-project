<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class QontakService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('qontak.base_url');
    }

    /**
     * Ambil access token dari cache atau refresh jika expired
     */
    private function getAccessToken()
    {
        if (Cache::has('qontak_access_token')) {
            return Cache::get('qontak_access_token');
        }

        return $this->refreshAccessToken();
    }

    /**
     * Refresh token
     */
    public function refreshAccessToken()
    {
        $response = Http::post($this->baseUrl . '/oauth/token', [
            'username' => env('QONTAK_USERNAME'),
            'password' => env('QONTAK_PASSWORD'),
            'grant_type' => 'password',
            'client_id' => env('QONTAK_CLIENT_ID'),
            'client_secret' => env('QONTAK_CLIENT_SECRET')
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gagal refresh token Qontak');
        }

        $data = $response->json();

        // Simpan access token ke cache 55 menit
        Cache::put('qontak_access_token', $data['access_token'], 55 * 60);

        return $data['access_token'];
    }

    /**
     * Kirim WhatsApp template
     */
    public function sendWhatsApp($phone, $data)
    {
        $token = $this->getAccessToken();

        $payload = [
            "to_number" => $phone,
            "to_name"   => $data['name'] ?? "Customer",
            "message_template_id" => config('qontak.template_id'),
            "channel_integration_id" => config('qontak.waba_number'),
            "language" => [
                "code" => "en"
            ],
            "parameters" => [
                "body" => [
                    [
                        "key"   => 1,
                        "value" => $data['name'],
                    ],
                    [
                        "key"   => 2,
                        "value" => $data['duration'],
                    ],
                    [
                        "key"   => 3,
                        "value" => $data['price'],
                    ],
                    [
                        "key"   => 4,
                        "value" => $data['total_price'],
                    ],
                    [
                        "key"   => 5,
                        "value" => $data['tax'],
                    ],
                    [
                        "key"   => 6,
                        "value" => $data['price_after_tax'],
                    ],
                ]
            ]
        ];

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/api/open/v1/broadcasts/whatsapp/direct', $payload);

        // Token expired → refresh lalu retry
        if ($response->status() == 401) {
            $token = $this->refreshAccessToken();

            $response = Http::withToken($token)
                ->post($this->baseUrl . '/api/open/v1/broadcasts/whatsapp/direct', $payload);
        }

        return $response->json();
    }

}