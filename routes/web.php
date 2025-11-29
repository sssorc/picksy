<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\PreviewController;
use App\Http\Controllers\PublishController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Event Management
    Route::get('event', [EventController::class, 'edit'])->name('event.edit');
    Route::post('event', [EventController::class, 'store'])->name('event.store');

    // Questions Management
    Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::post('event/questions', [QuestionController::class, 'store'])->name('questions.store');

    // Publishing
    Route::get('publish', [PublishController::class, 'index'])->name('publish.index');
    Route::post('publish', [PublishController::class, 'store'])->name('publish.store');

    // Preview
    Route::get('preview/{slug}', [PreviewController::class, 'show'])->name('preview.show');
    Route::get('preview/{slug}/picks', [PreviewController::class, 'picks'])->name('preview.picks');
    Route::get('preview/{slug}/leaderboard', [PreviewController::class, 'leaderboard'])->name('preview.leaderboard');
});

// Stripe Webhook (no auth/CSRF needed)
Route::post('stripe/webhook', [PublishController::class, 'webhook'])->withoutMiddleware(['web']);

require __DIR__.'/settings.php';

