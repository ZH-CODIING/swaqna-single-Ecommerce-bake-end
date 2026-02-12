<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Order extends Model
{
    protected $fillable = ['user_id', 'total_price', 'status', 'discount_coupon_id', 'discount_amount', 'shipping_gate', 'address', 'qr_path', 'shippment_cost', 'platfourm_shipping_price' , 'tracking_link_id'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function coupon()
    {
        return $this->belongsTo(DiscountCoupon::class, 'discount_coupon_id');
    }
    // Realtion with order and shipping that ordar package
    public function ShippingInfo()
    {
        return $this->hasOne(OrderShippingInfo::class);
    }

    public function shippment()
    {
        return $this->hasOne(order_shipment::class);
    }

    public function payment()
    {
        return $this->hasOne(payment::class);
    }
    protected static function boot()
    {
        parent::boot();
        static::created(function ($order) {
            //Delete User Cart 
            
            CartItem::where('user_id', $order->user_id)->delete();
            $cacheKey = 'cart:' . $order->user_id;
            Cache::forget($cacheKey);


            //Create System  Notifcation 
            $data = [
                'title' => 'order_created',
                'body' => 'required|string',
                'phone_number' => 'required|string',
            ];

            SystemNotification::create($data);
        });
    }

    protected $hidden = [
        'platfourm_shipping_price',
    ];
}
