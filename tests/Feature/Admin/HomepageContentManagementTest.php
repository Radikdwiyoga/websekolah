<?php

namespace Tests\Feature\Admin;

use App\Enums\SettingType;
use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_homepage_content_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.settings.homepage.edit'))
            ->assertOk()
            ->assertSee('Halaman Utama')
            ->assertSee('Keunggulan Sekolah')
            ->assertSee('Program')
            ->assertSee('data-repeater', false)
            ->assertSee('data-icon-select', false)
            ->assertSee('Buku / Pelajaran');
    }

    public function test_admin_can_update_the_homepage_content(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.homepage.update'), [
                'hero_title' => 'Selamat Datang di Sekolah Kami',
                'hero_subtitle' => 'Belajar dengan gembira setiap hari.',
                'advantages_heading' => 'Keunggulan Kami',
                'advantages_description' => 'Alasan orang tua memilih kami.',
                'advantages' => [
                    ['icon' => 'book', 'tone' => 'primary', 'title' => 'Guru Hebat', 'description' => 'Guru berpengalaman.'],
                ],
                'programs_heading' => 'Program Kami',
                'programs_description' => '',
                'programs' => [
                    ['icon' => 'students', 'tone' => 'success', 'title' => 'Ekstrakurikuler', 'description' => 'Banyak pilihan.'],
                ],
            ])
            ->assertRedirect(route('admin.settings.homepage.edit'));

        $this->assertSame('Selamat Datang di Sekolah Kami', $this->value('home_hero_title'));

        $advantages = json_decode((string) $this->value('home_advantages'), true);
        $this->assertSame('Guru Hebat', $advantages[0]['title']);
        $this->assertSame('primary', $advantages[0]['tone']);

        $programs = json_decode((string) $this->value('home_programs'), true);
        $this->assertSame('Ekstrakurikuler', $programs[0]['title']);
    }

    public function test_empty_repeater_rows_are_ignored(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.homepage.update'), [
                'hero_title' => 'Judul',
                'advantages' => [
                    ['icon' => 'check', 'tone' => 'primary', 'title' => '', 'description' => ''],
                    ['icon' => 'book', 'tone' => 'primary', 'title' => 'Hanya Satu', 'description' => ''],
                ],
                'programs' => [],
            ])
            ->assertSessionHasNoErrors();

        $advantages = json_decode((string) $this->value('home_advantages'), true);
        $this->assertCount(1, $advantages);
        $this->assertSame('Hanya Satu', $advantages[0]['title']);
    }

    public function test_a_feature_row_requires_a_title_when_partially_filled(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.homepage.edit'))
            ->put(route('admin.settings.homepage.update'), [
                'hero_title' => 'Judul',
                'advantages' => [
                    ['icon' => 'book', 'tone' => 'primary', 'title' => '', 'description' => 'Deskripsi tanpa judul.'],
                ],
            ])
            ->assertSessionHasErrors('advantages.0.title');
    }

    public function test_homepage_renders_custom_advantages_and_programs(): void
    {
        $this->setting('home_hero_title', 'Judul Halaman Kustom');
        $this->setting('home_advantages_heading', 'Keunggulan Kustom');
        $this->setting('home_advantages', json_encode([
            ['icon' => 'book', 'tone' => 'primary', 'title' => 'Guru Hebat', 'description' => 'Guru berpengalaman.'],
        ]), SettingType::Json, 'home');
        $this->setting('home_programs_heading', 'Program Kustom');
        $this->setting('home_programs', json_encode([
            ['icon' => 'students', 'tone' => 'success', 'title' => 'Ekstrakurikuler', 'description' => 'Banyak pilihan.'],
        ]), SettingType::Json, 'home');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Judul Halaman Kustom')
            ->assertSee('Keunggulan Kustom')
            ->assertSee('Guru Hebat')
            ->assertSee('Program Kustom')
            ->assertSee('Ekstrakurikuler');
    }

    public function test_non_admin_cannot_manage_homepage_content(): void
    {
        $student = User::factory()->asStudent()->create();

        $this->actingAs($student)
            ->get(route('admin.settings.homepage.edit'))
            ->assertForbidden();

        $this->actingAs($student)
            ->put(route('admin.settings.homepage.update'), ['hero_title' => 'Curang'])
            ->assertForbidden();
    }

    private function value(string $key): mixed
    {
        return ApplicationSetting::query()->where('key', $key)->value('value');
    }

    private function setting(string $key, mixed $value, SettingType $type = SettingType::Text, string $group = 'home'): ApplicationSetting
    {
        return ApplicationSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group],
        );
    }
}
