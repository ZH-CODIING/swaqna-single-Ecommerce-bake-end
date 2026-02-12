<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    protected $table = 'about_us'; // اسم الجدول

    protected $fillable = [
        'title',
        'description',
        'image',
        'goal',
        'mission',
        'vision',
    ];

    protected $primaryKey = 'id';   // حدد الـ primary key
    public $incrementing = true;    // id Auto Increment
    protected $keyType = 'int';     // نوع المفتاح
}
