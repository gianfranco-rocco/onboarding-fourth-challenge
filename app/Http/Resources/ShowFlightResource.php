<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShowFlightResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'airline' => $this->airline,
            'departure_city' => $this->departureCity,
            'destination_city' => $this->destinationCity,
            'departure_at' => $this->departure_at,
            'arrival_at' => $this->arrival_at,
        ];
    }
}
