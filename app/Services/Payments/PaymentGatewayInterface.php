<?php

namespace App\Services\Payments;
use Illuminate\Http\Request;
interface PaymentGatewayInterface
{

    public function generatePayment(array $data): mixed;
    public function handleWebhook(Request $request): mixed;  //On success for now 


}
