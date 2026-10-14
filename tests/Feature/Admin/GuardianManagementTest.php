<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuardianManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_guardian_create_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.guardians.create'))
            ->assertOk()
            ->assertSee('Tambah Orang Tua');
    }

    public function test_admin_can_view_the_guardian_edit_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();
        $guardian->students()->attach($student->id, ['relation' => 'Ayah', 'is_primary' => true]);

        $this->actingAs($admin)
            ->get(route('admin.guardians.edit', $guardian))
            ->assertOk()
            ->assertSee($guardian->user->name)
            ->assertSee($student->user->name)
            ->assertSee('Siswa Terhubung');
    }

    public function test_admin_can_create_a_guardian_with_an_auto_generated_account(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.guardians.store'), [
                'name' => 'Melati Kusuma',
                'phone' => '081200000003',
                'occupation' => 'Wirausaha',
                'email' => 'melati@sekolah.test',
                'whatsapp_opt_in' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user = User::query()->where('username', 'melati.kusuma')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Parent, $user->role);
        $this->assertTrue(Hash::check('password', $user->password));

        $this->assertDatabaseHas('parents', [
            'user_id' => $user->id,
            'phone' => '081200000003',
            'occupation' => 'Wirausaha',
            'whatsapp_opt_in' => true,
        ]);
    }

    public function test_admin_can_link_a_student_to_a_guardian(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.guardians.attach-student', $guardian), [
                'student_id' => $student->id,
                'relation' => 'Ayah',
                'is_primary' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('guardian_student', [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
            'relation' => 'Ayah',
            'is_primary' => true,
        ]);
    }

    public function test_marking_a_guardian_as_primary_demotes_other_guardians(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $student = Student::factory()->create();
        $first = Guardian::factory()->create();
        $second = Guardian::factory()->create();

        $this->actingAs($admin)->post(route('admin.guardians.attach-student', $first), [
            'student_id' => $student->id,
            'is_primary' => '1',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('admin.guardians.attach-student', $second), [
            'student_id' => $student->id,
            'is_primary' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('guardian_student', [
            'guardian_id' => $first->id,
            'student_id' => $student->id,
            'is_primary' => false,
        ]);
        $this->assertDatabaseHas('guardian_student', [
            'guardian_id' => $second->id,
            'student_id' => $student->id,
            'is_primary' => true,
        ]);
    }

    public function test_admin_can_detach_a_student_from_a_guardian(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $guardian = Guardian::factory()->create();
        $student = Student::factory()->create();
        $guardian->students()->attach($student->id, ['relation' => 'Ibu']);

        $this->actingAs($admin)
            ->delete(route('admin.guardians.detach-student', [$guardian, $student]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('guardian_student', [
            'guardian_id' => $guardian->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_toggling_a_guardian_toggles_the_login_account(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $guardian = Guardian::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.guardians.toggle-status', $guardian))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFalse($guardian->user->fresh()->is_active);
    }

    public function test_non_admin_cannot_manage_guardians(): void
    {
        $studentUser = User::factory()->asStudent()->create();
        $guardian = Guardian::factory()->create();

        $this->actingAs($studentUser)
            ->get(route('admin.guardians.create'))
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->post(route('admin.guardians.store'), ['name' => 'Curang'])
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->patch(route('admin.guardians.toggle-status', $guardian))
            ->assertForbidden();
    }
}
