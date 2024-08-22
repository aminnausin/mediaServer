<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MetadataUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'description' => 'nullable|max:512',
            'episode' => 'nullable|integer|min:0',
            'season' => 'nullable|integer|min:1',
            'date_released' => 'nullable|date',
            'tags' => 'nullable|max:128'
        ];
    }
}
