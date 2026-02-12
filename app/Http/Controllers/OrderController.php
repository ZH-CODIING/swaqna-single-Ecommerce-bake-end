<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Jobs\MakeOrderShipmentJob;
use App\Models\User;
use App\Notifications\AdminOrderPlaced;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreInfo;
use App\Models\Product;
use App\Models\DiscountCoupon;
use App\Models\payment;
use App\Models\order_shipment;
use App\Models\shipping_gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use App\Services\Payments\PaymentGatewayFactory;
use App\Services\Shipping\GeneralShippingClass;
use App\Services\Shipping\RedBoxShippingService;

class OrderController extends Controller
{

     public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $user = Auth::user();
        $totalPrice = 0;
        foreach ($validated['products'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $totalPrice += $product->price * $item['quantity'];
        }
        $discountAmount = 0;
        $coupon = null;
        if (request()->has('coupon_code')) {
            $coupon = DiscountCoupon::where('code', $validated['coupon_code'])
                ->where('active', true)
                ->where('end_date', '>', now())
                ->first();
            if ($coupon) {
                $discountAmount = ($totalPrice * $coupon->discount) / 100;
            }
        }
        $tracking_link_id = null ;
        if (request()->has('tracking_link_id')) {
            $tracking_link_id = $request->tracking_link_id;
         
        }
        $buyer_address =  [
            'address' => $validated['address'],
            'city' => $validated['city'],
            'country' => 'Saudi Arabia',
            'area' => $validated['area'],
            'phone' => $validated['phone'],
        ];
        $order_price_not_taxed = $totalPrice - $discountAmount;
        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => round(($totalPrice - $discountAmount) * 1.15),
            'address' => json_encode($buyer_address),
            'discount_coupon_id' => $coupon?->id,
            'discount_amount' => $discountAmount,
            'status' => 'pending',
            'shipping_gate' => $validated['shipping_gate'],
            'shippment_cost' => 0,                          //0 For now will be updated ,
            'platfourm_shipping_price' => 0,                 //0 For now will be updated ,
            'tracking_link_id' => $tracking_link_id
        ]);

        //    $admin = user::where('role', 'admin')->first();
        //    $admin->notify(new AdminOrderPlaced($order->id));

        foreach ($validated['products'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'name' =>  $product->name ,
                'specs' =>  json_encode( $item['specs'] ?? []  )
            ]);
        }
        $this->ResetCache('user_orders_' . $user->id);
        $this->AddOrderQr($order , $order_price_not_taxed);   //Adding Qr code to order


        $shippmentCost = 0;
        $platfourm_shipping_price  = 0;

        $gate = shipping_gate::where('name', $validated['shipping_gate'])->first();
        if (!$gate) {
            abort(404, 'Gateway Doesnt exist');
        }
        $client_city = $gate->city;

        //Pre Shipping Calculate

        if ($validated['shipping_gate'] === 'fastlo') {
            $ShippingGate = new GeneralShippingClass($user, $order, 'fastlo', $buyer_address, $validated['cod'], $client_city);
            $CalcData =  $ShippingGate->CalculateShippmentForFastlo();
            $shippmentCost = $CalcData[0];
            $platfourm_shipping_price =  $CalcData[1];
        } elseif ($validated['shipping_gate'] === 'naqel') {
            //Another Logic for naqel
            $ShippingGate = new GeneralShippingClass($user, $order, 'naqel', $buyer_address, $validated['cod'], $client_city);
            $CalcData = $ShippingGate->CalculateShippmentForNaqel();
            $shippmentCost = $CalcData[0];
            $platfourm_shipping_price =  $CalcData[1];
        }
        $order->update(['shippment_cost' => $shippmentCost, 'platfourm_shipping_price' =>  $platfourm_shipping_price]);
        if (!$validated['cod']) {   //    Online Payment 
            $response = $this->InitPayment($user, $order, $shippmentCost, $validated);
            return response()->json([
                'message' => 'تم إنشاء الطلب بنجاح',
                'payment_url' => $response,
            ]);
        } else {
            MakeOrderShipmentJob::dispatch($order, $user, $buyer_address, $validated['shipping_gate'], $validated['cod'], $client_city);
            $order->status = 'shippment_created';
            $order->save();
            $this->DecreaseOrderItems($order->items);
            return response()->json([
                'message' => 'تم إنشاء الطلب بنجاح',
                'order'  => $order
            ]);
        }
    }
    
    protected function InitPayment($user, $order, $shippmentCost, $validated)
    {
        [$firstName, $lastName] = array_pad(explode(' ', trim($user->name), 2), 2, '');
        $lastName = $lastName ?: $user->name;
        $payment_data = [
            'order_id' => $order->id . '-' . Str::random(16),
            'order_amount' => $order->total_price + $shippmentCost,
            'order_description' => 'order Description',
            'payer_first_name'   => $firstName,
            'payer_last_name'    => $lastName,
            'payer_email'        => $user->email,
            'payer_mobile'       => $validated['phone'],
            'payer_ip_address'   => request()->ip(),
            'redirect_url'       => env('EDFA_PAY_RETURN_URL'),
            'test_mode'          => true,
            'zip_code'           => $validated['zip_code'],
            'city'               => $validated['city'],
            'returnUrl'          => $validated['returnUrl'],
        ];
        $response = PaymentGatewayFactory::make()->generatePayment($payment_data);
        payment::create([
            'user_id' => $user->id,
            'payment_value' => $order->total_price + $shippmentCost,
            'order_id' => $order->id,
            'gateway' =>  env('PAYMENT_GATEWAY'),
            'status'  => 'pending',
        ]);

        return $response;
    }
    protected function DecreaseOrderItems($items)
    {
        foreach ($items as $item) {
            $product = Product::find($item->product_id);

            if ($product) {
                $product->quantity -= $item->quantity;

                if ($product->quantity < 0) {
                    $product->quantity = 0;
                }
                $product->save();
            }
        }
    }
    protected function MakeOrderSippment($order, $user, $address,  $ShippingGate)
    {

        $GenRialShippingSerivce = new GeneralShippingClass($user, $order, $ShippingGate, $address, true);
        $resData =  $GenRialShippingSerivce->MakeUserShipment();

        $orderShippment =  order_shipment::create([
            'order_id' => $order->id,
            'tracking_number' => $resData['tracking_number'],
            'reference' => $resData['reference'],
            'shipping_gateway' => $ShippingGate
        ]);

        return $orderShippment;
    }
  protected function AddOrderQr($order , $order_price_not_taxed)
    {
        $tax = $order_price_not_taxed * 0.15;
        $store_info = StoreInfo::first();
        $qrContent = $this->generateZatcaQR(
            $store_info->name,
            $store_info->tax_number,
            now()->toIso8601String(),
            $order->total_price,
            $tax
        );
        $filename = 'qr_' . Str::uuid() . '.svg';
        Storage::disk('public')->put("qrcodes/{$filename}", QrCode::format('svg')->size(400)->generate($qrContent));
        $order->qr_path = "qrcodes/{$filename}";
        $order->save();
    }


    function generateZatcaQR($sellerName, $vatNumber, $invoiceDate, $total, $vat)
    {
        $tlv = $this->toTLV(1, $sellerName) .
            $this->toTLV(2, $vatNumber) .
            $this->toTLV(3, $invoiceDate) .
            $this->toTLV(4, number_format($total, 2, '.', '')) .
            $this->toTLV(5, number_format($vat, 2, '.', ''));

        return base64_encode($tlv);
    }

    protected function NotifyAdminForOrder($order_id)
    {
        $admin = User::where('role', 'admin')->first();
        $admin->notify(new AdminOrderPlaced($order_id));
    }
    public function toTLV($tag, $value)
    {
        $length = strlen($value);
        return chr($tag) . chr($length) . $value;
    }


    public function myOrders()
    {


        $user = Auth::user();
        $userId = $user->id;


        $orders = Cache::remember('user_orders_' . $userId, env('DEFAULT_CACHE_VALUE'), function () use ($user) {
            return $user->orders()
                ->with('items.product', 'coupon', 'ShippingInfo', 'user')
                ->latest()
                ->get();
        });

        return response()->json([
            'orders' => $orders,
        ]);
    }
    public function AdminOrders(request $request)
    {

        $this->checkAdmin();
        $query =  Order::query();
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders =  $query->with('items.product:id,name,img,price', 'user')->latest()->paginate(15);

        return response()->json([
            'orders' => $orders,
        ]);
    }
    public function show($id)
    {
        $order = Order::with('user', 'items.product', 'coupon')->findOrFail($id);

        return response()->json([
            'order' => $order,
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $request->validate([
            'address' => 'nullable|string',
            'status' => 'nullable|in:pending,processing,shipped,delivered,canceled',
            'products' => 'nullable|array|min:1',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'required_with:products|integer|min:1',
        ]);

        $order = Order::with('items')->findOrFail($id);

        if ($request->filled('address')) {
            $order->address = $request->address;
        }
        if ($request->filled('status')) {
            $order->status = $request->status;
        }
        if ($request->filled('products')) {
            $order->items()->delete();
            $totalPrice = 0;
            foreach ($request->products as $item) {
                $product = Product::findOrFail($item['product_id']);
                $totalPrice += $product->price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }


            $order->total_price = $totalPrice;
            $order->discount_amount = 0;
            $order->discount_coupon_id = null;
        }

        $order->save();

        return response()->json([
            'message' => 'تم تعديل الطلب بنجاح',
            'order' => $order->load('items.product', 'coupon'),
        ]);
    }

    public function UpdateStatus(request $request, $order_id)
    {

        $request->validate([
            'status' => 'required|in:pending,completed,canceled,returned,failed,shippment_created',
        ]);
        $order = Order::findOrFail($order_id);
        $order->update(['status' => $request->status]);
        Cache::forget('user_orders_' . $order_id);
        Cache::forget('user_orders_' . $order->user_id);

        return response()->json([
            'message' => 'تم تعديل حالة الطلب بنجاح',
            'order' => $order,
        ]);
    }

    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function GetLastOrder()
    {
        $user = Auth::user();
        $order = order::where('user_id', $user->id)->with('items.product')->latest()->first();
        $payment = $order->payment;
        $shipment = $order->shippment;
        //Get Tracking link 
        if ($order->status != 'failed') {
            if ($shipment->shipping_gateway == 'redbox') {
                $redbox = new RedBoxShippingService();
                $Res = $redbox->getTrackingPage($shipment->tracking_number);
            } elseif ($shipment->shipping_gateway == 'fastlo') {
                $Res = ['url_original' => "https://fastlo.com/track/" . $shipment->tracking_number];
            } else {
                // Naqel Shipping Handeling
                $Res = ['url_original' => null];
            }
        } else {
            $Res = false;
        }
        return response()->json([$order, $payment, $shipment, $Res],);
    }
}
