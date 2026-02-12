<?php

namespace App\Services\Shipping;

use App\Models\shipping_gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class FastloShippingService
{
    protected $baseUrl;
    protected $apiKey;
    public function __construct()
    {
        $this->baseUrl = 'https://fastlo.com/api/v1/';
        $this->apiKey = env('FASTLO_API_KEY');
    }

    protected function request($method, $endpoint, $body = [])
    {

        $response = Http::withHeaders([
            'fastlo-api-key' => $this->apiKey,
            //   'Content-Type' => 'application/json',
        ])->$method($this->baseUrl . $endpoint,  $body);

        return $response->json();
    }
    public function getShipmentPrices()
    {
        return $this->request(
            'post',
            'prices_shipment',
            [
                'request' =>
                [
                    'delivery' => 1,
                    'shipping' => 1,
                ]
            ]
        );
    }


    public function addShipment(array $data)
    {

        return $this->request('post', 'add_shipment', [
            'request' => $data
        ]);
    }


    public function readShipment(string $trackNumber)
    {
        $response = $this->request('post', 'read_shipment', [
            'request' => [
                'tracknumber' => $trackNumber
            ]
        ]);

        if (isset($response['output']['shipment_data']['status_code'])) {
            $response['output']['shipment_data']['status_text'] =
                $this->mapStatus($response['output']['shipment_data']['status_code']);
        }

        return $response;
    }


    public function getLabels(array $trackNumbers)
    {
        return $this->request('post', 'labels', [
            'request' => [
                'tracknumbers' => $trackNumbers,
                'pdf_format' => 'base64',
            ]
        ]);
    }
    public function canBeCanceled(string $trackNumber)
    {
        return $this->request('post', 'can_cancel_shipment', [
            'request' => ['tracknumber' => $trackNumber]
        ]);
    }


    public function cancelShipment(string $trackNumber)
    {
        return $this->request('post', 'cancel_shipment', [
            'request' => ['tracknumber' => $trackNumber]
        ]);
    }


    public function returnShipment(string $trackNumber)
    {
        return $this->request('post', 'return_shipment', [
            'request' => ['tracknumber' => $trackNumber]
        ]);
    }



    protected function mapStatus(int $code): string
    {
        return match ($code) {
            10 => 'New',
            20 => 'Pickup In Progress',
            30 => 'Picked Up',
            40 => 'In Distribution Center',
            50 => 'Shipping In Progress',
            60 => 'Delivery In Progress',
            65 => 'Return In Progress',
            70 => 'Canceled',
            80 => 'Returned',
            90 => 'Dispatched',
            100 => 'Delivered',
            default => 'Unknown',
        };
    }



}
