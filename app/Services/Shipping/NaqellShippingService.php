<?php

namespace App\Services\Shipping;


use SoapClient;
use SoapFault;

class NaqellShippingService
{

  protected $naqel;
  protected $client;


  public function __construct() {}

  public function createShipment(array $data) {}



  public function createShipmentWwaybail(array $data)
  {



    $wsdl = env('NAQEL_ENDPOINT_API');

    // Create the SoapClient
    $client = new SoapClient($wsdl, [
      'trace' => true,
      'exceptions' => true,
    ]);


    // Build request array
    $request = [
      '_ManifestShipmentDetails' => [
        'ClientInfo' => [
          'ClientID'  => env('NAQEL_CLIENT_ID'),
          'Password'  => env('NAQEL_PASSWORD'),
          'Version'   => 9,
          'ClientAddress' => [
            'PhoneNumber' => $data['ClientPhoneNumber'],
            'FirstAddress' => $data['ClientAddress'],
            'CountryCode' => 'KSA',
            'CityCode' => $data['ClientCityCode'],
          ],
          'ClientContact' => [
            'Name' => $data['ClientName'],
            'Email' => $data['ClientEmail'],
            'PhoneNumber' => $data['ClientPhoneNumber'],
          ]
        ],
        'ConsigneeInfo' => [
          'ConsigneeName' => $data['ConsigneeName'],
          'Email' => $data['ConsigneeEmail'],
          'PhoneNumber' => $data['ConsigneePhone'],
          'Address' => $data['ConsigneeAddress'],
          'CountryCode' => $data['ConsigneeCountryCode'],
          'CityCode' => $data['ConsigneeCityCode'],
        ],
        'BillingType' => $data['BillingType'],
        'PicesCount' => $data['PicesCount'],
        'Weight' => 1,
        'DeliveryInstruction' => '',
        'CODCharge' => $data['CODCharge'],
        'LoadTypeID' => 36,
        'RefNo' => $data['RefNo'],
        'GoodDesc' => $data['GoodDesc'],
        'InsuredValue' => 0,
        'GeneratePiecesBarCodes' => true,
        'CreateBooking' => true,
        'PickUpPoint' => $data['PickUpPoint'],
        'CustomDutyAmount' => 0,
        'GoodsVATAmount' => 0,
        'IsCustomDutyPayByConsignee' => true,
        'CurrenyID' => 1,
        'IsRTO' => false,
      ]
    ];


    try {
      $response = $client->__soapCall("CreateWaybill", [$request]);
      if ($response->CreateWaybillResult->HasError) {
        abort(422, 'Please check Shippment Data');
      }
      return $response;
    } catch (SoapFault $fault) {
      return response()->json("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})");
    }
  }
  public function cancelShipment(array $data)
  {
    $wsdl = env('NAQEL_ENDPOINT_API');

    try {

      $client = new SoapClient($wsdl, [
        'trace' => true,
        'exceptions' => true,
      ]);

      $request = [
        '_clientInfo' => [
          'ClientAddress' => [
            'PhoneNumber'      => $data['ClientPhoneNumber'] ?? '',
            'NationalAddress'  => $data['ClientNationalAddress'] ?? '',
            'POBox'            => $data['ClientPOBox'] ?? '',
            'ZipCode'          => $data['ClientZipCode'] ?? '',
            'Fax'              => $data['ClientFax'] ?? '',
            'Latitude'         => $data['ClientLatitude'] ?? '',
            'Longitude'        => $data['ClientLongitude'] ?? '',
            'ShipperName'      => $data['ClientShipperName'] ?? '',
            'FirstAddress'     => $data['ClientFirstAddress'] ?? '',
            'Location'         => $data['ClientLocation'] ?? '',
            'CountryCode'      => $data['ClientCountryCode'] ?? '',
            'CityCode'         => $data['ClientCityCode'] ?? '',
          ],
          'ClientContact' => [
            'Name'         => $data['ClientContactName'] ?? '',
            'Email'        => $data['ClientContactEmail'] ?? '',
            'PhoneNumber'  => $data['ClientContactPhoneNumber'] ?? '',
            'MobileNo'     => $data['ClientContactMobileNo'] ?? '',
          ],
          'ClientID'  => env('NAQEL_CLIENT_ID'),
          'Password'  => env('NAQEL_PASSWORD'),
          'Version'   => 9,
        ],
        'WaybillNo'    => $data['WaybillNo'],
        'CancelReason' => $data['CancelReason'] ?? '',
      ];

      // Make the SOAP call
      $response = $client->__soapCall("CancelWaybill", [$request]);
      return $response;
    } catch (SoapFault $fault) {
      return response()->json([
        'error' => "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})",
      ], 500);
    }
  }
  public function getWaybillNoByDate(string $fromDate, string $toDate): mixed
  {
    $wsdl = env('NAQEL_ENDPOINT_API');

    try {
      $client = new SoapClient($wsdl, [
        'trace' => true,
        'exceptions' => true,
      ]);
      $request = [
        'ClientInfo' => [
          'ClientAddress' => [
            'PhoneNumber'      => $data['ClientPhoneNumber'] ?? '',
            'NationalAddress'  => $data['ClientNationalAddress'] ?? '',
            'POBox'            => $data['ClientPOBox'] ?? '',
            'ZipCode'          => $data['ClientZipCode'] ?? '',
            'Fax'              => $data['ClientFax'] ?? '',
            'Latitude'         => $data['ClientLatitude'] ?? '',
            'Longitude'        => $data['ClientLongitude'] ?? '',
            'ShipperName'      => $data['ClientShipperName'] ?? '',
            'FirstAddress'     => $data['ClientFirstAddress'] ?? '',
            'Location'         => $data['ClientLocation'] ?? '',
            'CountryCode'      => $data['ClientCountryCode'] ?? '',
            'CityCode'         => $data['ClientCityCode'] ?? '',
          ],
          'ClientContact' => [
            'Name'         => $data['ClientContactName'] ?? '',
            'Email'        => $data['ClientContactEmail'] ?? '',
            'PhoneNumber'  => $data['ClientContactPhoneNumber'] ?? '',
            'MobileNo'     => $data['ClientContactMobileNo'] ?? '',
          ],
          'ClientID'  => env('NAQEL_CLIENT_ID'),
          'Password'  => env('NAQEL_PASSWORD'),
          'Version'   => 9,
        ],
        'FromDatetime' => $fromDate,
        'ToDatetime'   => $toDate,
      ];

      // Make the SOAP call
      $response = $client->__soapCall("GetWaybillNoByDate", [$request]);

      return $response->GetWaybillNoByDateResult ?? $response;
    } catch (SoapFault $fault) {
      return response()->json([
        'error' => "SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})",
      ], 500);
    }
  }
}
