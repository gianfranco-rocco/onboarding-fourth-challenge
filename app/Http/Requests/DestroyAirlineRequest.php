<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyAirlineRequest extends FormRequest
{
    private array $rules, $messages;

    public function __construct()
    {
        $this->rules = [];
        $this->messages = [];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function messages()
    {
        return $this->messages;
    }

    protected function prepareForValidation(): void
    {
        $airline = $this->airline;

        $flightsCount = $airline->flights()->count();

        if ($flightsCount) {
            $this->rules = [
                'confirmation' => ['accepted']
            ];

            $this->messages = [
                'confirmation.accepted' => "The airline is assigned to <strong>{$flightsCount} flight(s)</strong>, this action will delete the airline as well as every flight assigned to it."
            ];
        }
    }
}
