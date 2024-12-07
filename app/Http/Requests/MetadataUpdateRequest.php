<?php

namespace App\Http\Requests;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'title' => 'required|max:255',
            'description' => 'nullable|max:512',
            'episode' => 'nullable|integer|min:0',
            'season' => 'nullable|integer|min:1',
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
