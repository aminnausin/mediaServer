<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SeriesUpdateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Auth::check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation() {
        if ($this->has('thumbnail_url')) {
            $this->merge(['thumbnail_url' => str_replace(' ', '%20', $this->input('thumbnail_url'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'title' => 'required|max:255',
            'description' => 'nullable',
            'studio' => 'nullable|max:255',
            'rating' => 'nullable|integer|min:0|max:100',
            'seasons' => 'nullable|integer|min:0',
            'episodes' => 'nullable|integer|min:0',
            'films' => 'nullable|integer|min:0',
            'date_start' => 'nullable|date|date_format:"F d, Y"',
            'date_end' => 'nullable|date|date_format:"F d, Y"',
            'thumbnail_url' => 'nullable|url',
        ];
    }
}
