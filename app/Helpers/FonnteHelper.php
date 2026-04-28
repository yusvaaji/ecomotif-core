<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class FonnteHelper
{
    /**
     * Send WhatsApp message via Fonnte API.
     * Returns decoded Fonnte response array, or false on curl error.
     */
    public static function sendWhatsAppOTP($phone, $message)
    {
        $token = env('FONTE_TOKEN', '');

        if (empty($token)) {
            Log::warning('Fonnte: FONTE_TOKEN is not set in .env');
            return false;
        }

        // Normalize phone: strip non-digits, remove country code prefix
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($cleanPhone, 0, 2) === '62') {
            $cleanPhone = substr($cleanPhone, 2);
        } elseif (substr($cleanPhone, 0, 1) === '0') {
            $cleanPhone = substr($cleanPhone, 1);
        }

        // Fonnte requires full number with country code
        $target = '62' . $cleanPhone;

        Log::info("Fonnte: Sending OTP to target=$target");

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 15, // seconds
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => http_build_query([
                'target'  => $target,
                'message' => $message,
            ]),
            CURLOPT_HTTPHEADER     => [
                "Authorization: $token",
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);

        $response = curl_exec($curl);
        $curlErr  = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($curlErr) {
            Log::error("Fonnte cURL error for $target: $curlErr");
            return false;
        }

        $decoded = json_decode($response, true);
        Log::info("Fonnte response for $target [HTTP $httpCode]: " . $response);

        // Fonnte returns {"status":true,...} on success
        if (isset($decoded['status']) && $decoded['status'] === true) {
            return $decoded;
        }

        Log::warning("Fonnte failed for $target: " . $response);
        return false;
    }
}
