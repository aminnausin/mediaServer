<?php

namespace App\Http\Requests;

use App\Support\RequestPresets;
use Illuminate\Foundation\Http\FormRequest;

class VideoUpdateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
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
            'episode' => RequestPresets::NON_NEGATIVE_INT,
            'season' => 'nullable|integer|min:1',
        ];
    }
}
