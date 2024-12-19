<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaybackStoreRequest extends FormRequest {
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
            'entries' => 'array|required|min:1',
            'entries.*.metadata_id' => 'required|integer',
            'entries.*.progress' => 'required|numeric|between:0,1000',
        ];
    }
}
