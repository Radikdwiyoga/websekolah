<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a parent / wali murid account profile.
 *
 * The database table is `parents`; the model is named Guardian because
 * `Parent` is a reserved word in PHP.
 */
#[Fillable(['user_id', 'phone', 'occupation', 'address', 'whatsapp_opt_in'])]
class Guardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parents';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'whatsapp_opt_in' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student', 'guardian_id', 'student_id')
            ->withPivot(['relation', 'is_primary'])
            ->withTimestamps();
    }
}
