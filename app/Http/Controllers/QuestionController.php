<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionsRequest;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class QuestionController extends Controller
{
    public function index(): Response|RedirectResponse
    {
        $event = auth()->user()->event()->with('questions.answers')->first();

        if (! $event) {
            return redirect()->back();
        }

        return Inertia::render('admin/QuestionsPage', [
            'event' => $event,
            'popularQuestions' => $this->getPopularQuestions(),
        ]);
    }

    public function store(StoreQuestionsRequest $request): JsonResponse
    {
        $event = auth()->user()->event;

        if (! $event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        if ($event->hasStarted()) {
            return response()->json(['message' => 'Questions cannot be updated after the event has started.'], 422);
        }

        DB::transaction(function () use ($request, $event) {
            // Get existing question IDs
            $submittedQuestionIds = collect($request->questions)->pluck('id')->filter();

            // Delete questions that were removed
            $event->questions()
                ->whereNotIn('id', $submittedQuestionIds)
                ->delete();

            // Process each question
            foreach ($request->questions as $index => $questionData) {
                $question = Question::query()->updateOrCreate(
                    [
                        'id' => $questionData['id'] ?? null,
                        'event_id' => $event->id,
                    ],
                    [
                        'question_text' => $questionData['question_text'],
                        'order' => $index,
                        'is_tiebreaker' => $questionData['is_tiebreaker'] ?? false,
                    ]
                );

                // Handle answers for non-tiebreaker questions
                if (! $question->is_tiebreaker && isset($questionData['answers'])) {
                    $submittedAnswerIds = collect($questionData['answers'])->pluck('id')->filter();

                    // Delete answers that were removed
                    $question->answers()
                        ->whereNotIn('id', $submittedAnswerIds)
                        ->delete();

                    // Process each answer
                    foreach ($questionData['answers'] as $answerIndex => $answerData) {
                        Answer::query()->updateOrCreate(
                            [
                                'id' => $answerData['id'] ?? null,
                                'question_id' => $question->id,
                            ],
                            [
                                'answer_text' => $answerData['answer_text'],
                                'order' => $answerIndex,
                            ]
                        );
                    }
                }
            }

            // Handle tiebreaker question
            if ($request->has('tiebreaker') && $request->tiebreaker) {
                Question::query()->updateOrCreate(
                    [
                        'id' => $request->tiebreaker['id'] ?? null,
                        'event_id' => $event->id,
                    ],
                    [
                        'question_text' => $request->tiebreaker['question_text'],
                        'order' => 999, // Put tiebreaker at the end
                        'is_tiebreaker' => true,
                    ]
                );
            } else {
                // Delete tiebreaker if it was removed
                $event->questions()->where('is_tiebreaker', true)->delete();
            }
        });

        return response()->json([
            'message' => 'Questions saved successfully.',
            'event' => $event->load('questions.answers'),
        ]);
    }

    private function getPopularQuestions(): array
    {
        $path = resource_path('data/popular-questions.json');

        if (! file_exists($path)) {
            return [];
        }

        $json = file_get_contents($path);

        return json_decode($json, true) ?? [];
    }
}
