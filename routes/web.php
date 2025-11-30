<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ParticipantEventController;
use App\Http\Controllers\PickController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\PublishController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    // Event Management
    Route::get('event', [EventController::class, 'edit'])->name('event.edit');
    Route::post('event', [EventController::class, 'store'])->name('event.store');

    // Questions Management
    Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::post('event/questions', [QuestionController::class, 'store'])->name('questions.store');

    // Publishing
    Route::get('publish', [PublishController::class, 'index'])->name('publish.index');
    Route::post('publish', [PublishController::class, 'store'])->name('publish.store');
    Route::post('publish/without-payment', [PublishController::class, 'publishWithoutPayment'])->name('publish.without-payment'); // TODO: Remove before production

    // Preview
    Route::get('preview/{slug}', [PreviewController::class, 'show'])->name('preview.show');
    Route::get('preview/{slug}/picks', [PreviewController::class, 'picks'])->name('preview.picks');
    Route::get('preview/{slug}/leaderboard', [PreviewController::class, 'leaderboard'])->name('preview.leaderboard');
});

// Stripe Webhook (no auth/CSRF needed)
Route::post('stripe/webhook', [PublishController::class, 'webhook'])->withoutMiddleware(['web']);

// Public Event Routes (protected by event password)
Route::prefix('{slug}')->middleware('event.password')->group(function () {
    // Event Entry
    Route::get('/', [ParticipantEventController::class, 'show'])->name('event.show');
    Route::post('/password', [ParticipantEventController::class, 'authenticatePassword'])->name('event.password');
    Route::post('/name', [ParticipantEventController::class, 'storeName'])->name('participant.store');
    Route::post('/confirm-identity', [ParticipantEventController::class, 'confirmIdentity'])->name('participant.confirm');

    // Picks
    Route::get('/picks', [PickController::class, 'index'])->name('picks.index');
    Route::post('/picks', [PickController::class, 'store'])->name('picks.store');

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
});

// Grading Routes (protected by grading password only)
Route::prefix('{slug}')->group(function () {
    Route::get('/grade', [GradeController::class, 'show'])->name('grade.show');
    Route::post('/grade/password', [GradeController::class, 'authenticatePassword'])->name('grade.password');

    Route::middleware('grading.password')->group(function () {
        Route::get('/grade/questions', [GradeController::class, 'index'])->name('grade.questions');
        Route::post('/grade', [GradeController::class, 'store'])->name('grade.store');
    });
});

require __DIR__.'/settings.php';
