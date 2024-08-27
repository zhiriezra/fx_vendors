<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $guarded = [''];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class);
    }

    public function farmFarmingSeasons()
    {
        return $this->hasMany(FarmFarmingSeason::class);
    }

    public function farmVisitations()
    {
        return $this->hasMany(FarmVisitation::class);
    }

}
