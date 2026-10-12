<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || ($user->isTeacher() && $user->teacher !== null);
    }

    /**
     * Whether a teacher may create an assignment for the given class and
     * subject: admins always, teachers only when they teach the pair in the
     * active school year or are the class homeroom teacher.
     */
    public function createForClass(User $user, SchoolClass $class, Subject $subject): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isTeacher() || ! $user->teacher) {
            return false;
        }

        if ($class->homeroom_teacher_id === $user->teacher->id) {
            return true;
        }

        $year = SchoolYear::query()->where('is_active', true)->first();

        return TeachingAssignment::query()
            ->where('teacher_id', $user->teacher->id)
            ->when($year, fn ($query) => $query->where('school_year_id', $year->id))
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->exists();
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTeacher() && $user->teacher && $assignment->teacher_id === $user->teacher->id) {
            return true;
        }

        if ($user->isStudent() && $user->student) {
            return $assignment->isPublished()
                && $assignment->class_id === $user->student->class_id;
        }

        return false;
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isAdmin()
            || ($user->isTeacher() && $user->teacher && $assignment->teacher_id === $user->teacher->id);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function publish(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function close(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function manageQuestions(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment) && $assignment->isDraft();
    }

    public function submit(User $user, Assignment $assignment): bool
    {
        if (! $user->isStudent() || ! $user->student) {
            return false;
        }

        if (! $assignment->isPublished()) {
            return false;
        }

        if ($assignment->class_id !== $user->student->class_id) {
            return false;
        }

        if (! $assignment->allow_resubmit) {
            $existing = $assignment->submissions()
                ->where('student_id', $user->student->id)
                ->first();

            if ($existing && $existing->submitted_at !== null) {
                return false;
            }
        }

        return true;
    }

    public function grade(User $user, Assignment $assignment): bool
    {
        return $this->update($user, $assignment);
    }

    public function viewSubmission(User $user, AssignmentSubmission $submission): bool
    {
        if ($this->grade($user, $submission->assignment)) {
            return true;
        }

        return $user->isStudent()
            && $user->student
            && $submission->student_id === $user->student->id;
    }
}
