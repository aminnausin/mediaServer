<?php

namespace App\Http\Requests;

use App\Services\Auth\GuestIdentity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PlaybackProgressStoreRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Auth::check() || GuestIdentity::guestToken();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'progress_offset' => 'required|integer|min:0',
            'record_id' => 'nullable|integer',
        ];
    }
}
