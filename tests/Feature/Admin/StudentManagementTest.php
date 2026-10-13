<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeClass(): SchoolClass
    {
        $year = SchoolYear::factory()->active()->create();

        return SchoolClass::factory()->create(['school_year_id' => $year->id]);
    }

    public function test_admin_can_view_the_student_create_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.students.create'))
            ->assertOk()
            ->assertSee('Tambah Siswa');
    }

    public function test_admin_can_view_the_student_edit_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $student = Student::factory()->create(['nis' => '2026555']);

        $this->actingAs($admin)
            ->get(route('admin.students.edit', $student))
            ->assertOk()
            ->assertSee($student->user->name)
            ->assertSee('2026555');
    }

    public function test_admin_can_create_a_student_with_an_auto_generated_account(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $class = $this->makeClass();

        $this->actingAs($admin)
            ->post(route('admin.students.store'), [
                'name' => 'Siti Aminah',
                'nis' => '2026123',
                'nisn' => '0099988877',
                'gender' => 'P',
                'birth_date' => '2008-05-04',
                'class_id' => $class->id,
                'email' => 'siti@sekolah.test',
                'phone' => '081200000001',
            ])
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $user = User::query()->where('username', '2026123')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertTrue(Hash::check('password', $user->password));

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'class_id' => $class->id,
            'nis' => '2026123',
            'nisn' => '0099988877',
            'gender' => 'P',
            'is_active' => true,
        ]);
    }

    public function test_duplicate_nis_is_rejected(): void
    {
        $admin = User::factory()->asAdmin()->create();
        Student::factory()->create(['nis' => '2026001']);

        $this->actingAs($admin)
            ->from(route('admin.students.create'))
            ->post(route('admin.students.store'), [
                'name' => 'Siswa Baru',
                'nis' => '2026001',
            ])
            ->assertSessionHasErrors('nis');
    }

    public function test_admin_can_update_a_student_and_the_username_follows_the_nis(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $class = $this->makeClass();
        $student = Student::factory()->create(['nis' => '2026002']);

        $this->actingAs($admin)
            ->put(route('admin.students.update', $student), [
                'name' => 'Nama Diperbarui',
                'nis' => '2026999',
                'nisn' => '0012345678',
                'gender' => 'L',
                'class_id' => $class->id,
                'email' => 'baru@sekolah.test',
            ])
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'nis' => '2026999',
            'class_id' => $class->id,
            'gender' => 'L',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $student->user_id,
            'name' => 'Nama Diperbarui',
            'username' => '2026999',
            'email' => 'baru@sekolah.test',
        ]);
    }

    public function test_admin_can_toggle_a_student_active_status(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $student = Student::factory()->create();

        $this->assertTrue($student->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.students.toggle-status', $student))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($student->fresh()->is_active);
        $this->assertFalse($student->user->fresh()->is_active);
    }

    public function test_class_detail_page_lists_its_students(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $class = $this->makeClass();
        $students = Student::factory()->count(3)->create(['class_id' => $class->id]);

        $this->actingAs($admin)
            ->get(route('admin.classes.show', $class))
            ->assertOk()
            ->assertSee($students->first()->user->name);
    }

    public function test_non_admin_cannot_manage_students(): void
    {
        $studentUser = User::factory()->asStudent()->create();
        $student = Student::factory()->create();

        $this->actingAs($studentUser)
            ->get(route('admin.students.create'))
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->post(route('admin.students.store'), [
                'name' => 'Curang',
                'nis' => '2026009',
            ])
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->put(route('admin.students.update', $student), [
                'name' => 'Curang',
                'nis' => $student->nis,
            ])
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->patch(route('admin.students.toggle-status', $student))
            ->assertForbidden();
    }
}
