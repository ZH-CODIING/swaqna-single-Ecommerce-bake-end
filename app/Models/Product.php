<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'brand_id',
        'img',
        'images',
        'name',
        'name_en',
        'code',
        'price',
        'description',
        'description_en',
        'discount',
        'specs',
        'quantity',
        'isFeatured',
        'discount_end_date',
        'rating',
        'seo_keywords',
        'seo_description',
        'visits',
        'weight'
    ];

    protected $casts = [
        'images' => 'array',
        'specs' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
