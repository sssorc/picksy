<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'first_name',
        'last_name',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function picks(): HasMany
    {
        return $this->hasMany(Pick::class);
    }

    public function hasSubmittedPicks(): bool
    {
        return $this->submitted_at !== null;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function calculateScore(): int
    {
        return $this->picks()
            ->whereHas('question', fn ($query) => $query->whereNotNull('graded_at'))
            ->whereHas('answer', fn ($query) => $query->where('is_correct', true))
            ->count();
    }
}
