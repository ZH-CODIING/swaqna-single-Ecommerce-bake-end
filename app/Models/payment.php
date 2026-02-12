<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    protected $fillable = [
        'order_id' ,
        'user_id' ,
        'status' , 
        'gateway' , 
        'payment_value' ,
        
    ];
}
