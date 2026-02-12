<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = ['resource_type', 'resource_id', 'visited_at'];
    public $timestamps = false;
}
