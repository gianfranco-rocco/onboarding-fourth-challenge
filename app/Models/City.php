<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'name'
    ];

    public function incomingFlights(): HasMany
    {
        return $this->hasMany(Flight::class, 'destination_city_id');
    }

    public function outgoingFlights(): HasMany
    {
        return $this->hasMany(Flight::class, 'departure_city_id');
    }
}
