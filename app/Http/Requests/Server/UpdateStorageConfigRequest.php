<?php

namespace App\Http\Requests\Server;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStorageConfigRequest extends FormRequest {
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
            'cache_path' => ['nullable', 'string', function ($attr, $value, $fail) {
                $this->validatePath($value, $fail);
            }],
            'metadata_path' => ['nullable', 'string', function ($attr, $value, $fail) {
                $this->validatePath($value, $fail);
            }],
        ];
    }

    /**
     * Validates existance of a given path on the local (private) disk
     *
     * @return bool
     */
    private function validatePath(string $path, Closure $fail) {
        if ($path && ! is_dir($path)) {
            $fail("The path '$path' does not exist on the server.");
        }
    }
}
