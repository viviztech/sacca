<?php

namespace App\Support\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmService
{
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (! $user->fcm_token) {
            return false;
        }

        return $this->send($user->fcm_token, $title, $body, $data);
    }

    public function sendToMany(array $tokens, string $title, string $body, array $data = []): int
    {
        $sent = 0;
        foreach ($tokens as $token) {
            if ($this->send($token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function send(string $token, string $title, string $body, array $data = []): bool
    {
        $credentialsPath = config('services.firebase.credentials');

        if (! $credentialsPath || ! file_exists($credentialsPath)) {
            Log::debug('FCM skipped: no credentials configured');

            return false;
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $messaging = $factory->createMessaging();

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(['title' => $title, 'body' => $body])
                ->withData($data);

            $messaging->send($message);

            return true;
        } catch (\Throwable $e) {
            Log::warning('FCM send failed', ['token' => substr($token, 0, 20), 'error' => $e->getMessage()]);

            return false;
        }
    }
}
