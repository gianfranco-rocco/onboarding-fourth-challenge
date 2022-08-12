<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class City extends Model
{
    use HasFactory, SoftDeletes;

    private const CACHED_KEYS_KEY = 'city_cached_keys';
    
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

    public static function addCacheKeyToCachedKeys(string $key): void
    {
        $cachedKeys = self::getCachedKeys();

        if (!in_array($key, $cachedKeys)) {
            $cachedKeys[] = $key;

            Cache::put(self::CACHED_KEYS_KEY, $cachedKeys);
        }
    }

    public static function clearCachedKeys(): void
    {
        $cachedKeys = self::getCachedKeys();

        foreach($cachedKeys as $key) {
            Cache::forget($key);
        }
    }

    private static function getCachedKeys(): array
    {
        return Cache::get(self::CACHED_KEYS_KEY, []);
    }
}
