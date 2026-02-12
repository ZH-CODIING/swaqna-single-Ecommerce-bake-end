<?php

namespace App\Services\Shipping;

use SoapClient;
use SoapFault;
use Exception;

class AramexShippingService
{
    protected SoapClient $client;
    protected array $clientInfo;

    public function __construct()
    {
        $this->client = new SoapClient(env('ARAMEX_WSDL_PATH', public_path('app/aramexShipping.wsdl')), [
            'trace' => true
        ]);

        $this->clientInfo = [
            'AccountCountryCode' => env('ARAMEX_COUNTRY_CODE', 'JO'),
            'AccountEntity'      => env('ARAMEX_ENTITY', 'AMM'),
            'AccountNumber'      => env('ARAMEX_ACCOUNT_NUMBER', '20016'),
            'AccountPin'         => env('ARAMEX_ACCOUNT_PIN', '221321'),
            'UserName'           => env('ARAMEX_USERNAME', 'reem@reem.com'),
            'Password'           => env('ARAMEX_PASSWORD', '123456789'),
            'Version'            => env('ARAMEX_VERSION', '1.0'),
            'Source'             => 24,
        ];
    }

    /**
     * Create shipment in Aramex
     *
     * @param array $shipmentData
     * @return mixed
     * @throws Exception
     */
    public function createShipment(array $shipmentData)
    {
        $params = [
            'Shipments' => [
                'Shipment' => $shipmentData
            ],
            'ClientInfo'  => $this->clientInfo,
            'Transaction' => [
                'Reference1' => '001'
            ],
            'LabelInfo'   => [
                'ReportID'   => 9201,
                'ReportType' => 'URL',
            ],
        ];

        try {
            $response = $this->client->CreateShipments($params);

            if (!empty($response->HasErrors)) {
                throw new Exception('Aramex Error: ' . json_encode($response->Shipments->ProcessedShipment->Notifications));
            }

            return $response;
        } catch (SoapFault $fault) {
            throw new Exception('SOAP Fault: ' . $fault->getMessage());
        }
    }
}
