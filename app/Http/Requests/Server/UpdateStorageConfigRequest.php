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
        $paths = ['cache_path', 'metadata_path'];

        foreach ($paths as $path) {
            if (! $this->has($path)) {
                continue;
            }

            $value = str_replace('\\', '/', $this->input($path));
            $value = rtrim($value, '/');

            if ($value === '') {
                $value = null;
            }

            $this->merge([$path => $value]);
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
        if (empty($path)) {
            return;
        }

        if (str_contains($path, '..') || str_contains($path, "\0")) {
            $fail("The path '$path' is invalid.");

            return;
        }

        if ($path === 'storage') {
            $fail('The root storage folder is not a valid selection.');

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
