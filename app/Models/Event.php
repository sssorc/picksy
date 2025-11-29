<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class Event extends Model
{
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

    public function setGradingPasswordAttribute(?string $value): void
    {
        $this->attributes['grading_password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedGradingPasswordAttribute(): ?string
    {
        return $this->grading_password ? Crypt::decryptString($this->grading_password) : null;
    }

    public function verifyGradingPassword(string $password): bool
    {
        return $this->decrypted_grading_password === $password;
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
}
