<?php

namespace App\Http\Requests\Server;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScannerConfigRequest extends FormRequest {
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
            'uuid_embed' => 'boolean|required',
            'uuid_write_cache' => 'boolean|required',
            'attachments_extract' => 'boolean|required',
            'art_extract' => 'boolean|required',
            'thumbnails_generate' => 'boolean|required',
        ];
    }
}
