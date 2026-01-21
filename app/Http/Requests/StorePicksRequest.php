<?php

namespace App\Http\Requests;

use App\Models\Answer;
use App\Models\Event;
use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'loaded_at' => ['required', 'integer'],
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

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $slug = $this->route('slug');
            $event = Event::where('slug', $slug)->first();

            if (! $event) {
                return;
            }

            $loadedAt = $this->loaded_at;

            // Get current question IDs for this event
            $currentQuestionIds = $event->questions()->pluck('id')->sort()->values()->toArray();
            $submittedQuestionIds = collect($this->picks)->pluck('question_id')->sort()->values()->toArray();

            // Check if questions have been added or removed
            if ($currentQuestionIds !== $submittedQuestionIds) {
                $validator->errors()->add(
                    'picks',
                    'The questions have been updated. Please refresh the page and submit your picks again.'
                );

                return;
            }

            // Check if any question has been updated since the page was loaded
            $hasQuestionUpdate = $event->questions()->where('updated_at', '>', date('Y-m-d H:i:s', $loadedAt))->exists();

            if ($hasQuestionUpdate) {
                $validator->errors()->add(
                    'picks',
                    'The questions have been updated. Please refresh the page and submit your picks again.'
                );

                return;
            }

            // Check if any answer has been updated since the page was loaded
            $questionIds = $event->questions()->pluck('id');
            $hasAnswerUpdate = Answer::whereIn('question_id', $questionIds)
                ->where('updated_at', '>', date('Y-m-d H:i:s', $loadedAt))
                ->exists();

            if ($hasAnswerUpdate) {
                $validator->errors()->add(
                    'picks',
                    'The answers have been updated. Please refresh the page and submit your picks again.'
                );
            }
        });
    }
}
