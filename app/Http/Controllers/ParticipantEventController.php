<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantRequest;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantEventController extends Controller
{
    public function show(string $slug): Response|RedirectResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Check if event has started
        if (! $event->hasStarted()) {
            return Inertia::render('public/EventNotStarted', [
                'event' => $event,
            ]);
        }

        // Check for existing participant cookie
        $participantId = request()->cookie("event_{$event->id}_participant");
        if ($participantId) {
            $participant = Participant::find($participantId);
            if ($participant && $participant->event_id === $event->id) {
                return redirect()->route('picks.index', $slug);
            }
        }

        // Show name entry form
        return Inertia::render('public/EventLogin', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'intro_text' => $event->intro_text,
                'slug' => $event->slug,
                'has_password' => ! empty($event->password),
            ],
        ]);
    }

    public function storeName(StoreParticipantRequest $request, string $slug): JsonResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Validate event password if required
        if ($event->password) {
            if (! $request->has('password') || $request->password !== $event->password) {
                return response()->json([
                    'errors' => [
                        'password' => 'Incorrect password.',
                    ],
                ], 422);
            }

            // Set password authentication in session
            session()->put("event_{$event->id}_password_auth", true);
        }

        // Check for existing participant
        $existingParticipant = Participant::query()
            ->where('event_id', $event->id)
            ->where('first_name', $request->first_name)
            ->where('last_name', $request->last_name)
            ->first();

        if ($existingParticipant) {
            return response()->json([
                'duplicate' => true,
                'participant' => [
                    'id' => $existingParticipant->id,
                    'first_name' => $existingParticipant->first_name,
                    'last_name' => $existingParticipant->last_name,
                    'has_submitted' => $existingParticipant->hasSubmittedPicks(),
                ],
            ]);
        }

        // Create new participant
        $participant = Participant::create([
            'event_id' => $event->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        // Set cookie for 60 days
        $cookie = Cookie::make(
            "event_{$event->id}_participant",
            $participant->id,
            60 * 24 * 60, // 60 days in minutes
            '/',
            null,
            config('session.secure', false), // Use session.secure config
            true,
            false,
            'lax'
        );

        return response()->json([
            'duplicate' => false,
            'redirect' => route('picks.index', $slug),
        ])->cookie($cookie);
    }

    public function confirmIdentity(string $slug): JsonResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $participantId = request()->input('participant_id');
        $participant = Participant::findOrFail($participantId);

        if ($participant->event_id !== $event->id) {
            return response()->json(['message' => 'Invalid participant.'], 403);
        }

        // Ensure password auth session is set (in case they're confirming a returning identity)
        if ($event->password) {
            session()->put("event_{$event->id}_password_auth", true);
        }

        // Set cookie for 60 days
        $cookie = Cookie::make(
            "event_{$event->id}_participant",
            $participant->id,
            60 * 24 * 60,
            '/',
            null,
            true,
            true,
            false,
            'lax'
        );

        return response()->json([
            'redirect' => route('picks.index', $slug),
        ])->cookie($cookie);
    }
}
