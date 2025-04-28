<?php

namespace App\Http\Requests;

use App\Support\RequestPresets;
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
            'folder_id' => 'required|integer',
            'title' => 'required|max:255',
            'description' => 'nullable',
            'studio' => 'nullable|max:255',
            'rating' => RequestPresets::NON_NEGATIVE_INT . '|max:100',
            'seasons' => RequestPresets::NON_NEGATIVE_INT,
            'episodes' => RequestPresets::NON_NEGATIVE_INT,
            'films' => RequestPresets::NON_NEGATIVE_INT,
            'date_start' => 'nullable|date|date_format:"F d, Y"',
            'date_end' => 'nullable|date|date_format:"F d, Y"',
            'thumbnail_url' => 'nullable|url',
            'tags' => 'nullable|array',
            'tags.*.name' => 'required|min:1|max:64',
            'tags.*.id' => 'required|integer',
        ];
    }
}
