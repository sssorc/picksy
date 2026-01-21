<?php

use App\Models\Answer;
use App\Models\Event;
use App\Models\Question;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

test('user can view questions page', function () {
    $response = $this->actingAs($this->user)->get(route('questions.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/QuestionsPage')
        ->has('event')
    );
});

test('user cannot view questions page without event', function () {
    $userWithoutEvent = User::factory()->create();

    $response = $this->actingAs($userWithoutEvent)->get(route('questions.index'));

    $response->assertRedirect(route('event.edit'));
});

test('user can save new questions with answers', function () {
    $questionsData = [
        'questions' => [
            [
                'id' => null,
                'question_text' => 'What color will the bridesmaids wear?',
                'is_tiebreaker' => false,
                'answers' => [
                    ['id' => null, 'answer_text' => 'Blue', 'order' => 0],
                    ['id' => null, 'answer_text' => 'Pink', 'order' => 1],
                    ['id' => null, 'answer_text' => 'Green', 'order' => 2],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertSuccessful();
    $response->assertJson([
        'message' => 'Questions saved successfully.',
    ]);

    $this->assertDatabaseHas('questions', [
        'event_id' => $this->event->id,
        'question_text' => 'What color will the bridesmaids wear?',
    ]);

    $question = Question::where('event_id', $this->event->id)->first();
    expect($question->answers)->toHaveCount(3);
});

test('user can update existing questions', function () {
    $question = Question::factory()->create([
        'event_id' => $this->event->id,
        'question_text' => 'Original question?',
        'order' => 0,
    ]);

    $answer = Answer::factory()->create([
        'question_id' => $question->id,
        'answer_text' => 'Original answer',
        'order' => 0,
    ]);

    $questionsData = [
        'questions' => [
            [
                'id' => $question->id,
                'question_text' => 'Updated question?',
                'is_tiebreaker' => false,
                'answers' => [
                    ['id' => $answer->id, 'answer_text' => 'Updated answer', 'order' => 0],
                    ['id' => null, 'answer_text' => 'New answer', 'order' => 1],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertSuccessful();

    $this->assertDatabaseHas('questions', [
        'id' => $question->id,
        'question_text' => 'Updated question?',
    ]);

    $this->assertDatabaseHas('answers', [
        'id' => $answer->id,
        'answer_text' => 'Updated answer',
    ]);

    $this->assertDatabaseHas('answers', [
        'question_id' => $question->id,
        'answer_text' => 'New answer',
    ]);
});

test('user can delete questions', function () {
    $question1 = Question::factory()->create([
        'event_id' => $this->event->id,
        'question_text' => 'Keep this question',
        'order' => 0,
    ]);

    $question2 = Question::factory()->create([
        'event_id' => $this->event->id,
        'question_text' => 'Delete this question',
        'order' => 1,
    ]);

    $questionsData = [
        'questions' => [
            [
                'id' => $question1->id,
                'question_text' => 'Keep this question',
                'is_tiebreaker' => false,
                'answers' => [
                    ['id' => null, 'answer_text' => 'Answer 1', 'order' => 0],
                    ['id' => null, 'answer_text' => 'Answer 2', 'order' => 1],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertSuccessful();

    $this->assertDatabaseHas('questions', ['id' => $question1->id]);
    $this->assertDatabaseMissing('questions', ['id' => $question2->id]);
});

test('user can delete answers', function () {
    $question = Question::factory()->create([
        'event_id' => $this->event->id,
        'order' => 0,
    ]);

    $answer1 = Answer::factory()->create([
        'question_id' => $question->id,
        'answer_text' => 'Keep this',
        'order' => 0,
    ]);

    $answer2 = Answer::factory()->create([
        'question_id' => $question->id,
        'answer_text' => 'Delete this',
        'order' => 1,
    ]);

    $questionsData = [
        'questions' => [
            [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'is_tiebreaker' => false,
                'answers' => [
                    ['id' => $answer1->id, 'answer_text' => 'Keep this', 'order' => 0],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertSuccessful();

    $this->assertDatabaseHas('answers', ['id' => $answer1->id]);
    $this->assertDatabaseMissing('answers', ['id' => $answer2->id]);
});

test('questions are limited to 16', function () {
    $questionsData = [
        'questions' => array_fill(0, 17, [
            'id' => null,
            'question_text' => 'Test question?',
            'is_tiebreaker' => false,
            'answers' => [
                ['id' => null, 'answer_text' => 'Answer 1', 'order' => 0],
                ['id' => null, 'answer_text' => 'Answer 2', 'order' => 1],
            ],
        ]),
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['questions']);
});

test('answers require at least 2 options', function () {
    $questionsData = [
        'questions' => [
            [
                'id' => null,
                'question_text' => 'Test question?',
                'is_tiebreaker' => false,
                'answers' => [
                    ['id' => null, 'answer_text' => 'Only one answer', 'order' => 0],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['questions.0.answers']);
});

test('answers are limited to 6 per question', function () {
    $questionsData = [
        'questions' => [
            [
                'id' => null,
                'question_text' => 'Test question?',
                'is_tiebreaker' => false,
                'answers' => array_fill(0, 7, [
                    'id' => null,
                    'answer_text' => 'Test answer',
                    'order' => 0,
                ]),
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['questions.0.answers']);
});

test('questions cannot be updated after entries have been submitted', function () {
    // Create a participant with a submitted entry
    $participant = \App\Models\Participant::factory()->create([
        'event_id' => $this->event->id,
        'submitted_at' => now(),
    ]);

    $questionsData = [
        'questions' => [
            [
                'id' => null,
                'question_text' => 'New question?',
                'is_tiebreaker' => false,
                'answers' => [
                    ['id' => null, 'answer_text' => 'Answer 1', 'order' => 0],
                    ['id' => null, 'answer_text' => 'Answer 2', 'order' => 1],
                ],
            ],
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('questions.store'), $questionsData);

    $response->assertStatus(422);
    $response->assertJson([
        'message' => 'Questions cannot be updated after entries have been submitted.',
    ]);
});
