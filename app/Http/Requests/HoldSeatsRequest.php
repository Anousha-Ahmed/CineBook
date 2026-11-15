<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HoldSeatsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['integer', 'exists:seats,id'],
        ];
    }
}
