<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\order_shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\Auth;
use App\Models\payment;
use Illuminate\Support\Facades\Cache;

class PaymentSettingsController extends Controller
{
    protected function checkAdmin()
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized: Admins only');
        }
    }

    public function status(request $request)
    {
        return response()->json([
            'payment_enabled' => env('PAYMENT_ENABLED'),
            'payment_gateway' => env('PAYMENT_GATEWAY'),
        ]);
    }


    public function enablePayment(Request $request)
    {


        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized ');
        }
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $envUpdates = [
            'PAYMENT_ENABLED' => 'true',
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);


        Artisan::call('config:clear');

        return response()->json([
            'message' => 'Payment has been enabled successfully',
        ]);
    }

    public function disablePayment(Request $request)
    {

        if (!$this->HasAccess($request->token)) {
            abort(401, 'unauthorized');
        }
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);

        $envUpdates = [
            'PAYMENT_ENABLED' => 'false',
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);


        return response()->json([
            'message' => 'Payment has been disabled successfully',
        ]);
    }

    public function UpdatePaymentGate(Request $request)
    {

        $this->checkAdmin();

        $request->validate([
            'PAYMENT_GATEWAY' => 'required|string|in:adfapay,paymob'
        ]);

        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);


        $envUpdates = [
            'PAYMENT_GATEWAY' => $request->PAYMENT_GATEWAY,
        ];

        foreach ($envUpdates as $key => $value) {
            if (strpos($str, "$key=") !== false) {
                $str = preg_replace("/^$key=.*/m", "$key=$value", $str);
            } else {
                $str .= "\n$key=$value";
            }

            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        file_put_contents($envFile, $str);
        return response()->json([
            'message' => 'Payment has been disabled successfully',
        ]);
    }


    public function HasAccess($token)
    {
        return $token == env('ACCESS_DASHBOARD_TOKEN') ? true : false;
    }

    public function HandlePaymentSuccess(request $request)
    {
        PaymentGatewayFactory::make()->handleWebhook($request);
    }



    public function GetPayments(Request $request)
    {
        $this->checkAdmin();

        $query = payment::query();



        // filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->with('order:id,shippment_cost')->latest()->paginate(30);

        return response()->json(['payments' => $payments], 200);
    }

    public function GetShippments(Request $request)
    {
        $this->checkAdmin();

        $query = order_shipment::query();

        // filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $shippments = $query->with('order:id,shippment_cost,status')->latest()->paginate(30);

        return response()->json(['shippments' => $shippments], 200);
    }

    public function GetOrdersStates()
    {
        $this->checkAdmin();


        $stats = Cache::remember('orders_dashboard_states', 60 * 10, function () {
            $stats = order::select('status', \DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');
            $totalOrderSuccessValue = Order::whereIn('status', ['success', 'shippment_created'])->sum('total_price');
            $stats['totalOrderSuccessValue'] =   $totalOrderSuccessValue;
            return   $stats;
        });


        return response()->json([
            'orders_stats' => [
                'shippment_created'  => $stats['shippment_created'] ?? 0,
                'failed' => $stats['failed'] ?? 0,
                'pending' => $stats['pending'] ?? 0,
            ],
            'total_success_order_value' => $stats['totalOrderSuccessValue'],
        ], 200);
    }
    public function GetPaymentsStates()
    {
        $this->checkAdmin();

        $stats = Cache::remember('dashboard_states', 60 * 10, function () {
            $stats = payment::select('status', \DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');
            $totalOrderSuccessValue = Order::where('status', 'success')->sum('total_price');

            $stats['totalOrderSuccessValue'] =   $totalOrderSuccessValue;
            return   $stats;
        });



        return response()->json([
            'payment_stats' => [
                'failed'  => $stats['failed'] ?? 0,
                'success' => $stats['success'] ?? 0,
                'pending' => $stats['pending'] ?? 0,
            ],
            'total_success_order_value' => $stats['totalOrderSuccessValue'],
        ], 200);
    }
}
