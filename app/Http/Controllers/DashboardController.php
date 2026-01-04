<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $event = auth()->user()->event()
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
            ->first();

        // If no event exists, return empty dashboard
        if (! $event) {
            return Inertia::render('admin/Dashboard', [
                'event' => null,
                'participants' => [],
                'gradedCount' => 0,
                'totalCount' => 0,
                'tiebreaker_question' => null,
            ]);
        }

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

        return Inertia::render('admin/Dashboard', [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'password' => $event->password,
                'grading_password' => $event->grading_password,
                'start_datetime' => $event->start_datetime?->toIso8601String(),
                'has_started' => $event->hasStarted(),
                'is_published' => $event->is_published,
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
