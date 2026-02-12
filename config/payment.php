<?php

return [

    'enabled' => env('PAYMENT_ENABLED', false),


    'provider' => env('PAYMENT_PROVIDER', null),
    'merchant_id' => env('PAYMENT_MERCHANT_ID', null),
    'merchant_key' => env('PAYMENT_MERCHANT_KEY', null),
    'currency' => env('PAYMENT_CURRENCY', 'EGP'),
    'callback_url' => env('PAYMENT_CALLBACK_URL', null),


];