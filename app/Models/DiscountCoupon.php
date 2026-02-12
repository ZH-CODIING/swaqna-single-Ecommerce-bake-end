<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountCoupon extends Model
{
    protected $fillable = [
        'code',
        'discount',
        'active',
        'end_date',
    ];

    protected $casts = [
        'active' => 'boolean',
        'end_date' => 'datetime',
    ];

    public function orders()
{
    return $this->hasMany(Order::class, 'discount_coupon_id');
}

}
