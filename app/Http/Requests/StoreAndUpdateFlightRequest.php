<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAndUpdateFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $arrivalAtTime = ['required', 'date_format:H:i'];
        
        if ($this->departure_at_date === $this->arrival_at_date) {
            $arrivalAtTime[] = 'after:departure_at_time';
        }

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
            'departure_at_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'departure_at_time' => ['required', 'date_format:H:i'],
            'arrival_at_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:departure_at_date'],
            'arrival_at_time' => $arrivalAtTime,
            'departure_at' => ['exclude_without:arrival_at,', 'before:arrival_at'],
            'arrival_at' => ['exclude_without:departure_at', 'after:departure_at'],
        ];
    }

    public function messages()
    {
        return [
            'arrival_at_time.after' => 'The arrival time must be a time after the departure time when both the departure date and arrival date are the same.'
        ];
    }

    public function attributes()
    {
        return [
            'departure_at_date' => 'departure date',
            'departure_at_time' => 'departure time',
            'arrival_at_date' => 'arrival date',
            'arrival_at_time' => 'arrival time',
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if (!empty($this->departure_at_date) && !empty($this->departure_at_time)) {
            $merge['departure_at'] = "{$this->departure_at_date} {$this->departure_at_time}";
        }

        if (!empty($this->arrival_at_date) && !empty($this->arrival_at_time)) {
            $merge['arrival_at'] = "{$this->arrival_at_date} {$this->arrival_at_time}";
        }

        $this->merge($merge);
    }
}
