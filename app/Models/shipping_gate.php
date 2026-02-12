<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shipping_gate extends Model
{
    protected $fillable = [
        'name',
        'note',
        'logo',
        'website',
        'price',
        'second_price',
        'trader_price',
        'trader_second_price',
        'city',
        'price',
        'status',
        'cod_charge',
        'kg_additional',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
