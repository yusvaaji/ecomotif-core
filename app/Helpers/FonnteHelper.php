<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class FonnteHelper
{
    /**
     * Send WhatsApp message via Fonnte API.
     *
     * Fonnte handles countryCode replacement:
     *   - countryCode=62 → replaces leading 0 with 62
     *   - So just pass the original local number (e.g. 08123456789)
     *
     * Returns decoded response array on success, false on curl failure.
     */
    public static function sendWhatsAppOTP($phone, $message)
    {
        $token = env('FONTE_TOKEN', '');

        if (empty($token)) {
            Log::warning('Fonnte: FONTE_TOKEN is not set in .env');
            return false;
        }

        // Pass phone as-is; Fonnte will replace leading 0 with country code 62.
        // Strip only whitespace/dashes so the format stays clean.
        $target = preg_replace('/[\s\-]/', '', $phone);

        Log::info("Fonnte: Sending OTP → target={$target}");

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => [
                'target'      => $target,
                'message'     => $message,
                'countryCode' => '62',
            ],
            CURLOPT_HTTPHEADER => [
                "Authorization: $token",
            ],
        ]);

        $response = curl_exec($curl);
        $curlErr  = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($curlErr) {
            Log::error("Fonnte cURL error for {$target}: {$curlErr}");
            return false;
        }

        $decoded = json_decode($response, true);
        Log::info("Fonnte response [{$target}] HTTP {$httpCode}: {$response}");

        // Fonnte returns {"status":true} on success
        if (!empty($decoded['status']) && $decoded['status'] === true) {
            return $decoded;
        }

        Log::warning("Fonnte failed [{$target}]: " . $response);
        return false;
    }
}
