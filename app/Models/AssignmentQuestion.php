<?php

namespace App\Models;

use App\Enums\AssignmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'assignment_id',
    'order',
    'type',
    'question_text',
    'options',
    'correct_answers',
    'points',
])]
class AssignmentQuestion extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AssignmentType::class,
            'options' => 'array',
            'correct_answers' => 'array',
            'order' => 'integer',
            'points' => 'decimal:2',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssignmentAnswer::class);
    }
}
