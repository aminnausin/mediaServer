<?php

namespace App\Http\Requests\Server;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerformanceConfigRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     * Gets category and potentially folder id from query strings
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'max_scan_workers' => 'integer|min:1|max:16|required',
            'max_event_workers' => 'integer|min:1|max:8|required',
        ];
    }
}
