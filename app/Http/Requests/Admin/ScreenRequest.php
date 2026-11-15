<?php

namespace App\Http\Requests\Admin;

use App\Models\Screen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScreenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $screen = $this->route('screen');
        $screen = $screen instanceof Screen ? $screen : null;

        $maxRows = $screen?->rows ? max(26, (int) $screen->rows) : 26;

        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('screens', 'name')->ignore($screen?->id)],
            'rows' => ['required', 'integer', 'min:1', 'max:'.$maxRows],
            'seats_per_row' => ['required', 'integer', 'min:1', 'max:40'],
            'premium_rows' => ['required', 'integer', 'min:0', 'lte:rows'],
        ];
    }
}
