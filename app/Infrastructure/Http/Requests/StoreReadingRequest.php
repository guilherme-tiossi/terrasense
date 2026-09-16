<?php

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_plant_id' => ['required', 'integer', 'min:1'],
            'humidity' => ['required', 'numeric', 'min:0', 'max:100'],
            'measured_at' => ['required', 'date'],
        ];
    }
}
