<?php

namespace App\Services;

use App\Enums\SystemLogStatus;
use App\Enums\UserRole;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuardianService
{
    public function __construct(
        private readonly SettingService $settings,
        private readonly UsernameGenerator $usernames,
    ) {}

    /**
     * Create the login account and parent/guardian profile.
     *
     * @param  array{name: string, email?: string|null, phone?: string|null, occupation?: string|null, address?: string|null, whatsapp_opt_in?: bool}  $data
     */
    public function create(array $data, User $actor): Guardian
    {
        $guardian = DB::transaction(function () use ($data): Guardian {
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $this->usernames->fromName($data['name'], 'orangtua'),
                'email' => $data['email'] ?? null,
                'password' => $this->defaultPassword(),
                'role' => UserRole::Parent,
                'is_active' => true,
            ]);

            return Guardian::query()->create([
                'user_id' => $user->id,
                'phone' => $data['phone'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'address' => $data['address'] ?? null,
                'whatsapp_opt_in' => $data['whatsapp_opt_in'] ?? true,
            ]);
        });

        $this->log($actor, 'create', 'Menambahkan orang tua/wali '.$guardian->user->name);

        return $guardian;
    }

    /**
     * @param  array{name: string, email?: string|null, phone?: string|null, occupation?: string|null, address?: string|null, whatsapp_opt_in?: bool}  $data
     */
    public function update(Guardian $guardian, array $data, User $actor): void
    {
        DB::transaction(function () use ($guardian, $data): void {
            $guardian->user()->update([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
            ]);

            $guardian->update([
                'phone' => $data['phone'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'address' => $data['address'] ?? null,
                'whatsapp_opt_in' => $data['whatsapp_opt_in'] ?? false,
            ]);
        });

        $this->log($actor, 'update', 'Memperbarui data orang tua/wali '.$guardian->user->name);
    }

    public function setActive(Guardian $guardian, bool $active, User $actor): void
    {
        $guardian->user()->update(['is_active' => $active]);

        $this->log(
            $actor,
            $active ? 'activate' : 'deactivate',
            ($active ? 'Mengaktifkan' : 'Menonaktifkan').' akun orang tua/wali '.$guardian->user->name,
        );
    }

    /**
     * Link a student to the guardian. When marked as primary, other guardians
     * of the same student are demoted.
     */
    public function attachStudent(Guardian $guardian, Student $student, ?string $relation, bool $isPrimary, User $actor): void
    {
        DB::transaction(function () use ($guardian, $student, $relation, $isPrimary): void {
            if ($isPrimary) {
                DB::table('guardian_student')
                    ->where('student_id', $student->id)
                    ->where('guardian_id', '!=', $guardian->id)
                    ->update(['is_primary' => false]);
            }

            $guardian->students()->syncWithoutDetaching([
                $student->id => [
                    'relation' => $relation,
                    'is_primary' => $isPrimary,
                ],
            ]);
        });

        $this->log($actor, 'attach-student', 'Menghubungkan '.$guardian->user->name.' dengan siswa '.$student->user->name);
    }

    public function detachStudent(Guardian $guardian, Student $student, User $actor): void
    {
        $guardian->students()->detach($student->id);

        $this->log($actor, 'detach-student', 'Memutuskan '.$guardian->user->name.' dari siswa '.$student->user->name);
    }

    private function defaultPassword(): string
    {
        return (string) $this->settings->get('default_user_password', 'password');
    }

    private function log(User $actor, string $action, string $description): void
    {
        SystemLog::query()->create([
            'user_id' => $actor->id,
            'module' => 'guardian',
            'action' => $action,
            'description' => $description,
            'status' => SystemLogStatus::Success,
        ]);
    }
}
