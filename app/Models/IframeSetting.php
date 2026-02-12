<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IframeSetting extends Model
{
       protected $fillable = [
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'whatsapp',
    ];
}

