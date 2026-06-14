<?php

namespace App\Http\Requests;

use App\Support\MetadataRules;
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
            ...MetadataRules::base(),
            'deleted_tags' => 'nullable|array',
            'deleted_tags.*' => 'integer',
        ];
    }
}
