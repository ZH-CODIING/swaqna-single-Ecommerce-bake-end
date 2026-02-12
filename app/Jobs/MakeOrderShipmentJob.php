<?php

namespace App\Jobs;

use App\Models\order_shipment;
use App\Services\Shipping\GeneralShippingClass;


use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MakeOrderShipmentJob
{
    use Dispatchable, SerializesModels;

    protected $order;
    protected $user;
    protected $address;
    protected $shippingGate;
    protected $cod;
    protected $client_city;

    public function __construct($order, $user, $address, string $shippingGate, $cod, $client_city)
    {
        $this->order = $order;
        $this->user = $user;
        $this->address = $address;
        $this->shippingGate = $shippingGate;
        $this->cod = $cod;
        $this->client_city = $client_city;
    }

    public function handle()
    {


        $GenRialShippingSerivce = new GeneralShippingClass(
            $this->user,
            $this->order,
            $this->shippingGate,
            $this->address,
            $this->cod,
            $this->client_city
        );
        $resData = $GenRialShippingSerivce->MakeUserShipment();
        order_shipment::create([
            'order_id' => $this->order->id,
            'tracking_number' => $resData['tracking_number'],
            'reference' => $resData['reference'],
            'shipping_gateway' => $this->shippingGate,

        ]);


    }
}
