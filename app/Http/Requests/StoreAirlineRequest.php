<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAirlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 
                'unique:airlines',
                'string',
                'max:75'
            ],
            'description' => [
                'required',
                'string',
                'max:255'
            ],
            'cities' => [
                'nullable',
                'array'
            ],
            'cities.*' => [
                'exists:cities,id'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'cities.*.exists' => 'Invalid city'
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->cities) {
            $this->merge([
                'cities' => explode(',', $this->cities)
            ]);
        }
    }
}
