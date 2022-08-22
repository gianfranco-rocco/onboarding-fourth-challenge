<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowFlightResource extends JsonResource
{
    public function toArray($request): array
    {
        $departureCityId = $this->departureCity->id;
        $destinationCityId = $this->destinationCity->id;

        return [
            'id' => $this->id,
            'airline' => $this->airline->id,
            'departure_city' => $departureCityId,
            'destination_city' => $destinationCityId,
            'departure_at_date' => $this->departure_at->format('Y-m-d'),
            'departure_at_time' => $this->departure_at->format('H:i'),
            'arrival_at_date' => $this->arrival_at->format('Y-m-d'),
            'arrival_at_time' => $this->arrival_at->format('H:i'),
            'departure_cities' => $this->airline->cities,
            'destination_cities' => $this->airline->cities()->where('city_id', '!=', $departureCityId)->get(),
        ];
    }
}
