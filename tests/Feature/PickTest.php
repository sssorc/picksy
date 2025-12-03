<?php

use App\Models\Answer;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Question;

beforeEach(function () {
    $this->event = Event::factory()->create([
        'is_published' => true,
        'max_entries' => 10,
        'password' => null, // No password required
    ]);

    $this->question = Question::factory()->create([
        'event_id' => $this->event->id,
        'is_tiebreaker' => false,
    ]);

    $this->answers = Answer::factory()->count(3)->create([
        'question_id' => $this->question->id,
    ]);
});

test('participant can submit picks when under max entries', function () {
    // Create 5 participants who have already submitted
    Participant::factory()->count(5)->submitted()->create([
        'event_id' => $this->event->id,
    ]);

    $participant = Participant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $cookieName = "event_{$this->event->id}_participant";
    
    $response = $this->withUnencryptedCookie($cookieName, (string) $participant->id)
        ->postJson(route('picks.store', $this->event->slug), [
            'picks' => [
                [
                    'question_id' => $this->question->id,
                    'answer_id' => $this->answers->first()->id,
                ],
            ],
        ]);

    $response->assertSuccessful();
    expect($participant->fresh()->hasSubmittedPicks())->toBeTrue();
});

test('participant can submit picks at exactly max entries limit', function () {
    // Create 9 participants who have already submitted (this will be the 10th)
    Participant::factory()->count(9)->submitted()->create([
        'event_id' => $this->event->id,
    ]);

    $participant = Participant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->withCookie("event_{$this->event->id}_participant", $participant->id)
        ->postJson(route('picks.store', $this->event->slug), [
            'picks' => [
                [
                    'question_id' => $this->question->id,
                    'answer_id' => $this->answers->first()->id,
                ],
            ],
        ]);

    $response->assertSuccessful();
    expect($participant->fresh()->hasSubmittedPicks())->toBeTrue();
    expect($this->event->getSubmittedEntriesCount())->toBe(10);
});

test('participant cannot submit picks when max entries reached', function () {
    // Create 10 participants who have already submitted
    Participant::factory()->count(10)->submitted()->create([
        'event_id' => $this->event->id,
    ]);

    $participant = Participant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->withCookie("event_{$this->event->id}_participant", $participant->id)
        ->postJson(route('picks.store', $this->event->slug), [
            'picks' => [
                [
                    'question_id' => $this->question->id,
                    'answer_id' => $this->answers->first()->id,
                ],
            ],
        ]);

    $response->assertStatus(400);
    $response->assertJson(['message' => 'This event has reached its maximum number of entries.']);
    expect($participant->fresh()->hasSubmittedPicks())->toBeFalse();
});

test('unlimited entries allows any number of participants', function () {
    $this->event->update(['max_entries' => 0]);

    // Create 50 participants who have already submitted
    Participant::factory()->count(50)->submitted()->create([
        'event_id' => $this->event->id,
    ]);

    $participant = Participant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->withCookie("event_{$this->event->id}_participant", $participant->id)
        ->postJson(route('picks.store', $this->event->slug), [
            'picks' => [
                [
                    'question_id' => $this->question->id,
                    'answer_id' => $this->answers->first()->id,
                ],
            ],
        ]);

    $response->assertSuccessful();
    expect($participant->fresh()->hasSubmittedPicks())->toBeTrue();
    expect($this->event->getSubmittedEntriesCount())->toBe(51);
});

test('participants who have not submitted do not count towards limit', function () {
    // Create 8 who submitted and 5 who have not
    Participant::factory()->count(8)->submitted()->create([
        'event_id' => $this->event->id,
    ]);
    Participant::factory()->count(5)->create([
        'event_id' => $this->event->id,
        'submitted_at' => null,
    ]);

    // This should be the 9th submission, well under the limit of 10
    $participant = Participant::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $response = $this->withCookie("event_{$this->event->id}_participant", $participant->id)
        ->postJson(route('picks.store', $this->event->slug), [
            'picks' => [
                [
                    'question_id' => $this->question->id,
                    'answer_id' => $this->answers->first()->id,
                ],
            ],
        ]);

    $response->assertSuccessful();
    expect($this->event->getSubmittedEntriesCount())->toBe(9);
});
