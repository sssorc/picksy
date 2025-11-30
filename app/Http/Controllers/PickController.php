<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePicksRequest;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Pick;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PickController extends Controller
{
    public function index(string $slug): Response|RedirectResponse
    {
        $event = Event::query()
            ->with(['questions.answers'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Get participant from cookie
        $participantId = request()->cookie("event_{$event->id}_participant");
        if (! $participantId) {
            return redirect()->route('event.show', $slug);
        }

        $participant = Participant::query()
            ->with(['picks.answer', 'picks.question.answers'])
            ->findOrFail($participantId);

        if ($participant->event_id !== $event->id) {
            return redirect()->route('event.show', $slug);
        }

        // If participant has submitted picks, show their picks
        if ($participant->hasSubmittedPicks()) {
            return Inertia::render('Public/MyPicks', [
                'event' => [
                    'title' => $event->title,
                    'intro_text' => $event->intro_text,
                ],
                'participant' => [
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                ],
                'picks' => $participant->picks->map(function ($pick) {
                    return [
                        'question_id' => $pick->question_id,
                        'question_text' => $pick->question->question_text,
                        'is_tiebreaker' => $pick->question->is_tiebreaker,
                        'selected_answer_id' => $pick->answer_id,
                        'tiebreaker_answer' => $pick->tiebreaker_answer,
                        'is_graded' => $pick->question->isGraded(),
                        'is_correct' => $pick->isCorrect(),
                        'correct_answer_id' => $pick->question->correctAnswer()?->id,
                        'answers' => $pick->question->answers->map(function ($answer) {
                            return [
                                'id' => $answer->id,
                                'answer_text' => $answer->answer_text,
                                'is_correct' => $answer->is_correct,
                            ];
                        }),
                    ];
                }),
            ]);
        }

        // Check if any questions have been graded (can't submit picks after grading starts)
        if ($event->hasAnyGradedQuestions()) {
            return Inertia::render('Public/PicksClosed', [
                'event' => [
                    'title' => $event->title,
                    'slug' => $event->slug,
                ],
            ]);
        }

        // Show picks submission form
        $questions = $event->questions->where('is_tiebreaker', false);
        $tiebreaker = $event->questions->where('is_tiebreaker', true)->first();

        return Inertia::render('Public/SubmitPicks', [
            'event' => [
                'title' => $event->title,
                'intro_text' => $event->intro_text,
            ],
            'participant' => [
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
            ],
            'questions' => $questions->values()->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'answers' => $question->answers->map(function ($answer) {
                        return [
                            'id' => $answer->id,
                            'answer_text' => $answer->answer_text,
                        ];
                    }),
                ];
            }),
            'tiebreaker' => $tiebreaker ? [
                'id' => $tiebreaker->id,
                'question_text' => $tiebreaker->question_text,
            ] : null,
        ]);
    }

    public function store(StorePicksRequest $request, string $slug): JsonResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Get participant from cookie
        $participantId = request()->cookie("event_{$event->id}_participant");
        if (! $participantId) {
            return response()->json(['message' => 'Participant not found.'], 403);
        }

        $participant = Participant::findOrFail($participantId);

        if ($participant->event_id !== $event->id) {
            return response()->json(['message' => 'Invalid participant.'], 403);
        }

        // Check if participant has already submitted
        if ($participant->hasSubmittedPicks()) {
            return response()->json(['message' => 'You have already submitted your picks.'], 400);
        }

        // Check if any questions have been graded
        if ($event->hasAnyGradedQuestions()) {
            return response()->json(['message' => 'Picks can no longer be submitted as grading has started.'], 400);
        }

        DB::transaction(function () use ($request, $participant) {
            // Create picks
            foreach ($request->picks as $pickData) {
                Pick::create([
                    'participant_id' => $participant->id,
                    'question_id' => $pickData['question_id'],
                    'answer_id' => $pickData['answer_id'] ?? null,
                    'tiebreaker_answer' => $pickData['tiebreaker_answer'] ?? null,
                ]);
            }

            // Mark participant as submitted
            $participant->update([
                'submitted_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Picks submitted successfully!',
            'redirect' => route('picks.index', $slug),
        ]);
    }
}
