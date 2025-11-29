<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
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
        $event = $this->user()->event;

        return [
            'title' => ['required', 'string', 'max:255'],
            'intro_text' => ['nullable', 'string', 'max:1000'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('events', 'slug')->ignore($event?->id),
            ],
            'password' => ['nullable', 'string', 'max:255'],
            'grading_password' => ['required', 'string', 'max:255'],
            'start_datetime' => ['required', 'date', 'after:now'],
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
            'slug.regex' => 'The event path must be lowercase letters, numbers, and hyphens only.',
            'slug.unique' => 'This event path is already taken. Please choose a different one.',
            'start_datetime.after' => 'The event start time must be in the future.',
        ];
    }
}
