<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OfferBanner extends Model
{
    protected $fillable = [
        'img',
        'title',
        'description',
        'category_id',
        'product_id',
        'end_date',
        'discount',

     
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

  
}
