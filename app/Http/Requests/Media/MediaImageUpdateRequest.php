<?php

namespace App\Http\Requests\Media;

use App\Models\Metadata;
use App\Rules\ImageUpdateRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class MediaImageUpdateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Gate::allows('editor');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'type' => ['required', Rule::in(Metadata::getEditableImageTypes())],
            'mode' => ['required', Rule::in(['existing', 'upload', 'url', 'remove'])],
            ...ImageUpdateRules::base(),
        ];
    }
}
