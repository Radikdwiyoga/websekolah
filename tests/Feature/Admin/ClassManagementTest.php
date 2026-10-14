<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_class_create_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        SchoolYear::factory()->active()->create();

        $this->actingAs($admin)
            ->get(route('admin.classes.create'))
            ->assertOk()
            ->assertSee('Tambah Kelas');
    }

    public function test_admin_can_view_the_class_edit_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $year = SchoolYear::factory()->active()->create();
        $class = SchoolClass::factory()->create(['school_year_id' => $year->id]);

        $this->actingAs($admin)
            ->get(route('admin.classes.edit', $class))
            ->assertOk()
            ->assertSee($class->name);
    }

    public function test_admin_can_create_a_class(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $year = SchoolYear::factory()->active()->create();
        $teacher = Teacher::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.classes.store'), [
                'school_year_id' => $year->id,
                'name' => 'X-3',
                'level' => 'X',
                'capacity' => 32,
                'homeroom_teacher_id' => $teacher->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classes', [
            'school_year_id' => $year->id,
            'name' => 'X-3',
            'level' => 'X',
            'capacity' => 32,
            'homeroom_teacher_id' => $teacher->id,
        ]);
    }

    public function test_duplicate_class_name_in_the_same_year_is_rejected(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $year = SchoolYear::factory()->active()->create();

        SchoolClass::factory()->create([
            'school_year_id' => $year->id,
            'name' => 'X-1',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.classes.create'))
            ->post(route('admin.classes.store'), [
                'school_year_id' => $year->id,
                'name' => 'X-1',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_a_class(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $year = SchoolYear::factory()->active()->create();
        $teacher = Teacher::factory()->create();
        $class = SchoolClass::factory()->create([
            'school_year_id' => $year->id,
            'name' => 'X-1',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.classes.update', $class), [
                'name' => 'X-1 Unggulan',
                'level' => 'X',
                'capacity' => 30,
                'homeroom_teacher_id' => $teacher->id,
            ])
            ->assertRedirect(route('admin.classes.show', $class))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'name' => 'X-1 Unggulan',
            'level' => 'X',
            'capacity' => 30,
            'homeroom_teacher_id' => $teacher->id,
            'school_year_id' => $year->id,
        ]);
    }

    public function test_non_admin_cannot_manage_classes(): void
    {
        $studentUser = User::factory()->asStudent()->create();
        $class = SchoolClass::factory()->create();

        $this->actingAs($studentUser)
            ->get(route('admin.classes.create'))
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->put(route('admin.classes.update', $class), ['name' => 'Curang'])
            ->assertForbidden();
    }
}
