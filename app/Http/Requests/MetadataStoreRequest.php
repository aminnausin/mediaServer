<?php

namespace App\Http\Requests;

use App\Support\MetadataRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MetadataStoreRequest extends FormRequest {
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
            'video_id' => 'required|integer|exists:videos,id',
            ...MetadataRules::base(),
        ];
    }
}
