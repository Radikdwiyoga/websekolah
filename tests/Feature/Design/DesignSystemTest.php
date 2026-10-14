<?php

namespace Tests\Feature\Design;

use App\Enums\SettingType;
use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shell_renders_brand_and_logout_for_every_role(): void
    {
        ApplicationSetting::query()->updateOrCreate(
            ['key' => 'school_name'],
            ['value' => 'SMA Nusantara', 'type' => SettingType::Text, 'group' => 'school'],
        );

        $users = [
            User::factory()->asStudent()->create(),
            User::factory()->asTeacher()->create(),
            User::factory()->asParent()->create(),
            User::factory()->asAdmin()->create(),
        ];

        foreach ($users as $user) {
            $this->actingAs($user)
                ->get(route($user->role->dashboardRoute()))
                ->assertOk()
                ->assertSee('SMA Nusantara')
                ->assertSee('Keluar');
        }
    }

    public function test_sidebar_shows_items_specific_to_each_role(): void
    {
        $cases = [
            'student' => ['route' => 'dashboard.student', 'expect' => 'Kehadiran Saya', 'absent' => 'Data Siswa'],
            'teacher' => ['route' => 'dashboard.teacher', 'expect' => 'Kelas Saya', 'absent' => 'Data Orang Tua'],
            'parent' => ['route' => 'dashboard.parent', 'expect' => 'Nilai Anak', 'absent' => 'Input Absensi'],
            'admin' => ['route' => 'dashboard.admin', 'expect' => 'System Log', 'absent' => 'Kehadiran Anak'],
        ];

        foreach ($cases as $role => $case) {
            // Build the user matching the expected role.
            $user = match ($role) {
                'student' => User::factory()->asStudent()->create(),
                'teacher' => User::factory()->asTeacher()->create(),
                'parent' => User::factory()->asParent()->create(),
                'admin' => User::factory()->asAdmin()->create(),
            };

            $response = $this->actingAs($user)->get(route($case['route']));

            $response->assertOk()
                ->assertSee($case['expect'], false)
                ->assertDontSee($case['absent'], false);
        }
    }

    public function test_profile_and_announcements_are_shared_across_roles(): void
    {
        $student = User::factory()->asStudent()->create();

        $this->actingAs($student)
            ->get(route('dashboard.student'))
            ->assertOk()
            ->assertSee('Pengumuman', false);
    }

    public function test_navigation_config_defines_valid_shape_for_every_role(): void
    {
        foreach (['student', 'teacher', 'parent', 'admin'] as $role) {
            $items = config('navigation.'.$role);

            $this->assertNotEmpty($items);

            foreach ($items as $item) {
                $this->assertArrayHasKey('label', $item);
                $this->assertArrayHasKey('icon', $item);
                $this->assertArrayHasKey('route', $item);
                $this->assertIsString($item['route']);
            }
        }
    }

    public function test_button_component_renders_variant_specific_classes(): void
    {
        $primary = Blade::render('<x-button>Simpan</x-button>');
        $this->assertStringContainsString('bg-primary-700 text-white', $primary);
        $this->assertStringContainsString('Simpan', $primary);
        $this->assertStringContainsString('</button>', $primary);

        $danger = Blade::render('<x-button variant="danger" type="submit">Hapus</x-button>');
        $this->assertStringContainsString('bg-danger-700 text-white', $danger);
        $this->assertStringContainsString('type="submit"', $danger);

        $link = Blade::render('<x-button href="/dashboard" variant="outline">Buka</x-button>');
        $this->assertStringContainsString('<a href="/dashboard"', $link);
    }

    public function test_badge_icon_and_alert_components_render(): void
    {
        $badge = Blade::render('<x-badge variant="success">Hadir</x-badge>');
        $this->assertStringContainsString('bg-success-100 text-success-700', $badge);
        $this->assertStringContainsString('Hadir', $badge);

        $icon = Blade::render('<x-icon name="dashboard" />');
        $this->assertStringContainsString('<svg', $icon);
        $this->assertStringContainsString('h-5 w-5', $icon);

        $alert = Blade::render('<x-alert tone="danger" title="Gagal">Tidak tersimpan</x-alert>');
        $this->assertStringContainsString('bg-danger-100', $alert);
        $this->assertStringContainsString('Gagal', $alert);
        $this->assertStringContainsString('Tidak tersimpan', $alert);
    }

    public function test_stat_card_and_empty_state_components_render(): void
    {
        $card = Blade::render('<x-stat-card label="Total Siswa" value="120" icon="students" />');
        $this->assertStringContainsString('Total Siswa', $card);
        $this->assertStringContainsString('120', $card);

        $empty = Blade::render('<x-empty-state title="Belum ada absensi">Data akan muncul di sini.</x-empty-state>');
        $this->assertStringContainsString('Belum ada absensi', $empty);
        $this->assertStringContainsString('Data akan muncul di sini.', $empty);
    }
}
