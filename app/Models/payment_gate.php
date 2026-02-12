<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payment_gate extends Model
{
    protected $fillable = [
        'name',
        'note',
        'short_description',
        'logo',
        'faqs',
        'website',
        'status',
        'commission',
        'meta_data'
    ];

    protected $casts = [        
        'status' => 'boolean',
    ];
}
