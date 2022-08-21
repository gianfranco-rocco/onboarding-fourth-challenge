<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'airline' => ['required', 'exists:airlines,id'],
            'departure_city' => [
                'required',
                'different:destination_city',
                'exists:cities,id',
                Rule::exists('airline_city', 'city_id')->where(function ($query) {
                    return $query->where('airline_id', $this->airline);
                })
            ],
            'destination_city' => [
                'required',
                'different:departure_city',
                'exists:cities,id',
                Rule::exists('airline_city', 'city_id')->where(function ($query) {
                    return $query->where('airline_id', $this->airline);
                })
            ],
            'departure_at' => ['required', 'date_format:Y-m-d H:i', 'after_or_equal:today'],
            'arrival_at' => ['required', 'date_format:Y-m-d H:i', 'after:departure_at']
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'departure_at' => "{$this->departure_at_date} {$this->departure_at_time}",
            'arrival_at' => "{$this->arrival_at_date} {$this->arrival_at_time}",
        ]);
    }
}
