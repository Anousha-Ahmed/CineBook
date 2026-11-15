<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hold_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'max_seats' => ['required', 'integer', 'min:1', 'max:12'],
            'cancel_cutoff_hours' => ['required', 'integer', 'min:0', 'max:48'],
            'premium_surcharge_cents' => ['required', 'integer', 'min:0', 'max:10000'],
            'currency' => ['required', 'string', 'in:usd,eur,gbp,pkr'],
        ];
    }
}
