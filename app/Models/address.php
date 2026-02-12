<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class address extends Model
{
   protected $fillable = ['address_line_1' , 'city' , 'country' , 'phone'];

}
