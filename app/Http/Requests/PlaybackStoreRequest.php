<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaybackStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'metadata_id' => ['required'],
            'progress' => ['required','min:0','max:100']
        ];
    }
}
