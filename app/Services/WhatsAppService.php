<?php

namespace App\Services;

use App\Models\whatsapp_limit;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{

    public static function  sendMessage($toPhone, $message,  $file = null, $filename = null)
    {
        $limit = whatsapp_limit::first();

        if ($limit->remaining_messages <= 0 || !$limit->status) {
            return;
        }
        $limit->decrement('remaining_messages');


        $token = env('HYPERSENDER_TOKEN');
        $instance = env('WHATSAPP_INSTANCE_ID');
        $baseUrl = env('WHATSAPPP_BASE_URL');


        try {
            if ($filename) {
                $url = "{$baseUrl}/{$instance}/send-image";
                $payload = [
                    'chatId' => $toPhone . '@c.us',
                    'caption' => $message,
                    'filename' => $filename,
                    'mimetype' => 'image/jpg'

                ];
                $response = Http::attach('file',  $file)->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'multipart/form-data',
                ])->post($url, $payload);
            } else {

                $url = "{$baseUrl}/{$instance}/send-text";
                $payload = [
                    'chatId' => $toPhone . '@c.us',
                    'text' => $message,

                ];
                $response = Http::withHeaders([
                    //    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);
            }
        } catch (\Throwable $th) {
            return 'unavailable service';
        }

        return $response->json();
    }
}
