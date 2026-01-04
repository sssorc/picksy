<?php

use App\Models\Event;
use App\Models\User;

test('user can soft delete their event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->delete('/event')
        ->assertRedirect('/');

    $this->assertSoftDeleted('events', ['id' => $event->id]);
});

test('deleting event redirects to dashboard', function () {
    $user = User::factory()->create();
    Event::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->delete('/event');

    $response->assertRedirect(route('home'));
});

test('user without event can still call delete without error', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/event')
        ->assertRedirect('/');
});

test('unauthenticated user cannot delete event', function () {
    $this->delete('/event')
        ->assertRedirect('/login');
});
