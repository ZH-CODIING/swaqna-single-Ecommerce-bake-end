<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnsPolicy extends Model
{
    protected $table = 'returns_policy';
    protected $fillable = ['description'];

    protected $primaryKey = null;

    public $incrementing = false; 
}
