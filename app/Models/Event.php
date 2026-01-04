<?php

namespace App\Models;

use Barzo\Password\Generator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->grading_password)) {
                $event->grading_password = Generator::generateEn(2, '-');
            }
        });
    }

    protected $fillable = [
        'user_id',
        'title',
        'intro_text',
        'slug',
        'password',
        'grading_password',
        'start_datetime',
        'is_published',
        'published_at',
        'payment_intent_id',
        'amount_paid',
        'max_entries',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function verifyGradingPassword(string $password): bool
    {
        return $this->grading_password === $password;
    }

    public function hasStarted(): bool
    {
        return now()->gte($this->start_datetime);
    }

    public function canAcceptPicks(): bool
    {
        return $this->is_published && $this->hasStarted() && ! $this->hasAnyGradedQuestions();
    }

    public function hasAnyGradedQuestions(): bool
    {
        return $this->questions()->whereNotNull('graded_at')->exists();
    }

    public function hasReachedMaxEntries(): bool
    {
        if ($this->max_entries === 0) {
            return false; // Unlimited entries
        }

        $submittedCount = $this->participants()->whereNotNull('submitted_at')->count();

        return $submittedCount >= $this->max_entries;
    }

    public function getSubmittedEntriesCount(): int
    {
        return $this->participants()->whereNotNull('submitted_at')->count();
    }
}
