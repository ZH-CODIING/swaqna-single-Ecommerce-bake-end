<?php

namespace App\Models;

use App\Jobs\SendWaybillToMain;
use Illuminate\Database\Eloquent\Model;

class order_shipment extends Model
{
    protected $fillable = ['order_id', 'shipping_gateway', 'tracking_number',   'reference'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    protected static function boot()
    {
        parent::boot();
        static::created(function ($orderShipment) {
            $store_info = StoreInfo::first();
            $order = $orderShipment->order;
            $waybill_id = $orderShipment->tracking_number;
            SendWaybillToMain::dispatch($orderShipment->shipping_gateway, $store_info->email, $store_info->phone, $order, $waybill_id);

            if ($order->tracking_link_id) {
                $tracking =  TrackingLink::find($order->tracking_link_id);
                $tracking->increment('purchases_count', 1);
            }
        });
    }
}
