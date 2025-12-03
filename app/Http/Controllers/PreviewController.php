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

        return Inertia::render('public/EventPicks', [
            'event' => [
                'slug' => $event->slug,
                'title' => $event->title,
                'intro_text' => $event->intro_text,
                'picks_closed' => true, // Preview mode - no submissions allowed
            ],
            'participant' => [
                'first_name' => 'Preview',
                'last_name' => 'User',
            ],
            'questions' => $event->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'is_tiebreaker' => $question->is_tiebreaker,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'answer_text' => $answer->answer_text,
                        ];
                    }),
                ];
            }),
            'picks' => [], // Empty picks for preview
            'isPreview' => true,
        ]);
    }

    public function leaderboard(string $slug): Response
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Get tiebreaker question (if exists)
        $tiebreakerQuestion = $event->questions()
            ->where('is_tiebreaker', true)
            ->first();

        // Generate mock leaderboard data for preview
        $mockParticipants = [
            ['name' => 'John Doe', 'score' => 0, 'tiebreaker_answer' => null],
            ['name' => 'Jane Smith', 'score' => 0, 'tiebreaker_answer' => null],
            ['name' => 'Bob Johnson', 'score' => 0, 'tiebreaker_answer' => null],
        ];

        $gradedCount = $event->questions()
            ->whereNotNull('graded_at')
            ->where('is_tiebreaker', false)
            ->count();

        $totalCount = $event->questions()
            ->where('is_tiebreaker', false)
            ->count();

        return Inertia::render('public/EventLeaderboard', [
            'event' => [
                'title' => $event->title,
                'slug' => $event->slug,
            ],
            'participants' => $mockParticipants,
            'gradedCount' => $gradedCount,
            'totalCount' => $totalCount,
            'tiebreaker_question' => $tiebreakerQuestion ? [
                'id' => $tiebreakerQuestion->id,
                'question_text' => $tiebreakerQuestion->question_text,
            ] : null,
        ]);
    }
}
