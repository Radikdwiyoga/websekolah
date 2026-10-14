<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_teacher_create_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.teachers.create'))
            ->assertOk()
            ->assertSee('Tambah Guru');
    }

    public function test_admin_can_view_the_teacher_edit_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $teacher = Teacher::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.teachers.edit', $teacher))
            ->assertOk()
            ->assertSee($teacher->user->name)
            ->assertSee('Penugasan Mengajar');
    }

    public function test_admin_can_create_a_teacher_with_an_auto_generated_account(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.teachers.store'), [
                'name' => 'Budi Santoso',
                'nip' => '1987654321',
                'gender' => 'L',
                'phone' => '081200000002',
                'email' => 'budi@sekolah.test',
                'address' => 'Jl. Merdeka No. 1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user = User::query()->where('username', 'budi.santoso')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Teacher, $user->role);
        $this->assertTrue(Hash::check('password', $user->password));

        $this->assertDatabaseHas('teachers', [
            'user_id' => $user->id,
            'nip' => '1987654321',
            'gender' => 'L',
            'phone' => '081200000002',
            'is_active' => true,
        ]);
    }

    public function test_duplicate_teacher_name_gets_a_unique_username(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'name' => 'Andi Wijaya',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.teachers.store'), [
            'name' => 'Andi Wijaya',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['username' => 'andi.wijaya']);
        $this->assertDatabaseHas('users', ['username' => 'andi.wijaya2']);
    }

    public function test_admin_can_update_a_teacher(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $teacher = Teacher::factory()->create(['nip' => '111']);

        $this->actingAs($admin)
            ->put(route('admin.teachers.update', $teacher), [
                'name' => 'Nama Guru Baru',
                'nip' => '222',
                'gender' => 'P',
                'email' => 'guru.baru@sekolah.test',
            ])
            ->assertRedirect(route('admin.teachers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'nip' => '222',
            'gender' => 'P',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $teacher->user_id,
            'name' => 'Nama Guru Baru',
            'email' => 'guru.baru@sekolah.test',
        ]);
    }

    public function test_toggling_a_teacher_syncs_the_login_account(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $teacher = Teacher::factory()->create(['is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.teachers.toggle-status', $teacher))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($teacher->fresh()->is_active);
        $this->assertFalse($teacher->user->fresh()->is_active);
    }

    public function test_admin_can_assign_multiple_subjects_and_remove_an_assignment(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $teacher = Teacher::factory()->create();
        $year = SchoolYear::factory()->active()->create();
        $class = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        $firstSubject = Subject::factory()->create();
        $secondSubject = Subject::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.teachers.assign', $teacher), [
                'subject_ids' => [$firstSubject->id, $secondSubject->id],
                'class_id' => $class->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $firstSubject->id,
            'school_year_id' => $year->id,
        ]);

        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $secondSubject->id,
            'school_year_id' => $year->id,
        ]);

        // Re-posting the same subjects should not create duplicates.
        $this->actingAs($admin)
            ->post(route('admin.teachers.assign', $teacher), [
                'subject_ids' => [$firstSubject->id, $secondSubject->id],
                'class_id' => $class->id,
            ])
            ->assertRedirect();

        $this->assertSame(2, TeachingAssignment::query()->where('teacher_id', $teacher->id)->count());

        $assignment = TeachingAssignment::query()->firstOrFail();

        $this->actingAs($admin)
            ->delete(route('admin.teachers.unassign', [$teacher, $assignment]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('teaching_assignments', ['id' => $assignment->id]);
    }

    public function test_non_admin_cannot_manage_teachers(): void
    {
        $studentUser = User::factory()->asStudent()->create();
        $teacher = Teacher::factory()->create();

        $this->actingAs($studentUser)
            ->get(route('admin.teachers.create'))
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->post(route('admin.teachers.store'), ['name' => 'Curang'])
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->patch(route('admin.teachers.toggle-status', $teacher))
            ->assertForbidden();
    }
}
