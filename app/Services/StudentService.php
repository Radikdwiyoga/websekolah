<?php

namespace App\Services;

use App\Enums\SystemLogStatus;
use App\Enums\UserRole;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function __construct(private readonly SettingService $settings) {}

    /**
     * Create the login account and student record for a new student.
     *
     * @param  array{name: string, nis: string, nisn?: string|null, gender?: string|null, birth_date?: string|null, address?: string|null, email?: string|null, phone?: string|null, class_id?: int|null, password?: string|null}  $data
     */
    public function create(array $data, User $actor): Student
    {
        $student = DB::transaction(function () use ($data): Student {
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['nis'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => ($data['password'] ?? null) ?: $this->defaultPassword(),
                'role' => UserRole::Student,
                'is_active' => true,
            ]);

            return Student::query()->create([
                'user_id' => $user->id,
                'class_id' => $data['class_id'] ?? null,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => true,
            ]);
        });

        $this->log($actor, 'create', 'Menambahkan siswa '.$student->user->name.' (NIS '.$student->nis.')');

        return $student;
    }

    /**
     * Update the student record, keeping the login account (username = NIS) in sync.
     *
     * @param  array{name: string, nis: string, nisn?: string|null, gender?: string|null, birth_date?: string|null, address?: string|null, email?: string|null, phone?: string|null, class_id?: int|null, password?: string|null}  $data
     */
    public function update(Student $student, array $data, User $actor): void
    {
        DB::transaction(function () use ($student, $data): void {
            $userData = [
                'name' => $data['name'],
                'username' => $data['nis'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $student->user()->update($userData);

            $student->update([
                'class_id' => $data['class_id'] ?? null,
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        $this->log($actor, 'update', 'Memperbarui data siswa '.$student->user->name.' (NIS '.$student->nis.')');
    }

    /**
     * Activate or deactivate both the login account and the student record.
     */
    public function setActive(Student $student, bool $active, User $actor): void
    {
        DB::transaction(function () use ($student, $active): void {
            $student->update(['is_active' => $active]);
            $student->user()->update(['is_active' => $active]);
        });

        $this->log(
            $actor,
            $active ? 'activate' : 'deactivate',
            ($active ? 'Mengaktifkan' : 'Menonaktifkan').' siswa '.$student->user->name.' (NIS '.$student->nis.')',
        );
    }

    private function defaultPassword(): string
    {
        return (string) $this->settings->get('student_default_password', 'password');
    }

    private function log(User $actor, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'student',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
