<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class FonnteHelper
{
    public static function sendWhatsAppOTP($phone, $message)
    {
        $token = env('FONTE_TOKEN', 'KEFQdXcEYZR8NY7jvFTm');
        
        // Clean phone number (remove + and leading 0 if needed, though Fonnte often handles it if countryCode is set)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($cleanPhone, 0, 2) == '62') {
            $cleanPhone = substr($cleanPhone, 2);
        } elseif (substr($cleanPhone, 0, 1) == '0') {
            $cleanPhone = substr($cleanPhone, 1);
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $cleanPhone,
                'message' => $message,
                'countryCode' => '62', // Important: Fonnte automatically prepends 62 if this is set
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: $token"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error("Fonnte Error: " . $err);
            return false;
        }

        Log::info("Fonnte Send OTP Response for $phone : " . $response);
        return true;
    }
}
