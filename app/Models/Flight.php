<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'airline_id',
        'departure_at',
        'arrival_at',
        'departure_city_id',
        'destination_city_id'
    ];

    protected $casts = [
        'departure_at' => 'datetime:Y-m-d H:i:s',
        'arrival_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function departureCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'departure_city_id');
    }

    public function destinationCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }
}
