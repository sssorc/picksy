<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradesRequest;
use App\Models\Answer;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    public function show(string $slug): Response|RedirectResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Check if grading password is authenticated
        $isGradingAuthenticated = session()->get("event_{$event->id}_grading_auth", false);

        if (! $isGradingAuthenticated) {
            return Inertia::render('Public/GradingPassword', [
                'event' => [
                    'title' => $event->title,
                    'slug' => $event->slug,
                ],
            ]);
        }

        return redirect()->route('grade.questions', $slug);
    }

    public function authenticatePassword(Request $request, string $slug): RedirectResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! $event->verifyGradingPassword($request->password)) {
            return back()->withErrors([
                'password' => 'Incorrect grading password.',
            ]);
        }

        session()->put("event_{$event->id}_grading_auth", true);

        return redirect()->route('grade.questions', $slug);
    }

    public function index(string $slug): Response
    {
        $event = Event::query()
            ->with(['questions.answers'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $questions = $event->questions->where('is_tiebreaker', false);

        return Inertia::render('Public/Grade', [
            'event' => [
                'title' => $event->title,
                'slug' => $event->slug,
            ],
            'questions' => $questions->values()->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'is_graded' => $question->isGraded(),
                    'graded_at' => $question->graded_at?->toIso8601String(),
                    'answers' => $question->answers->map(function ($answer) {
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

    public function store(StoreGradesRequest $request, string $slug): JsonResponse
    {
        $event = Event::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        DB::transaction(function () use ($request, $event) {
            foreach ($request->grades as $gradeData) {
                $question = $event->questions()->findOrFail($gradeData['question_id']);

                // Clear all answers for this question first
                Answer::query()
                    ->where('question_id', $question->id)
                    ->update(['is_correct' => null]);

                // Set the correct answer if provided
                if (isset($gradeData['correct_answer_id']) && $gradeData['correct_answer_id']) {
                    Answer::query()
                        ->where('id', $gradeData['correct_answer_id'])
                        ->where('question_id', $question->id)
                        ->update(['is_correct' => true]);

                    // Mark question as graded
                    $question->update(['graded_at' => now()]);
                } else {
                    // If no answer selected, clear grading
                    $question->update(['graded_at' => null]);
                }
            }
        });

        return response()->json([
            'message' => 'Grades saved successfully!',
        ]);
    }
}
