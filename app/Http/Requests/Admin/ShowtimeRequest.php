<?php

namespace App\Http\Requests\Admin;

use App\Models\Showtime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowtimeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $showtime = $this->route('showtime');

        return [
            'movie_id' => ['required', 'integer', 'exists:movies,id'],
            'screen_id' => ['required', 'integer', 'exists:screens,id'],
            'start_time' => array_filter([
                'required',
                'date',
                $showtime instanceof Showtime ? null : 'after:now',
            ]),
            'base_price_cents' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(['scheduled', 'cancelled'])],
        ];
    }
}
