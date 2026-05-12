<?php

namespace App\Http\Requests\Server;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateStorageConfigRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return $this->user()?->isAdmin();
    }

    protected function prepareForValidation() {
        if ($this->has('cache_path')) {
            $this->merge(['cache_path' => rtrim($this->cache_path, '/')]);
        }

        if ($this->has('metadata_path')) {
            $this->merge(['metadata_path' => rtrim($this->metadata_path, '/')]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     * Gets category and potentially folder id from query strings
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'cache_path' => ['nullable', 'string', function ($_, $value, $fail) {
                $this->validatePath($value, $fail);
            }],
            'metadata_path' => ['nullable', 'string', function ($_, $value, $fail) {
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
        if (! $path) {
            return;
        }

        if (str_contains($path, '..') || str_contains($path, "\0")) {
            $fail("The path '$path' is invalid.");

            return;
        }

        $exists = str_starts_with($path, 'storage/')
            ? Storage::disk('local')->exists(Str::after($path, 'storage/'))
            : is_dir($path);

        if (! $exists) {
            $fail("The path '$path' does not exist.");
        }
    }
}
