<?php

namespace App\Models;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'teacher_id',
    'class_id',
    'subject_id',
    'school_year_id',
    'title',
    'description',
    'type',
    'deadline',
    'grade_weight',
    'late_deduction_percent',
    'status',
    'allow_resubmit',
    'attachment_path',
])]
class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AssignmentType::class,
            'status' => AssignmentStatus::class,
            'deadline' => 'datetime',
            'grade_weight' => 'decimal:2',
            'late_deduction_percent' => 'integer',
            'allow_resubmit' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(AssignmentQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function isDraft(): bool
    {
        return $this->status === AssignmentStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === AssignmentStatus::Published;
    }

    public function isClosed(): bool
    {
        return $this->status === AssignmentStatus::Closed;
    }

    public function isOverdue(): bool
    {
        return $this->deadline !== null && $this->deadline->isPast();
    }
}
