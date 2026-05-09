<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppClient
{
    public function sendTemplate(string $phone, string $templateName, array $components = []): bool
    {
        $baseUrl = config('services.whatsapp.url', 'http://localhost:8003/api');

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout(10)
                ->post('/send-template', [
                    'phone' => $phone,
                    'templateName' => $templateName,
                    'components' => $components,
                ]);

            if ($response->failed()) {
                Log::warning('WhatsApp send failed', [
                    'phone' => $phone,
                    'template' => $templateName,
                    'status' => $response->status(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp client exception', ['message' => $e->getMessage()]);

            return false;
        }
    }

    public function sendText(string $phone, string $message): bool
    {
        $baseUrl = config('services.whatsapp.url', 'http://localhost:8003/api');

        try {
            $response = Http::baseUrl($baseUrl)
                ->timeout(10)
                ->post('/send-message', [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('WhatsApp client exception', ['message' => $e->getMessage()]);

            return false;
        }
    }
}
