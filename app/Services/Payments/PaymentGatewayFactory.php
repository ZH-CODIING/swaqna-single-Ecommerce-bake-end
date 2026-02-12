<?php 
namespace App\Services\Payments;

use App\Services\Payments\Gateways\AdfapayGateway;


class PaymentGatewayFactory
{
    public static function make(): PaymentGatewayInterface
    {   

        return match (config('app.payment_gateway')) {
            'adfapay' => new AdfapayGateway(),
            default => throw new \Exception('Unsupported payment gateway'),
        };
    }

    
}
