<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class DestroyCityRequest extends FormRequest
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

    public function prepareForValidation(): void
    {
        $city = $this->city;

        $incomingFlightsCount = $city->incomingFlights()->count();
        $outgoingFlightsCount = $city->outgoingFlights()->count();
        $airlinesAttached = $city->airlines()->count();

        if ($incomingFlightsCount || $outgoingFlightsCount || $airlinesAttached) {
            $this->rules = [
                'confirmation' => ['accepted']
            ];

            $relatedRecords = [];

            if ($incomingFlightsCount) {
                $relatedRecords[] = "<strong>{$incomingFlightsCount} incoming flight(s)</strong>";
            }

            if ($outgoingFlightsCount) {
                $relatedRecords[] = "<strong>{$outgoingFlightsCount} outgoing flight(s)</strong>";
            }

            if ($airlinesAttached) {
                $relatedRecords[] = "{<strong>$airlinesAttached} airline(s) attached</strong>";
            }

            $joinedRelatedRecords = Arr::join($relatedRecords, ', ', ' and ');

            $message = "The city has {$joinedRelatedRecords}, this action will delete the city as well as the mentioned related record(s), confirm to delete";

            $this->messages = [
                'confirmation.accepted' => $message
            ];
        }
    }
}
