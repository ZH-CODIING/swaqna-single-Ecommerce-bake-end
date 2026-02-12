<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class admin_token extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'facebook_token',
        'instagram_token',
        'youtube_token',
        'whatsapp_token',
        'google_token',
        'snapchat_token',
        'tiktok_token',
        'instagram_account_id'
    ];
}
