<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use App\Models\User;

class AttendanceSessionPolicy
{
    /**
     * Whether the user may open/view/manage attendance for the given class:
     * admins always, teachers only for classes they teach (active year) or
     * where they are the homeroom teacher.
     */
    public function manageClass(User $user, SchoolClass $class): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isTeacher() || ! $user->teacher) {
            return false;
        }

        $year = SchoolYear::query()->where('is_active', true)->first();

        $teachesClass = TeachingAssignment::query()
            ->where('teacher_id', $user->teacher->id)
            ->when($year, fn ($query) => $query->where('school_year_id', $year->id))
            ->where('class_id', $class->id)
            ->exists();

        return $teachesClass || $class->homeroom_teacher_id === $user->teacher->id;
    }

    public function close(User $user, AttendanceSession $session): bool
    {
        return $this->manageClass($user, $session->schoolClass);
    }

    public function update(User $user, AttendanceSession $session): bool
    {
        return $this->manageClass($user, $session->schoolClass);
    }

    public function checkIn(User $user, AttendanceSession $session): bool
    {
        if (! $session->isOpen()) {
            return false;
        }

        if (! $user->isStudent() || ! $user->student) {
            return false;
        }

        if ($session->class_id !== $user->student->class_id) {
            return false;
        }

        if ($session->date?->toDateString() !== now()->toDateString()) {
            return false;
        }

        return ! AttendanceRecord::query()
            ->where('attendance_session_id', $session->id)
            ->where('student_id', $user->student->id)
            ->exists();
    }
}
