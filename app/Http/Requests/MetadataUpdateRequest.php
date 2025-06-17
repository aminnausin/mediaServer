<?php

namespace App\Http\Requests;

use App\Support\RequestPresets;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MetadataUpdateRequest extends FormRequest {
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
        if ($this->has('poster_url')) {
            $this->merge(['poster_url' => str_replace(' ', '%20', $this->input('poster_url'))]);
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
            'lyrics' => 'nullable',
            'episode' => RequestPresets::NON_NEGATIVE_INT,
            'season' => RequestPresets::NON_NEGATIVE_INT,
            'poster_url' => 'nullable|url',
            'date_released' => 'nullable|date|date_format:"F d, Y"',
            'tags' => 'nullable|max:128',
            'video_tags' => 'nullable|array',
            'video_tags.*.name' => 'required|min:1|max:64',
            'video_tags.*.id' => 'required|integer',
            'deleted_tags' => 'nullable|array',
            'deleted_tags.*' => 'integer',
        ];
    }
}
