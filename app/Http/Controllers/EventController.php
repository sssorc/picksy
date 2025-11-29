<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function edit(): Response
    {
        $event = auth()->user()->event()->with('questions.answers')->first();

        return Inertia::render('Event/Edit', [
            'event' => $event,
        ]);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $user = auth()->user();

        $event = $user->event()->updateOrCreate(
            ['user_id' => $user->id],
            $request->validated()
        );

        return response()->json([
            'message' => 'Event saved successfully.',
            'event' => $event,
        ]);
    }
}
