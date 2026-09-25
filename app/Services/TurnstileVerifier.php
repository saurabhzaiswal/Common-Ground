<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TurnstileVerifier
{
    public function __construct(private readonly TurnstileSiteverifyClient $siteverifyClient) {}

    public function verify(string $token, ?string $ipAddress, string $expectedAction): bool
    {
        $secretKey = config('services.turnstile.secret_key');

        if (! is_string($secretKey) || $secretKey === '' || $token === '') {
            Log::warning('Cloudflare Turnstile verification is missing its secret key or response token.', [
                'action' => $expectedAction,
                'secret_configured' => is_string($secretKey) && $secretKey !== '',
                'token_present' => $token !== '',
            ]);

            return false;
        }

        $verification = $this->siteverifyClient->post($secretKey, $token, $ipAddress);
        $body = $verification['body'];

        if (! $verification['connected']) {
            Log::warning('Cloudflare Turnstile Siteverify request failed to connect.', [
                'action' => $expectedAction,
                'http_status' => $verification['http_status'],
                'curl_errno' => $verification['curl_errno'],
                'reason' => $verification['curl_error'],
            ]);

            return false;
        }

        $verified = $verification['http_status'] >= 200
            && $verification['http_status'] < 300
            && ($body['success'] ?? false) === true;
        $actualAction = $body['action'] ?? null;

        if (! $verified || $actualAction !== $expectedAction) {
            Log::warning('Cloudflare Turnstile rejected a Siteverify response.', [
                'http_status' => $verification['http_status'],
                'success' => $body['success'] ?? false,
                'expected_action' => $expectedAction,
                'actual_action' => $actualAction,
                'hostname' => $body['hostname'] ?? null,
                'error_codes' => $body['error-codes'] ?? [],
            ]);

            return false;
        }

        return true;
    }
}
