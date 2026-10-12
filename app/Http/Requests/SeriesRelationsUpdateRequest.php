<?php

namespace App\Http\Requests;

use App\Enums\SeriesRelationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SeriesRelationsUpdateRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'upserts' => ['sometimes', 'array'],
            'upserts.*.series_id' => ['required', 'integer', 'distinct', 'exists:series,id', Rule::notIn([$this->route('series')->id])],
            'upserts.*.type' => ['required', Rule::enum(SeriesRelationType::class)->except([SeriesRelationType::PARENT, SeriesRelationType::PREQUEL])],
            'deletes' => ['sometimes', 'array'],
            'deletes.*' => 'integer|distinct',
        ];
    }
}
