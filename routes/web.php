<?php


use App\Services\shipping\NaqellShippingService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('test' , function(){
      $totalWeight = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.id', 1)
          ->sum(DB::raw('order_items.quantity * products.weight'));
          return $totalWeight;
});



Route::get('test-naqel-waybs', function () {

    $naqel = new \App\Services\shipping\NaqellShippingService();

    $response = $naqel->getWaybillNoByDate('2025-08-01T00:00:00', '2025-08-10T15:00:00');


    dd($response);
});


Route::get('test-naqel-cancel', function () {

    $WaybillNo = request('WaybillNo');
    $naqelService = new NaqellShippingService();
    $response = $naqelService->cancelShipment([
        'ClientPhoneNumber' => '0551234567',
        'ClientAddress' => 'Makkah (Mecca)	, Al Olaya Street',
        'ClientCountryCode' => 'KSA',
        'ClientCityCode' => 'MAC',
        'ClientName' => 'Test Store Co.',
        'ClientEmail' => 'store@example.com',
        'WaybillNo' =>  $WaybillNo,
        'CancelReason' => 'Customer cancelled order',
    ]);

    dd($response);


});


Route::get('test-naqel', function () {

    $data = [
        'ClientID' => env('NAQEL_CLIENT_ID'),
        'Password' => '8O!t@O7',
        'Version' => '9.0',
        'ClientPhoneNumber' => '0551234567',
        'ClientAddress' => 'Makkah (Mecca)	, Al Olaya Street',
        'ClientCountryCode' => 'KSA',
        'ClientCityCode' => 'MAC',
        'ClientName' => 'Test Store Co.',
        'ClientEmail' => 'store@example.com',

        'ConsigneeName' => 'Ahmed Ali',
        'ConsigneeEmail' => 'ahmed.ali@example.com',
        'ConsigneePhone' => '0567891234',
        'ConsigneeAddress' => 'Jeddah, Al Andalus District, Building 12',
        'ConsigneeCountryCode' => 'KSA',
        'ConsigneeCityCode' => 'MAC',

        'BillingType' => 5, // 5 = COD, 1 = Prepaid
        'PicesCount' => 3,
        'Weight' => 1.5, // in KG
        'CODCharge' => 250.00, // if COD selected
        'LoadTypeID' => 36, // Use the correct ID for your case
        'RefNo' => '#ord_1001',
        'GoodDesc' => '3x T-shirts, Size M',
        'InsuredValue' => 0,
        'PickUpPoint' => '',
        'CustomDutyAmount' => 0,
        'GoodsVATAmount' => 0,
        'IsCustomDutyPayByConsignee' => false,
        'CurrencyID' => 1, // Usually 1 = SAR
        'IsRTO' => false,
    ];
    $NaQelService = new NaqellShippingService();

    $res = $NaQelService->createShipmentWwaybail($data);

    return [
        'tracking_number' => $res->waybillNo ?? null,
        'reference' => $res->bookingRefNo ?? null
    ];
});









