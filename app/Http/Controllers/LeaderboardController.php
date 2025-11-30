<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function index(string $slug): Response
    {
        $event = Event::query()
            ->with([
                'participants' => function ($query) {
                    $query->whereNotNull('submitted_at')->orderBy('first_name');
                },
                'participants.picks' => function ($query) {
                    $query->whereHas('question', function ($q) {
                        $q->where('is_tiebreaker', true);
                    });
                },
            ])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Get tiebreaker question (if exists)
        $tiebreakerQuestion = $event->questions()
            ->where('is_tiebreaker', true)
            ->first();

        // Calculate scores for all participants
        $participants = $event->participants->map(function ($participant) use ($tiebreakerQuestion) {
            $tiebreakerAnswer = null;

            if ($tiebreakerQuestion) {
                $tiebreakerPick = $participant->picks
                    ->where('question_id', $tiebreakerQuestion->id)
                    ->first();

                $tiebreakerAnswer = $tiebreakerPick?->tiebreaker_answer;
            }

            return [
                'name' => $participant->full_name,
                'score' => $participant->calculateScore(),
                'tiebreaker_answer' => $tiebreakerAnswer,
            ];
        })->sortByDesc('score')->values();

        // Count graded questions
        $gradedCount = $event->questions()
            ->whereNotNull('graded_at')
            ->where('is_tiebreaker', false)
            ->count();

        $totalCount = $event->questions()
            ->where('is_tiebreaker', false)
            ->count();

        return Inertia::render('Public/Leaderboard', [
            'event' => [
                'title' => $event->title,
                'slug' => $event->slug,
            ],
            'participants' => $participants,
            'gradedCount' => $gradedCount,
            'totalCount' => $totalCount,
            'tiebreaker_question' => $tiebreakerQuestion ? [
                'id' => $tiebreakerQuestion->id,
                'question_text' => $tiebreakerQuestion->question_text,
            ] : null,
        ]);
    }
}
