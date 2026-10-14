<?php

namespace Tests\Feature\Admin;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_subject_create_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.subjects.create'))
            ->assertOk()
            ->assertSee('Tambah Mata Pelajaran');
    }

    public function test_admin_can_view_the_subject_edit_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $subject = Subject::factory()->create(['code' => 'MAT-99']);

        $this->actingAs($admin)
            ->get(route('admin.subjects.edit', $subject))
            ->assertOk()
            ->assertSee('MAT-99')
            ->assertSee($subject->name);
    }

    public function test_admin_can_create_a_subject(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.subjects.store'), [
                'code' => 'MAT-01',
                'name' => 'Matematika',
                'description' => 'Matematika wajib',
            ])
            ->assertRedirect(route('admin.subjects.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', [
            'code' => 'MAT-01',
            'name' => 'Matematika',
            'description' => 'Matematika wajib',
        ]);
    }

    public function test_duplicate_subject_code_is_rejected(): void
    {
        $admin = User::factory()->asAdmin()->create();
        Subject::factory()->create(['code' => 'MAT-01']);

        $this->actingAs($admin)
            ->from(route('admin.subjects.create'))
            ->post(route('admin.subjects.store'), [
                'code' => 'MAT-01',
                'name' => 'Matematika Lanjutan',
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_a_subject(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $subject = Subject::factory()->create(['code' => 'OLD-1']);

        $this->actingAs($admin)
            ->put(route('admin.subjects.update', $subject), [
                'code' => 'NEW-1',
                'name' => 'Nama Baru',
                'description' => 'Deskripsi baru',
            ])
            ->assertRedirect(route('admin.subjects.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'code' => 'NEW-1',
            'name' => 'Nama Baru',
            'description' => 'Deskripsi baru',
        ]);
    }

    public function test_non_admin_cannot_manage_subjects(): void
    {
        $studentUser = User::factory()->asStudent()->create();
        $subject = Subject::factory()->create();

        $this->actingAs($studentUser)
            ->get(route('admin.subjects.create'))
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->put(route('admin.subjects.update', $subject), ['code' => 'X', 'name' => 'Curang'])
            ->assertForbidden();
    }
}
