<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $guarded = [''];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function farm()
    {
        return $this->hasMany(Farmer::class);
    }

    public function farmer()
    {
        return $this->hasMany(Farmer::class);
    }
}
