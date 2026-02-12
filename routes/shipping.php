<?php

use Illuminate\Support\Facades\Route;

use App\Services\Shipping\RedBoxShippingService;
use App\Services\Shipping\FastloShippingService;
use App\Services\Shipping\NaqellShippingService;



Route::get('testAramex', function () {
  $shipmentData = [
    'Shipper' => [
      'AccountNumber' => env('ARAMEX_ACCOUNT_NUMBER'),
      'PartyAddress' => [
        'Line1'   => 'Mecca St',
        'City'    => 'Amman',
        'StateOrProvinceCode' => '',
        'PostCode' => '',
        'CountryCode' => 'SA'
      ],
      'Contact' => [
        'Department'     => '',
        'PersonName'     => 'Michael',
        'Title'          => '',
        'CompanyName'    => 'Aramex',
        'PhoneNumber1'   => '5555555',
        'PhoneNumber1Ext' => '125',
        'PhoneNumber2'   => '',
        'PhoneNumber2Ext' => '',
        'FaxNumber'      => '',
        'CellPhone'      => '07777777',
        'EmailAddress'   => 'michael@aramex.com',
        'Type'           => ''
      ],
    ],
    'Consignee' => [
      'Reference1'   => 'Ref 333333',
      'Reference2'   => 'Ref 444444',
      'AccountNumber' => '',
      'PartyAddress' => [
        'Line1'   => '15 ABC St',
        'Line2'   => '',
        'Line3'   => '',
        'City'    => 'Dubai',
        'StateOrProvinceCode' => '',
        'PostCode' => '',
        'CountryCode' => 'SA'
      ],
      'Contact' => [
        'Department'     => '',
        'PersonName'     => 'Mazen',
        'Title'          => '',
        'CompanyName'    => 'Aramex',
        'PhoneNumber1'   => '6666666',
        'PhoneNumber1Ext' => '155',
        'PhoneNumber2'   => '',
        'PhoneNumber2Ext' => '',
        'FaxNumber'      => '',
        'CellPhone'      => '',
        'EmailAddress'   => 'mazen@aramex.com',
        'Type'           => ''
      ],
    ],
    'ThirdParty' => [
      'AccountNumber' => '',
      'PartyAddress' => [
        'Line1'   => '',
        'City'    => '',
        'StateOrProvinceCode' => '',
        'PostCode' => '',
        'CountryCode' => 'SA'
      ],
      'Contact' => [
        'PersonName'     => '',
        'CompanyName'    => '',
        'PhoneNumber1'   => '',
        'CellPhone'      => '',
        'EmailAddress'   => '',
      ],
    ],
    'ForeignHAWB'         => 'ABC 000111',
    'ShippingDateTime'    => time(),
    'Details' => [
      'ActualWeight' => [
        'Value' => 0.5,
        'Unit'  => 'Kg'
      ],
      'ProductGroup'         => 'EXP',
      'ProductType'          => 'EPX',
      'PaymentType'          => 'P',
      'PaymentOptions'       => '',
      'NumberOfPieces'       => 1,
      'DescriptionOfGoods'   => 'Docs',
      'GoodsOriginCountry'   => 'SA',

      'CashOnDeliveryAmount' => [
        'Value' => 0,
        'CurrencyCode' => 'SAR'
      ],


      'CashAdditionalAmountDescription' => '',


      'Items' => [
        [
          'PackageType' => 'Box',
          'Quantity'    => 1,
          'Weight' => [
            'Value' => 0.5,
            'Unit'  => 'Kg',
          ],
          'Comments'  => 'Docs',

          'Reference' => ''
        ]
      ]
    ]
  ];
});









Route::get('naqel/get_by_date', function () {
  $naqel = new NaqellShippingService();
  $response = $naqel->getWaybillNoByDate(request('start_date'), request('end_date'));
  return response()->json($response);
});


Route::post('naqel/cancel', function () {
  $WaybillNo = request('WaybillNo');
  $naqelService = new NaqellShippingService();
  $response = $naqelService->cancelShipment([
    'ClientPhoneNumber' => '',
    'ClientAddress' => '',
    'ClientCountryCode' => '',
    'ClientCityCode' => '',
    'ClientName' => '',
    'ClientEmail' => '',
    'WaybillNo' =>  $WaybillNo,
    'CancelReason' => 'Customer cancelled order',
  ]);
  return response()->json($response);
});


Route::prefix('redbox')->group(function () {
  Route::get('/points-by-city', function (RedBoxShippingService $service) {
    return $service->getPointsByCity(request('city_code'), request('type'));
  });

  Route::get('/cities-by-country', function (RedBoxShippingService $service) {
    return $service->getCitiesByCountry(request('country_code'));
  });

  Route::get('/nearby-points', function (RedBoxShippingService $service) {
    return $service->searchNearbyPoints(request('lat'), request('lng'), request('radius'), request('type'));
  });

  Route::get('/point-details', function (RedBoxShippingService $service) {
    return $service->getPointDetails(request('point_id'));
  });

  Route::post('/create-shipment', function (RedBoxShippingService $service) {
    return $service->createShipment(request()->all());
  });

  Route::get('/shipment-details', function (RedBoxShippingService $service) {
    return $service->getShipmentDetails(request('id'));
  });

  Route::get('/shipment-activities', function (RedBoxShippingService $service) {
    return $service->getShipmentActivities(request('id'));
  });

  Route::post('/cancel-shipment', function (RedBoxShippingService $service) {
    return $service->cancelShipment(request('id'));
  });

  Route::get('/tracking-page', function (RedBoxShippingService $service) {
    return $service->getTrackingPage(request('id'));
  });

  Route::post('/create-pickup-location', function (RedBoxShippingService $service) {
    return $service->createPickupLocation(request()->all());
  });
  Route::post('/create-pickup-request', function (RedBoxShippingService $service) {
    return $service->createPickupLocationRequest(request()->all());
  });
});


Route::prefix('fastlo')->group(function () {
  Route::get('prices', function (FastloShippingService $fastlo) {

    return $fastlo->getShipmentPrices();
  });
  Route::post('add', function (FastloShippingService $fastlo) {
    $data = request('request');
    return $fastlo->addShipment($data);
  });
  Route::post('read', function (FastloShippingService $fastlo) {
    return $fastlo->readShipment(request('tracknumber'));
  });
  Route::post('labels', function (FastloShippingService $fastlo) {
    return $fastlo->getLabels(request('tracknumbers'));
  });
  Route::post('can_cancel', function (FastloShippingService $fastlo) {
    return $fastlo->canBeCanceled(request('tracknumber'));
  });
  Route::post('cancel', function (FastloShippingService $fastlo) {
    return $fastlo->cancelShipment(request('tracknumber'));
  });
  Route::post('return', function (FastloShippingService $fastlo) {
    return $fastlo->returnShipment(request('tracknumber'));
  });
});
