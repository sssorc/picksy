<?php

use App\Models\Event;
use App\Models\Question;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->event = Event::factory()->create([
        'user_id' => $this->user->id,
        'is_published' => false,
    ]);

    // Create at least one question to allow publishing
    Question::factory()->create([
        'event_id' => $this->event->id,
        'is_tiebreaker' => false,
    ]);
});

test('user can publish event with free tier', function () {
    $response = $this->actingAs($this->user)
        ->post(route('publish.store'), [
            'max_entries' => 10,
        ]);

    $response->assertRedirect(route('publish.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('events', [
        'id' => $this->event->id,
        'is_published' => true,
        'max_entries' => 10,
    ]);

    expect($this->event->fresh()->published_at)->not->toBeNull();
});

test('paid tier requires stripe payment', function () {
    // Skip this test if Stripe is not configured
    if (! class_exists(\Stripe\Stripe::class)) {
        $this->markTestSkipped('Stripe SDK not installed');
    }

    $response = $this->actingAs($this->user)
        ->postJson(route('publish.store'), [
            'max_entries' => 60,
        ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['checkout_url']);
});

test('unlimited tier requires stripe payment', function () {
    // Skip this test if Stripe is not configured
    if (! class_exists(\Stripe\Stripe::class)) {
        $this->markTestSkipped('Stripe SDK not installed');
    }

    $response = $this->actingAs($this->user)
        ->postJson(route('publish.store'), [
            'max_entries' => 0,
        ]);

    $response->assertSuccessful();
    $response->assertJsonStructure(['checkout_url']);
});

test('publishing requires valid max_entries value', function () {
    $response = $this->actingAs($this->user)
        ->post(route('publish.store'), [
            'max_entries' => 25,
        ]);

    $response->assertSessionHasErrors('max_entries');

    $this->assertDatabaseHas('events', [
        'id' => $this->event->id,
        'is_published' => false,
    ]);
});

test('cannot publish already published event', function () {
    $this->event->update([
        'is_published' => true,
        'published_at' => now(),
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('publish.store'), [
            'max_entries' => 10,
        ]);

    $response->assertStatus(400);
    $response->assertJson(['message' => 'Event is already published.']);
});

test('user can view publish page', function () {
    $response = $this->actingAs($this->user)
        ->get(route('publish.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/PublishPage')
        ->has('event')
    );
});
