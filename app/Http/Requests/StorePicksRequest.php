<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePicksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'picks' => ['required', 'array'],
            'picks.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'picks.*.answer_id' => ['nullable', 'integer', 'exists:answers,id'],
            'picks.*.tiebreaker_answer' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'picks.required' => 'Please select answers for all questions.',
            'picks.*.answer_id.required' => 'Please select an answer for this question.',
        ];
    }
}
