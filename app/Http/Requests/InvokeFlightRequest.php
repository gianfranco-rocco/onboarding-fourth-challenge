<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class InvokeFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    protected function prepareForValidation()
    {
        $merge = [];

        if ($this->query('departure_at')) {
            $departureAt = $this->parseStringDateToCarbon($this->query('departure_at'));

            if ($departureAt) {
                $merge['departure_at'] = $departureAt;
            }
        }

        if ($this->query('arrival_at')) {
            $arrivalAt = $this->parseStringDateToCarbon($this->query('arrival_at'));

            if ($arrivalAt) {
                $merge['arrival_at'] = $arrivalAt;
            }
        }

        if (count($merge)) {
            $this->merge($merge);
        }
    }

    private function parseStringDateToCarbon(string $stringDate): ?Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $stringDate);
        } catch (\Throwable $t) {
            return null;
        }
    }
}
