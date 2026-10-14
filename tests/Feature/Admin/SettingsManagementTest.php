<?php

namespace Tests\Feature\Admin;

use App\Enums\SettingType;
use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_settings_index(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $this->setting('school_name', 'SMA Contoh');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan Sistem')
            ->assertSee('Profil Sekolah');
    }

    public function test_admin_can_view_a_setting_edit_form(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $this->setting('default_user_password', 'password', group: 'account');

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', 'default_user_password'))
            ->assertOk()
            ->assertSee('default_user_password')
            ->assertSee('Kata Sandi Default');
    }

    public function test_admin_can_update_a_text_setting(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $this->setting('default_user_password', 'lama', group: 'account');

        $this->actingAs($admin)
            ->post(route('admin.settings.update', 'default_user_password'), ['value' => 'baru'])
            ->assertRedirect(route('admin.settings.index'));

        $this->assertDatabaseHas('application_settings', [
            'key' => 'default_user_password',
            'value' => 'baru',
        ]);
    }

    public function test_admin_can_update_a_boolean_setting(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $this->setting('maintenance_mode', '0', SettingType::Boolean, 'maintenance');

        $this->actingAs($admin)
            ->post(route('admin.settings.update', 'maintenance_mode'), ['value' => '1'])
            ->assertRedirect(route('admin.settings.index'));

        $setting = ApplicationSetting::query()->where('key', 'maintenance_mode')->firstOrFail();

        $this->assertSame('1', $setting->value);
        $this->assertTrue($setting->typedValue());
    }

    public function test_admin_can_view_the_school_profile_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.settings.school.edit'))
            ->assertOk()
            ->assertSee('Profil Sekolah')
            ->assertSee('Media Sosial');
    }

    public function test_admin_can_update_the_school_profile(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.school.update'), [
                'school_name' => 'SMA Negeri 2 Harapan',
                'school_npsn' => '20123457',
                'school_address' => 'Jl. Baru No. 1',
                'school_city' => 'Bandung',
                'school_phone' => '022-1234567',
                'school_email' => 'info@sma2.sch.id',
                'school_website' => 'https://sma2.sch.id',
                'school_social_instagram' => 'https://instagram.com/sman1',
            ])
            ->assertRedirect(route('admin.settings.school.edit'));

        $this->assertDatabaseHas('application_settings', ['key' => 'school_name', 'value' => 'SMA Negeri 2 Harapan']);
        $this->assertDatabaseHas('application_settings', ['key' => 'school_address', 'value' => 'Jl. Baru No. 1']);
        $this->assertDatabaseHas('application_settings', [
            'key' => 'school_social_instagram',
            'value' => 'https://instagram.com/sman1',
        ]);
    }

    public function test_admin_can_upload_a_school_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.school.update'), [
                'school_name' => 'SMA Logo',
                'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
            ])
            ->assertRedirect(route('admin.settings.school.edit'));

        $path = ApplicationSetting::query()->where('key', 'school_logo_path')->value('value');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_can_remove_the_school_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('school/old.png', 'content');

        $admin = User::factory()->asAdmin()->create();
        $this->setting('school_logo_path', 'school/old.png');

        $this->actingAs($admin)
            ->put(route('admin.settings.school.update'), [
                'school_name' => 'SMA Logo',
                'remove_logo' => '1',
            ])
            ->assertRedirect(route('admin.settings.school.edit'));

        $this->assertNull(ApplicationSetting::query()->where('key', 'school_logo_path')->value('value'));
        Storage::disk('public')->assertMissing('school/old.png');
    }

    public function test_school_name_is_required(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.school.edit'))
            ->put(route('admin.settings.school.update'), ['school_name' => ''])
            ->assertRedirect(route('admin.settings.school.edit'))
            ->assertSessionHasErrors('school_name');
    }

    public function test_school_website_must_be_a_valid_url(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.settings.school.edit'))
            ->put(route('admin.settings.school.update'), [
                'school_name' => 'SMA Contoh',
                'school_website' => 'bukan-url',
            ])
            ->assertRedirect(route('admin.settings.school.edit'))
            ->assertSessionHasErrors('school_website');
    }

    public function test_school_profile_is_shown_on_the_homepage(): void
    {
        $this->setting('school_name', 'SMP Cendekia Bangsa');
        $this->setting('school_social_instagram', 'https://instagram.com/cendekia');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('SMP Cendekia Bangsa')
            ->assertSee('https://instagram.com/cendekia');
    }

    public function test_non_admin_cannot_manage_settings(): void
    {
        $student = User::factory()->asStudent()->create();
        $this->setting('default_user_password', 'password', group: 'account');

        $this->actingAs($student)
            ->get(route('admin.settings.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.settings.school.edit'))
            ->assertForbidden();

        $this->actingAs($student)
            ->put(route('admin.settings.school.update'), ['school_name' => 'Curang'])
            ->assertForbidden();
    }

    private function setting(string $key, mixed $value, SettingType $type = SettingType::Text, string $group = 'school'): ApplicationSetting
    {
        return ApplicationSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group],
        );
    }
}
