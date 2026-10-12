<?php

namespace Tests\Feature\Public;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget as AnnouncementTargetEnum;
use App\Models\Announcement;
use App\Models\ApplicationSetting;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_when_no_data_exists(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Masuk')
            ->assertSee('Bersama Membangun Generasi Masa Depan');
    }

    public function test_homepage_renders_school_profile_from_settings(): void
    {
        ApplicationSetting::factory()->create(['key' => 'school_name', 'value' => 'SMP Cendekia Bangsa']);
        ApplicationSetting::factory()->create(['key' => 'school_address', 'value' => 'Jl. Melati No. 5, Kota Harapan']);
        ApplicationSetting::factory()->create(['key' => 'school_phone', 'value' => '021-5551234']);
        ApplicationSetting::factory()->create(['key' => 'school_email', 'value' => 'info@cendekia.sch.id']);
        ApplicationSetting::factory()->create(['key' => 'school_npsn', 'value' => '20129999']);
        ApplicationSetting::factory()->create(['key' => 'headmaster_name', 'value' => 'Ibu Siti Rahayu, M.Pd.']);

        $this->get('/')
            ->assertOk()
            ->assertSee('SMP Cendekia Bangsa')
            ->assertSee('Jl. Melati No. 5, Kota Harapan')
            ->assertSee('021-5551234')
            ->assertSee('info@cendekia.sch.id')
            ->assertSee('20129999')
            ->assertSee('Ibu Siti Rahayu, M.Pd.');
    }

    public function test_homepage_shows_only_public_published_announcements(): void
    {
        Announcement::factory()->published()->important()->create([
            'title' => 'Penerimaan Siswa Baru Resmi Dibuka',
            'target_type' => AnnouncementTargetEnum::All,
        ]);
        Announcement::factory()->published()->create([
            'title' => 'Rapat Wali Murid Kelas X-1',
            'target_type' => AnnouncementTargetEnum::ClassRoom,
        ]);
        Announcement::factory()->create([
            'title' => 'Draft Belum Terbit',
            'status' => AnnouncementStatus::Draft,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Penerimaan Siswa Baru Resmi Dibuka')
            ->assertDontSee('Rapat Wali Murid Kelas X-1')
            ->assertDontSee('Draft Belum Terbit');
    }

    public function test_homepage_shows_statistics_from_real_data(): void
    {
        Student::factory()->count(3)->create();
        Teacher::factory()->count(2)->create();
        SchoolClass::factory()->count(4)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Siswa Aktif')
            ->assertSee('Guru')
            ->assertSee('Rombongan Belajar')
            ->assertSee('Pengumuman');
    }

    public function test_homepage_renders_navigation_and_login_entry(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Beranda')
            ->assertSee('Tentang')
            ->assertSee('Program')
            ->assertSee('Berita')
            ->assertSee('Kontak')
            ->assertSee('Masuk');
    }

    public function test_homepage_shows_maintenance_banner_when_enabled(): void
    {
        ApplicationSetting::factory()->boolean(true)->create(['key' => 'maintenance_mode']);
        ApplicationSetting::factory()->create(['key' => 'maintenance_message', 'value' => 'Sedang diperbarui.']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Mode Pemeliharaan')
            ->assertSee('Sedang diperbarui.');
    }
}
