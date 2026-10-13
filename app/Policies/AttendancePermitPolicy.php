<?php

namespace App\Policies;

use App\Models\AttendancePermit;
use App\Models\AttendanceSession;
use App\Models\User;

class AttendancePermitPolicy
{
    /**
     * Students and parents may submit izin/sakit requests.
     */
    public function create(User $user): bool
    {
        return $user->isStudent() || $user->isParent();
    }

    /**
     * The permit owner (student), a linked parent, a class teacher, or an admin.
     */
    public function view(User $user, AttendancePermit $permit): bool
    {
        return $user->isAdmin()
            || $this->owns($user, $permit)
            || $this->review($user, $permit);
    }

    /**
     * Admins may review any permit; teachers only permits for classes they manage.
     */
    public function review(User $user, AttendancePermit $permit): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->isTeacher()) {
            return false;
        }

        $class = $permit->student?->schoolClass;

        if (! $class) {
            return false;
        }

        return $user->can('manageClass', [AttendanceSession::class, $class]);
    }

    /**
     * Only pending permits can be withdrawn, and only by their owner or an admin.
     */
    public function delete(User $user, AttendancePermit $permit): bool
    {
        if (! $permit->isPending()) {
            return false;
        }

        return $user->isAdmin() || $this->owns($user, $permit);
    }

    /**
     * Whether the user is the student behind the permit or one of that student's guardians.
     */
    private function owns(User $user, AttendancePermit $permit): bool
    {
        if ($permit->student?->user_id === $user->id) {
            return true;
        }

        if ($user->isParent() && $user->guardian) {
            return $user->guardian->students()->whereKey($permit->student_id)->exists();
        }

        return false;
    }
}
