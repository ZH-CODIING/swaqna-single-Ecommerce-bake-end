<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    protected $table = 'privacy_policy';  
    protected $fillable = ['description'];

    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
}
