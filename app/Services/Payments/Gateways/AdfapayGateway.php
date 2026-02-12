<?php

namespace App\Services\Payments\Gateways;

use App\Models\Order;
use App\Models\order_shipment;
use App\Models\payment;
use App\Models\Product;
use App\Models\TrackingLink;
use App\Models\User;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Shipping\GeneralShippingClass;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdfapayGateway implements PaymentGatewayInterface
{
    public function generatePayment(array $data): mixed
    {

        $password = env('EDFA_PAY_PASSWORD');
        $merchantKey = env('EDFA_PAY_MERCHANT_KEY');
        $returnUrl =  $data['returnUrl'];

        // Generate hash
        $hashInput = strtoupper($data['order_id'] . $data['order_amount'] . 'SAR' . $data['order_description'] . $password);
        $hash = sha1(md5($hashInput));

        // Prepare payload
        $payload = [
            'action'             => 'SALE',
            'edfa_merchant_id'   => $merchantKey,
            'order_id'           => $data['order_id'],
            'order_amount'       => $data['order_amount'],
            'order_currency'     => 'SAR',
            'order_description'  => $data['order_description'],
            'payer_first_name'   => $data['payer_first_name'],
            'payer_last_name'    => $data['payer_last_name'],
            'payer_email'        => $data['payer_email'],
            'payer_phone'        => $data['payer_mobile'],
            'payer_ip'           => $data['payer_ip_address'],
            'term_url_3ds'       => $returnUrl,
            'hash'               => $hash,
            'req_token'          => 'N',
            'recurring_init'     => 'N',
            'payer_country'      => 'SA',
            'payer_city'         => $data['city'],
            'payer_zip'          => $data['zip_code'],
            'auth'               => 'N'
        ];

        $response = Http::asForm()->post('https://api.edfapay.com/payment/initiate', $payload);

        if ($response->successful()) {
            return $response->json('redirect_url');
        }

        return null;
    }

    public function handleWebhook($request): mixed
    {


        $status = $request->input('result');
        Log::info($request->all());
        if ($status === 'DECLINED') {
            $orderIdFull = $request->input('order_id');
            list($realOrderId, $randomPart) = explode('-', $orderIdFull, 2);

            $payment = payment::where('order_id', $realOrderId)->first();
            $payment->status = 'failed';
            $payment->save();
            $order = Order::find($payment->order_id);
            $order->status = 'failed';
            $order->save();
            abort(404);
        }
        if ($status === 'SUCCESS') {
            Log::info($request->all());
            $orderIdFull = $request->input('order_id');
            list($realOrderId, $randomPart) = explode('-', $orderIdFull, 2);
            $payment = payment::where('order_id', $realOrderId)->first();

            if ($payment) {
                $payment->status = 'success';
                $payment->save();
                $user = User::find($payment->user_id);
                $order = Order::find($payment->order_id);
                $order->status = 'paid';
                $order->save();

                //Handle Shippment lOGIC -> After Payment Success
                $buyer_address = json_decode($order->address, true);
                $GenRialShippingSerivce = new GeneralShippingClass($user, $order, $order->shipping_gate, $buyer_address, false, $buyer_address['city']);
                $resData =  $GenRialShippingSerivce->MakeUserShipment();
                order_shipment::create([
                    'order_id' => $order->id,
                    'tracking_number' => $resData['tracking_number'],
                    'reference' => $resData['reference'],
                    'shipping_gateway' => $order->shipping_gate,
                    'price_type'       => $order->shipping_price_type
                ]);
                $order->status = 'shippment_created';
                $order->save();

                //Decrese order items 
                foreach ($order->items as $item) {
                    $product = Product::find($item->product_id);

                    if ($product) {
                        $product->quantity -= $item->quantity;

                        if ($product->quantity < 0) {
                            $product->quantity = 0;
                        }

                        $product->save();
                    }
                }

                //Add to user points 
                $totalSAR = $order->total_price;
                $points = floor($totalSAR / 100);
                $user->increment('points', $points);

                //Handle Tracking link 
                if ($order->tracking_link_id) {
                    $tracking =  TrackingLink::find($order->tracking_link_id);
                    $tracking->increment('purchases_count', 1);
                }

                return response()->json(['status' => 'WebHook Accepted'], 200);
            } else {

                return response()->json([
                    'error' => 'Invalid payment reference.'
                ], 404);
            }
        }
        return response()->json([
            'error' => 'Invalid payment reference.'
        ], 404);
    }
}
