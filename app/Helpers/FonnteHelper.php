<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class FonnteHelper
{
    /**
     * Send WhatsApp message via Fonnte API.
     * Logs are written to laravel.log for debugging.
     */
    public static function sendWhatsAppOTP($phone, $message)
    {
        $token = env('FONTE_TOKEN', '');

        Log::channel('single')->info('[Fonnte] ====== START SEND OTP ======');
        Log::channel('single')->info('[Fonnte] Phone raw    : ' . $phone);
        Log::channel('single')->info('[Fonnte] Message      : ' . $message);
        Log::channel('single')->info('[Fonnte] Token exists : ' . (!empty($token) ? 'YES (len=' . strlen($token) . ')' : 'NO — FONTE_TOKEN is empty!'));
        if (!empty($token)) {
            $preview = substr($token, 0, 4) . str_repeat('*', max(0, strlen($token) - 8)) . substr($token, -4);
            Log::channel('single')->info('[Fonnte] Token preview: ' . $preview);
        }

        if (empty($token)) {
            Log::channel('single')->error('[Fonnte] ABORT — FONTE_TOKEN is not set in .env');
            return false;
        }

        // Pass phone as-is; Fonnte will replace leading 0 with country code 62.
        // Strip only whitespace/dashes so the format stays clean.
        $target = preg_replace('/[\s\-]/', '', $phone);

        Log::channel('single')->info('[Fonnte] Target (clean): ' . $target);
        Log::channel('single')->info('[Fonnte] Sending to    : https://api.fonnte.com/send');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
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
        $curlErrNo = curl_errno($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $totalTime = curl_getinfo($curl, CURLINFO_TOTAL_TIME);
        curl_close($curl);

        Log::channel('single')->info('[Fonnte] HTTP Code    : ' . $httpCode);
        Log::channel('single')->info('[Fonnte] Total Time   : ' . $totalTime . 's');

        if ($curlErr) {
            Log::channel('single')->error('[Fonnte] cURL Error No : ' . $curlErrNo);
            Log::channel('single')->error('[Fonnte] cURL Error    : ' . $curlErr);
            Log::channel('single')->info('[Fonnte] ====== END (FAILED - curl) ======');
            return false;
        }

        Log::channel('single')->info('[Fonnte] Raw Response : ' . $response);

        $decoded = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::channel('single')->error('[Fonnte] JSON decode error: ' . json_last_error_msg());
            Log::channel('single')->info('[Fonnte] ====== END (FAILED - json) ======');
            return false;
        }

        if (!empty($decoded['status']) && $decoded['status'] === true) {
            Log::channel('single')->info('[Fonnte] Result       : SUCCESS');
            Log::channel('single')->info('[Fonnte] Message ID   : ' . json_encode($decoded['id'] ?? null));
            Log::channel('single')->info('[Fonnte] Target ack   : ' . json_encode($decoded['target'] ?? null));
            Log::channel('single')->info('[Fonnte] ====== END (SUCCESS) ======');
            return $decoded;
        }

        Log::channel('single')->warning('[Fonnte] Result       : FAILED');
        Log::channel('single')->warning('[Fonnte] Reason       : ' . ($decoded['reason'] ?? 'unknown'));
        Log::channel('single')->warning('[Fonnte] Full decoded : ' . json_encode($decoded));
        Log::channel('single')->info('[Fonnte] ====== END (FAILED - api) ======');

        return false;
    }
}
