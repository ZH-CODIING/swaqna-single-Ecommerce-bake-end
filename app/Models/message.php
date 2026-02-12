<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class message extends Model
{
    protected $fillable = [
        'content',
        'sender_type',
        'chat_id',
    ];


}
