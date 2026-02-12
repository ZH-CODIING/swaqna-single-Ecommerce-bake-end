<?php

namespace App\Jobs;

use App\Models\shipping_gate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendWaybillToMain //implements ShouldQueue
{
    use Dispatchable;


    protected $gate;
    protected $trader_email;
    protected $trader_phone;
    protected $order;
    protected $waybill_id;
    public function __construct($gate, $email, $phone, $order, $waybill_id)
    {
        $this->gate = $gate;
        $this->trader_email = $email;
        $this->trader_phone = $phone;
        $this->order = $order;
        $this->waybill_id = $waybill_id;
    }

    public function handle(): void
    {
        $payLoad =   [
            'shipment_price' => $this->order->platfourm_shipping_price,
            'shipment_gateway' => $this->gate,
            'trader_email' => $this->trader_email,
            'phone_number' => $this->trader_phone,
            'secret_code' => '$2y$12$0bL9MLj2QOT37iAqd96F/uF5Gz.j2./HXQoibBefPUus99dBk1GFK',
            'waybill_id' =>   $this->waybill_id
        ];
        $response =  Http::post(
            'http://api.souqna-sa.com/api/bolisat-al-shahn',
            $payLoad
        );

        if (!$response->successful()) {
            Log::info('Failed to post Wayball' . $payLoad);
        } else {
        }
    }
}
