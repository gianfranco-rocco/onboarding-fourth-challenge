<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Airline extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description'
    ];

    public $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function activeFlights(): HasMany
    {
        return $this->flights()
                ->whereDate('departure_at', '<=', now())
                ->whereDate('arrival_at', '>=', now());
    }
}
