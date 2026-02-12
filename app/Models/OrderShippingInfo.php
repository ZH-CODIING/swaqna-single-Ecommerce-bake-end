<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderShippingInfo extends Model
{
    protected $fillable = ['order_id' , 'tracking_package_id' , 'package_receive_date' , 'shipping_cost_value'  ];
}

