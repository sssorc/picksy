<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionsRequest extends FormRequest
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
            'questions' => ['required', 'array', 'max:16'],
            'questions.*.id' => ['nullable', 'integer', 'exists:questions,id'],
            'questions.*.question_text' => ['required', 'string', 'max:500'],
            'questions.*.is_tiebreaker' => ['boolean'],
            'questions.*.answers' => ['required_if:questions.*.is_tiebreaker,false', 'array', 'min:2', 'max:6'],
            'questions.*.answers.*.id' => ['nullable', 'integer', 'exists:answers,id'],
            'questions.*.answers.*.answer_text' => ['required', 'string', 'max:255'],
            'tiebreaker' => ['nullable', 'array'],
            'tiebreaker.id' => ['nullable', 'integer', 'exists:questions,id'],
            'tiebreaker.question_text' => ['required_with:tiebreaker', 'string', 'max:500'],
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
            'questions.max' => 'You can have a maximum of 16 questions.',
            'questions.*.answers.min' => 'Each question must have at least 2 answers.',
            'questions.*.answers.max' => 'Each question can have a maximum of 6 answers.',
        ];
    }
}
