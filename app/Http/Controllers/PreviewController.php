<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class PreviewController extends Controller
{
    public function show(string $slug): Response
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Preview/Show', [
            'event' => $event,
            'hasStarted' => $event->hasStarted(),
        ]);
    }

    public function picks(string $slug): Response
    {
        $event = Event::query()
            ->with('questions.answers')
            ->where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return Inertia::render('Preview/Picks', [
            'event' => $event,
            'questions' => $event->questions->where('is_tiebreaker', false),
            'tiebreaker' => $event->questions->where('is_tiebreaker', true)->first(),
        ]);
    }

    public function leaderboard(string $slug): Response
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Generate mock leaderboard data for preview
        $mockParticipants = [
            ['name' => 'John Doe', 'score' => 0],
            ['name' => 'Jane Smith', 'score' => 0],
            ['name' => 'Bob Johnson', 'score' => 0],
        ];

        $gradedCount = $event->questions()
            ->whereNotNull('graded_at')
            ->where('is_tiebreaker', false)
            ->count();

        $totalCount = $event->questions()
            ->where('is_tiebreaker', false)
            ->count();

        return Inertia::render('Preview/Leaderboard', [
            'event' => $event,
            'participants' => $mockParticipants,
            'gradedCount' => $gradedCount,
            'totalCount' => $totalCount,
        ]);
    }
}
