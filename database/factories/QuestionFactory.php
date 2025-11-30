<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'question_text' => fake()->sentence() . '?',
            'order' => 0,
            'is_tiebreaker' => false,
            'graded_at' => null,
        ];
    }

    public function tiebreaker(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_tiebreaker' => true,
            'order' => 999,
        ]);
    }

    public function graded(): static
    {
        return $this->state(fn (array $attributes) => [
            'graded_at' => now(),
        ]);
    }
}
