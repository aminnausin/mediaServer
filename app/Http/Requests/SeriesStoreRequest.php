<?php

namespace App\Http\Requests;

use App\Support\SeriesRules;
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
            'folder_id' => 'required|integer|exists:folders,id',
            ...SeriesRules::base(),
        ];
    }
}
