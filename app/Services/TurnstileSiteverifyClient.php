<?php

namespace App\Services;

class TurnstileSiteverifyClient
{
    /**
     * @return array{
     *     connected: bool,
     *     http_status: int,
     *     curl_errno: int,
     *     curl_error: string,
     *     body: array<string, mixed>|null
     * }
     */
    public function post(string $secretKey, string $token, ?string $ipAddress): array
    {
        $handle = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');

        if ($handle === false) {
            return [
                'connected' => false,
                'http_status' => 0,
                'curl_errno' => curl_errno(),
                'curl_error' => 'Unable to initialize the cURL handle.',
                'body' => null,
            ];
        }

        $formData = array_filter([
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $ipAddress,
        ], fn (?string $value): bool => $value !== null);

        $options = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($formData, '', '&', PHP_QUERY_RFC3986),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        $caBundle = config('services.turnstile.ca_bundle');

        if (is_string($caBundle) && $caBundle !== '') {
            $options[CURLOPT_CAINFO] = $caBundle;
        }

        curl_setopt_array($handle, $options);
        $responseBody = curl_exec($handle);
        $curlErrorNumber = curl_errno($handle);
        $curlError = curl_error($handle);
        $httpStatus = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $decodedBody = is_string($responseBody) ? json_decode($responseBody, true) : null;

        return [
            'connected' => $responseBody !== false,
            'http_status' => $httpStatus,
            'curl_errno' => $curlErrorNumber,
            'curl_error' => $curlError,
            'body' => is_array($decodedBody) ? $decodedBody : null,
        ];
    }
}
