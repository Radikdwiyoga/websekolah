<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameGenerator
{
    /**
     * Build a unique username from a person's name, e.g. "Budi Santoso" → "budi.santoso".
     */
    public function fromName(string $name, string $fallback = 'pengguna'): string
    {
        $base = Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '.')
            ->trim('.')
            ->value();

        if ($base === '') {
            $base = $fallback;
        }

        $username = $base;
        $suffix = 1;

        while ($this->exists($username)) {
            $suffix++;
            $username = $base.$suffix;
        }

        return $username;
    }

    private function exists(string $username): bool
    {
        return User::withTrashed()->where('username', $username)->exists();
    }
}
