<?php

namespace App\Services;

use App\Enums\SystemLogStatus;
use App\Models\SchoolClass;
use App\Models\SystemLog;
use App\Models\User;

class SchoolClassService
{
    /**
     * @param  array{school_year_id: int, name: string, level?: string|null, capacity?: int|null, homeroom_teacher_id?: int|null}  $data
     */
    public function create(array $data, User $actor): SchoolClass
    {
        $class = SchoolClass::query()->create([
            'school_year_id' => $data['school_year_id'],
            'name' => $data['name'],
            'level' => $data['level'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'homeroom_teacher_id' => $data['homeroom_teacher_id'] ?? null,
        ]);

        $this->log($actor, 'create', 'Menambahkan kelas '.$class->name);

        return $class;
    }

    /**
     * @param  array{name: string, level?: string|null, capacity?: int|null, homeroom_teacher_id?: int|null}  $data
     */
    public function update(SchoolClass $class, array $data, User $actor): void
    {
        $class->update([
            'name' => $data['name'],
            'level' => $data['level'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'homeroom_teacher_id' => $data['homeroom_teacher_id'] ?? null,
        ]);

        $this->log($actor, 'update', 'Memperbarui data kelas '.$class->name);
    }

    private function log(User $actor, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'class',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
