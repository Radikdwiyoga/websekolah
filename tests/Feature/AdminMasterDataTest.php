<?php

namespace Tests\Feature;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_master_data_pages(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $year = SchoolYear::factory()->active()->create();
        $class = SchoolClass::factory()->create(['school_year_id' => $year->id]);
        Student::factory()->create(['class_id' => $class->id]);
        Teacher::factory()->create();
        Subject::factory()->create();

        $routes = [
            'admin.students.index',
            'admin.teachers.index',
            'admin.guardians.index',
            'admin.classes.index',
            'admin.subjects.index',
            'admin.school-years.index',
            'admin.attendance.index',
            'admin.assignments.index',
            'admin.maintenance.index',
            'admin.system-logs.index',
            'profile.show',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_non_admin_cannot_access_admin_master_data(): void
    {
        $student = User::factory()->asStudent()->create();

        $this->actingAs($student)
            ->get(route('admin.students.index'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('admin.maintenance.index'))
            ->assertForbidden();
    }

    public function test_public_can_view_public_announcement_and_image(): void
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('kegiatan.jpg');
        $path = $file->store('announcements', 'local');

        $announcement = Announcement::factory()->create([
            'title' => 'Pengumuman Penting MPLS',
            'status' => AnnouncementStatus::Published,
            'target_type' => AnnouncementTarget::All,
            'published_at' => now()->subHour(),
            'attachment_path' => $path,
        ]);

        // Homepage shows announcement with file route
        $this->get('/')
            ->assertOk()
            ->assertSee('Pengumuman Penting MPLS')
            ->assertSee(route('announcements.file', $announcement));

        // Public can view announcement detail without login
        $this->get(route('announcements.show', $announcement))
            ->assertOk()
            ->assertSee('Pengumuman Penting MPLS');

        // Public can view the image file without login
        $this->get(route('announcements.file', $announcement))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }
}
