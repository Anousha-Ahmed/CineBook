<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MovieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured')]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'synopsis' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'min:20', 'max:400'],
            'rating' => ['required', 'string', 'max:12'],
            'poster_url' => ['nullable', 'string', 'max:500', 'regex:/^(https?:\/\/\S+|\/\S*)$/'],
            'trailer_url' => ['nullable', 'url', 'max:500'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'is_featured' => ['boolean'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }
}
