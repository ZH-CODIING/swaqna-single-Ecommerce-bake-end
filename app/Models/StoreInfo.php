<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreInfo extends Model
{
    protected $table = 'store_info';

    protected $fillable = [
        'name',
        'description',
        'logo',
        'footer_text',
        'phone',
        'email',
        'address',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'whatsapp',
        'seo_description',
        'seo_keywords',
        'store_status',
        'subscription_package',
        'color',
        'start_subscription_date',
        'location_id' ,
        'city',
        'area',
        'street',
        'zip' , 
        'subscription_duration',
        'tax_number'
    ];
}
