<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolYearManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_school_year_create_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.school-years.create'))
            ->assertOk()
            ->assertSee('Tambah Tahun Ajaran');
    }

    public function test_admin_can_view_the_school_year_edit_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $year = SchoolYear::factory()->create(['name' => '2024/2025', 'semester' => 'ganjil']);

        $this->actingAs($admin)
            ->get(route('admin.school-years.edit', $year))
            ->assertOk()
            ->assertSee('2024/2025');
    }

    public function test_admin_can_create_a_school_year(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.school-years.store'), [
                'name' => '2027/2028',
                'semester' => 'ganjil',
                'start_date' => '2027-07-01',
                'end_date' => '2028-06-30',
            ])
            ->assertRedirect(route('admin.school-years.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('school_years', [
            'name' => '2027/2028',
            'semester' => 'ganjil',
            'is_active' => false,
        ]);
    }

    public function test_duplicate_school_year_and_semester_is_rejected(): void
    {
        $admin = User::factory()->asAdmin()->create();
        SchoolYear::factory()->create(['name' => '2026/2027', 'semester' => 'ganjil']);

        $this->actingAs($admin)
            ->from(route('admin.school-years.create'))
            ->post(route('admin.school-years.store'), [
                'name' => '2026/2027',
                'semester' => 'ganjil',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_a_school_year(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $year = SchoolYear::factory()->create(['name' => '2025/2026', 'semester' => 'ganjil']);

        $this->actingAs($admin)
            ->put(route('admin.school-years.update', $year), [
                'name' => '2025/2026',
                'semester' => 'genap',
                'start_date' => '2026-01-05',
                'end_date' => '2026-06-30',
            ])
            ->assertRedirect(route('admin.school-years.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('school_years', [
            'id' => $year->id,
            'semester' => 'genap',
        ]);

        $this->assertSame('2026-01-05', $year->fresh()->start_date->toDateString());
    }

    public function test_activating_a_school_year_deactivates_the_others(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $first = SchoolYear::factory()->create(['is_active' => true]);
        $second = SchoolYear::factory()->create(['is_active' => false]);

        $this->actingAs($admin)
            ->post(route('admin.school-years.activate', $second))
            ->assertRedirect(route('admin.school-years.index'))
            ->assertSessionHas('success');

        $this->assertFalse($first->fresh()->is_active);
        $this->assertTrue($second->fresh()->is_active);
        $this->assertSame(1, SchoolYear::query()->where('is_active', true)->count());
    }

    public function test_non_admin_cannot_manage_school_years(): void
    {
        $studentUser = User::factory()->asStudent()->create();
        $year = SchoolYear::factory()->create();

        $this->actingAs($studentUser)
            ->get(route('admin.school-years.create'))
            ->assertForbidden();

        $this->actingAs($studentUser)
            ->put(route('admin.school-years.update', $year), ['name' => 'X', 'semester' => 'ganjil'])
            ->assertForbidden();
    }
}
