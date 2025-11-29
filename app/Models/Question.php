<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'event_id',
        'question_text',
        'order',
        'is_tiebreaker',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_tiebreaker' => 'boolean',
            'graded_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('order');
    }

    public function picks(): HasMany
    {
        return $this->hasMany(Pick::class);
    }

    public function correctAnswer(): ?Answer
    {
        return $this->answers()->where('is_correct', true)->first();
    }

    public function isGraded(): bool
    {
        return $this->graded_at !== null;
    }
}
