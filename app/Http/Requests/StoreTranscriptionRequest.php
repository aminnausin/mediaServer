<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranscriptionRequest extends FormRequest {
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
            'task_id' => ['required', 'integer', 'min:1'],
            'metadata_id' => ['required', 'integer', 'min:1'],
            'vtt' => ['required', 'string', 'max:10485760'],
            'language' => ['required', 'string', 'max:20'],
            'language_probability' => ['required', 'numeric', 'between:0,1'],
            'segment_count' => ['required', 'integer', 'min:0'],
        ];
    }
}
