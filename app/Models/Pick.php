<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pick extends Model
{
    protected $fillable = [
        'participant_id',
        'question_id',
        'answer_id',
        'tiebreaker_answer',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function isCorrect(): bool
    {
        if (! $this->question->isGraded()) {
            return false;
        }

        if ($this->question->is_tiebreaker) {
            return false; // Tiebreaker doesn't count as correct/incorrect
        }

        return $this->answer && $this->answer->is_correct === true;
    }
}
