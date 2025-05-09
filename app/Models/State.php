<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function lgas(){
        return $this->hasMany(Lga::class);
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }


}
