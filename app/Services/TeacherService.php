<?php

namespace App\Services;

use App\Enums\SystemLogStatus;
use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\SystemLog;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeacherService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly UsernameGenerator $usernames,
    ) {}

    /**
     * Create the login account and teacher profile.
     *
     * @param  array{name: string, email?: string|null, nip?: string|null, gender?: string|null, phone?: string|null, birth_date?: string|null, address?: string|null}  $data
     */
    public function create(array $data, User $actor): Teacher
    {
        $teacher = DB::transaction(function () use ($data): Teacher {
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $this->usernames->fromName($data['name'], 'guru'),
                'email' => $data['email'] ?? null,
                'password' => $this->defaultPassword(),
                'role' => UserRole::Teacher,
                'is_active' => true,
            ]);

            return Teacher::query()->create([
                'user_id' => $user->id,
                'nip' => $data['nip'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => true,
            ]);
        });

        $this->log($actor, 'create', 'Menambahkan guru '.$teacher->user->name);

        return $teacher;
    }

    /**
     * @param  array{name: string, email?: string|null, nip?: string|null, gender?: string|null, phone?: string|null, birth_date?: string|null, address?: string|null}  $data
     */
    public function update(Teacher $teacher, array $data, User $actor): void
    {
        DB::transaction(function () use ($teacher, $data): void {
            $teacher->user()->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
            ]);

            $teacher->update([
                'nip' => $data['nip'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        $this->log($actor, 'update', 'Memperbarui data guru '.$teacher->user->name);
    }

    public function setActive(Teacher $teacher, bool $active, User $actor): void
    {
        DB::transaction(function () use ($teacher, $active): void {
            $teacher->update(['is_active' => $active]);
            $teacher->user()->update(['is_active' => $active]);
        });

        $this->log(
            $actor,
            $active ? 'activate' : 'deactivate',
            ($active ? 'Mengaktifkan' : 'Menonaktifkan').' guru '.$teacher->user->name,
        );
    }

    /**
     * Assign one or more subjects to the teacher for a single class.
     *
     * @param  array<int, int|string>  $subjectIds
     * @return int The number of new assignments created.
     */
    public function assignTeachings(Teacher $teacher, SchoolClass $class, array $subjectIds, User $actor): int
    {
        $created = 0;

        DB::transaction(function () use ($teacher, $class, $subjectIds, &$created): void {
            foreach (array_unique($subjectIds) as $subjectId) {
                $assignment = $teacher->teachingAssignments()->firstOrCreate([
                    'subject_id' => $subjectId,
                    'class_id' => $class->id,
                    'school_year_id' => $class->school_year_id,
                ]);

                if ($assignment->wasRecentlyCreated) {
                    $created++;
                }
            }
        });

        $this->log(
            $actor,
            'assign',
            'Menambahkan '.$created.' penugasan mengajar untuk '.$teacher->user->name.' di '.$class->name,
        );

        return $created;
    }

    public function removeTeaching(TeachingAssignment $assignment, User $actor): void
    {
        $description = 'Menghapus penugasan '
            .($assignment->teacher?->user?->name ?? 'guru')
            .': '.($assignment->subject?->name ?? 'mapel')
            .' di '.($assignment->schoolClass?->name ?? 'kelas');

        $assignment->delete();

        $this->log($actor, 'unassign', $description);
    }

    private function defaultPassword(): string
    {
        return (string) $this->settings->get('default_user_password', 'password');
    }

    private function log(User $actor, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'teacher',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
