<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Coordinator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'password'
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Relationship with tracking links
    public function trackingLinks()
    {
        return $this->hasMany(TrackingLink::class);
    }

        protected $hidden = [
        'password',
    ];



}
