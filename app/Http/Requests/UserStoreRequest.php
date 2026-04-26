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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void {
        $this->merge([
            'name' => strtolower($this->name),
            'email' => strtolower($this->email),
        ]);
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
                'regex:/^(?=.*[a-z])[a-z0-9_-]+$/',
                'not_regex:/^[_-]|[_-]$|[_-]{2}/',
                'not_in:profile,api,admin,user,settings,login,register,logout,home,dashboard',
            ],
            'email' => ['required', 'string', 'lowercase', 'max:255', 'unique:users,email', Rules\Email::defaults()],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
