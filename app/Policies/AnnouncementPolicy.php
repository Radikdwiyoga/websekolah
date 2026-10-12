<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Services\AnnouncementService;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any signed-in user may read an announcement that is targeted at them.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        return app(AnnouncementService::class)->isVisibleTo($announcement, $user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->isAdmin();
    }

    public function publish(User $user, Announcement $announcement): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Announcement $announcement): bool
    {
        return $user->isAdmin();
    }
}
