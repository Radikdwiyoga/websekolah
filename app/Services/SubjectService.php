<?php

namespace App\Services;

use App\Enums\SystemLogStatus;
use App\Models\Subject;
use App\Models\SystemLog;
use App\Models\User;

class SubjectService
{
    /**
     * @param  array{code: string, name: string, description?: string|null}  $data
     */
    public function create(array $data, User $actor): Subject
    {
        $subject = Subject::query()->create([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $this->log($actor, 'create', 'Menambahkan mata pelajaran '.$subject->name);

        return $subject;
    }

    /**
     * @param  array{code: string, name: string, description?: string|null}  $data
     */
    public function update(Subject $subject, array $data, User $actor): void
    {
        $subject->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $this->log($actor, 'update', 'Memperbarui mata pelajaran '.$subject->name);
    }

    private function log(User $actor, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'subject',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
