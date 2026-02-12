<?php

namespace App\Services\Shipping;

use App\Models\StoreInfo;
use App\Services\Shipping\FastloShippingService;
use App\Services\Shipping\RedBoxShippingService;
use InvalidArgumentException;
use App\Models\shipping_gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeneralShippingClass
{
    protected $user;
    protected $order;
    protected $items;
    protected $shippingGateway;
    protected $address;
    protected $codOption;
    protected $client_city;
    public function __construct($user, $order, $shippingGateway, $address, $codOption, $client_city)
    {
        if (is_null($user)) {
            throw new InvalidArgumentException('User cannot be null');
        }
        if (is_null($order)) {
            throw new InvalidArgumentException('Order cannot be null');
        }
        if (is_null($shippingGateway)) {
            throw new InvalidArgumentException('Shipping gateway cannot be null');
        }
        if (is_null($address)) {
            throw new InvalidArgumentException('Address cannot be null');
        }
        if (is_null($codOption)) {
            throw new InvalidArgumentException('COD option cannot be null');
        }
        if (is_null($client_city)) {
            throw new InvalidArgumentException('client city cannot be null');
        }
        $this->user = $user;
        $this->order = $order;
        $this->items = $order->items;
        $this->shippingGateway = $shippingGateway;
        $this->address = $address;
        $this->codOption = $codOption;
        $this->client_city = $client_city;
    }
    public function MakeUserShipment()
    {
        try {
            $store_info = StoreInfo::first();
            switch ($this->shippingGateway) {
                case 'fastlo':
                    $DataRequest = [
                        "sender_address" => [
                            "sender_name" => $store_info->name,
                            "sender_mobile1" =>  $store_info->phone,
                            "sender_mobile2" => "",
                            "sender_country" => "SA",
                            "sender_city" =>  " $this->client_city",
                            "sender_area" => $this->address['area'],
                            "sender_street" => $store_info->street,
                        ],
                        "receiver_address" => [
                            "receiver_name" => $this->user->name,
                            "receiver_mobile1" => $this->address['phone'],
                            "receiver_mobile2" => '',
                            "receiver_country" => "SA",
                            "receiver_city" =>  $this->address['city'],
                            "receiver_area" => $this->address['area'],
                            "receiver_street" => "",
                        ],
                        "shipment_data" => [
                            "collect_cash_amount" => $this->codOption ?  ($this->order->total_price + $this->order->shippment_cost) : 0,
                            "number_of_pieces" => $this->GetNumberOfpices($this->order->items),
                            "content_description" => "",
                            "reference" => "order-" . $this->order->id,
                            "refrigerated" => false,
                            "packaging" => false,           //Remember this Option is Important 
                            "weight_kg" => $this->GetOrderwehgit(),            // And this option
                            //  "mode" => "testing"             //Remove this on porduction
                        ]
                    ];

                    $fastloService = new FastloShippingService();
                    $resData =  $fastloService->addShipment($DataRequest);
                    
                    $this->SendConfirmedMessage($this->order,  $resData['output']['tracknumber']);
                    return ['tracking_number' => $resData['output']['tracknumber'], 'reference' => $resData['output']['writecode']];
                    break;
                case 'redbox':
                    $items = [];
                    foreach ($this->order->items as $item) {
                        $items[] = [
                            'currency' => 'SAR',
                            'description' => '',
                            'name'  =>  $item->product->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->price
                        ];
                    }
                    $data = [
                        "reference" => '#ord_' . $this->order->id,
                        "cod_amount" => $this->codOption ?  ($this->order->total_price + $this->order->shippment_cost) : 0,
                        "cod_currency" => "SAR",
                        "customer_name" => $this->user->name,
                        "customer_phone" => $this->address['phone'],
                        "customer_address" => $this->address['address'],
                        "customer_city" => $this->address,
                        "customer_country" => "SA",
                        "customer_email" => $this->user->email,
                        "from_platform" => "ASWAQNA",
                        "pickup_location_id" => $this->GetLocationId(),
                        "items" => $items
                    ];

                    $Redbox = new RedBoxShippingService();
                    $resData = $Redbox->createShipment($data);
                    $this->SendConfirmedMessage($this->order,  $resData['tracking_number']);
                    return ['tracking_number' => $resData['tracking_number'] ?? null, 'reference' => $resData['shipment_id']];
                    break;
                case "naqel":
                    $data = [
                        'ClientPhoneNumber' => $store_info->phone,
                        'ClientAddress' => $store_info->address,
                        'ClientCityCode' =>  $this->client_city,
                        'ClientName' => $store_info->name,
                        'ClientEmail' => $store_info->email,
                        'ConsigneeName' => $this->user->name,
                        'ConsigneeEmail' => $this->user->email,
                        'ConsigneePhone' => $this->address['phone'],
                        'ConsigneeAddress' => $this->address['address'],
                        'ConsigneeCountryCode' => 'KSA',
                        'ConsigneeCityCode' => $this->address['city'],
                        //City code will be needed
                        'BillingType' => $this->codOption ?  5 : 1,
                        'PicesCount' => $this->GetNumberOfpices($this->order->items),
                        'CODCharge' => $this->codOption ? ($this->order->total_price + $this->order->shippment_cost) : 0,
                        'RefNo' => '#ord_' . $this->order->id,
                        'GoodDesc' => $this->order->items[0]->name,
                        'PickUpPoint' =>   $store_info->address,
                    ];
                    $NaQelService = new NaqellShippingService();
                    $res = $NaQelService->createShipmentWwaybail($data);
                    return ['tracking_number' => $res->CreateWaybillResult->WaybillNo ?? null, 'reference' => $res->CreateWaybillResult->BookingRefNo ?? null];
                default:
                    break;
            }
        } catch (\Throwable $th) {
            abort(500, 'There is proplem with shippment Please check Shipment data : ' . $th);
        }
    }
    protected function GetNumberOfpices()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->quantity;
        }
        return $total;
    }
    protected function GetLocationId()
    {
        return StoreInfo::first()->location_id;
    }
    protected function SendConfirmedMessage($order, $trackingUrl)
    {
        $message = "📦 تم تأكيد طلبك بنجاح!\n\n"
            . "🔹 رقم الطلب: #{$order->id}\n"
            . "💰 إجمالي السعر: {$order->total_price} ريال\n"
            . "🕒 التاريخ: {$order->created_at->format('Y-m-d H:i')}\n\n"
            . "لمتابعة حالة شحنتك:\n"
            . "{$trackingUrl}\n\n"
            . "شكراً لتسوقك معنا 🌟";
        return dispatch(new \App\Jobs\SendWhatsAppMessage($this->address['phone'], $message));
    }

    public function CalculateShippmentForFastlo()  //For Fastlo 
    {

        $city =  Str::lower(trim($this->address['city']));
        $shipping_price = 0;
        $fastlo = shipping_gate::where('name', 'fastlo')->first();

        $CalculateShippmentForFastlo = 0;

        if (!$fastlo) {
            abort(500, 'Shippment Doesnt Selected from admin');
        }

        $diff_price = 0;   //Diffrant price between Platforum And trader
        //Culcluting City Logic 
        $exists = $this->doesCityExist($city);
        if ($exists || ($city === $fastlo->city)) {
            $shipping_price += $fastlo->trader_price;
            $diff_price =  $fastlo->trader_price - $fastlo->price;
        } else {
            $shipping_price += $fastlo->trader_second_price;
            $diff_price =  $fastlo->trader_second_price - $fastlo->second_price;
        }

        //Wegiht 
        $wehgit = $this->GetOrderwehgit();
        $wehgit_price = ($wehgit > 5) ? ((round($wehgit) - 5) * $fastlo->kg_additional) : 0;
        $shipping_price += $wehgit_price;

        //Cod Charge
        if ($this->codOption) {
            $shipping_price += ($shipping_price * $fastlo->cod_charge / 100);
        }

        //Calculating Shipping Price for the Platforum 
        $platforum_shipping_price =  $shipping_price  -  $diff_price;

        return [$shipping_price, $platforum_shipping_price];
    }
    public function CalculateShippmentForNaqel()  //For Naqel 
    {
        $platforum_shipping_price = 0;
        $deff_price = 0;
        $shipping_price = 0;
        $naqel = shipping_gate::where('name', 'naqel')->first();
        $shipping_price += $naqel->trader_price;


        //Wegiht 
        $wehgit = $this->GetOrderwehgit();

        $wehgit_price = ($wehgit > 3) ? ((round($wehgit) - 3) * $naqel->kg_additional) : 0;
        $shipping_price += $wehgit_price;

        if ($this->codOption) {
            $shipping_price +=   $naqel->cod_charge;
        }

        $deff_price  = abs($naqel->trader_price - $naqel->price);

        $platforum_shipping_price =   abs($shipping_price -  $deff_price);
        $shipping_price =  Round($shipping_price * 1.15);


        return [$shipping_price, $platforum_shipping_price];
    }


    protected function doesCityExist(string $cityName): bool
    {
        $allowedCities = [
            'abha',
            'abuarish',
            'ahadalmasarihah',
            'ahadrafidah',
            'alahsa',
            'alasyah',
            'alayun',
            'aljafr',
            'aljouf',
            'alkhutamah',
            'anak',
            'aqiq',
            'artawiyah',
            'asfan',
            'badaya',
            'baha',
            'bahrah',
            'baish',
            'baljurashi',
            'bellasmar',
            'billahmar',
            'bukayriyah',
            'buqayq',
            'buraydah',
            'dahaban',
            'dammam',
            'darb',
            'dawmataljandal',
            'dhahran',
            'dhahranaljanoub',
            'dhamad',
            'dhurma',
            'dilam',
            'ghat',
            'hafaralbaten',
            'hail',
            'hanakiyah',
            'hawtatbanitamim',
            'hawtatsudayr',
            'hufuf',
            'huraymila',
            'jamoum',
            'jazan',
            'jeddah',
            'jubail',
            'khabra',
            'khafji',
            'khamismushayt',
            'kharj',
            'khobar',
            'khulais',
            'kingabdullaheconomiccity',
            'kingkhalidmilitarycity',
            'madinah',
            'majmaah',
            'makkah',
            'mandaq',
            'midhnab',
            'mobarraz',
            'muhayil',
            'muzahimiyah',
            'najran',
            'nammas',
            'onaizah',
            'qatif',
            'qurayyat',
            'qunfudhah',
            'rabigh',
            'rafha',
            'ranyah',
            'rass',
            'riyadh',
            'sabya',
            'safwa',
            'sakaka',
            'samtah',
            'shaqra',
            'sharurah',
            'tabuk',
            'taif',
            'tarut',
            'turaif',
            'tathlith',
            'unayzah',
            'uqayr',
            'uyunaljiwa',
            'wadiad-dawasir',
            'yanbu',
            'zulfi',
        ];

        $sanitizedCityName = Str::lower(str_replace(' ', '', $cityName));
        return in_array($sanitizedCityName, $allowedCities);
    }

    protected function GetOrderwehgit()
    {
        $orderId = $this->order->id;
        return  (int)DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.id', $orderId)
            ->sum(DB::raw('order_items.quantity * products.weight'));
    }
}
