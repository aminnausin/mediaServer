<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UserStoreRequest extends FormRequest {
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
            'name' => [
                'required',
                'string',
                'lowercase',
                'min:3',
                'max:255',
                'unique:users,name',
                'regex:/^[a-zA-Z0-9_-]+$/',
                'not_in:profile,api,admin,user,settings,login,register,logout,home,dashboard',
                function ($_, $value, $fail) {
                    if (! preg_match('/[a-zA-Z]/', $value)) {
                        $fail('Username must contain at least one letter.');
                    }
                    if (preg_match('/[_-]{2}/', $value)) {
                        $fail('Username cannot contain consecutive hyphens or underscores.');
                    }
                    if (preg_match('/^[_-]|[_-]$/', $value)) {
                        $fail('Username cannot start or end with a hyphen or underscore.');
                    }
                },
            ],
            'email' => ['required', 'string', 'max:255', 'unique:users,email', Rules\Email::defaults()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
