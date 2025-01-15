<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SeriesStoreRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'folder_id' => 'required|integer',
            'title' => 'required|max:255',
            'description' => 'nullable',
            'studio' => 'nullable|max:255',
            'rating' => 'nullable|integer|min:0|max:100',
            'seasons' => 'nullable|integer|min:1',
            'episodes' => 'nullable|integer|min:0',
            'films' => 'nullable|integer|min:0',
            'date_start' => 'nullable|date|date_format:"F d, Y"',
            'date_end' => 'nullable|date|date_format:"F d, Y"',
            'thumbnail_url' => 'nullable|url',
        ];
    }
}
