<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class whatsapp_limit extends Model
{
    protected $fillable = ['remaining_messages' , 'status'];

    protected $casts = [
     'status'=> 'boolean',
    ];

    public $timestamps = false;

}
