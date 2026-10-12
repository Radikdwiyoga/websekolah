<?php

namespace Tests\Feature\Announcement;

use App\Enums\AnnouncementStatus;
use App\Enums\AnnouncementTarget;
use App\Models\Announcement;
use App\Models\AnnouncementTarget as AnnouncementTargetModel;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_sees_matching_targets(): void
    {
        [, $classA, $classB] = $this->makeClasses();
        $student = Student::factory()->create(['class_id' => $classA->id]);

        $all = $this->announcement(AnnouncementTarget::All);
        $students = $this->announcement(AnnouncementTarget::Students);
        $parents = $this->announcement(AnnouncementTarget::Parents);
        $ownClass = $this->announcement(AnnouncementTarget::ClassRoom, $classA);
        $otherClass = $this->announcement(AnnouncementTarget::ClassRoom, $classB);

        $this->actingAs($student->user)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee($all->title)
            ->assertSee($students->title)
            ->assertSee($ownClass->title)
            ->assertDontSee($parents->title)
            ->assertDontSee($otherClass->title);
    }

    public function test_parent_sees_class_announcement_for_their_child(): void
    {
        [, $classA, $classB] = $this->makeClasses();
        $student = Student::factory()->create(['class_id' => $classA->id]);
        $guardian = Guardian::factory()->create();
        $guardian->students()->attach($student->id, ['relation' => 'Ibu', 'is_primary' => true]);

        $ownClass = $this->announcement(AnnouncementTarget::ClassRoom, $classA);
        $otherClass = $this->announcement(AnnouncementTarget::ClassRoom, $classB);
        $studentsOnly = $this->announcement(AnnouncementTarget::Students);

        $this->actingAs($guardian->user)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee($ownClass->title)
            ->assertDontSee($otherClass->title)
            ->assertDontSee($studentsOnly->title);
    }

    public function test_admin_sees_every_published_announcement(): void
    {
        [, $classA] = $this->makeClasses();
        $admin = User::factory()->asSuperAdmin()->create();

        $all = $this->announcement(AnnouncementTarget::All);
        $students = $this->announcement(AnnouncementTarget::Students);
        $classAnnouncement = $this->announcement(AnnouncementTarget::ClassRoom, $classA);
        $draft = Announcement::factory()->create(['title' => 'Draf Internal']);

        $this->actingAs($admin)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee($all->title)
            ->assertSee($students->title)
            ->assertSee($classAnnouncement->title)
            ->assertDontSee($draft->title);
    }

    public function test_guest_is_redirected_from_announcements(): void
    {
        $this->get(route('announcements.index'))->assertRedirect(route('login'));
    }

    public function test_student_cannot_open_out_of_scope_announcement(): void
    {
        [, $classA, $classB] = $this->makeClasses();
        $student = Student::factory()->create(['class_id' => $classA->id]);
        $otherClass = $this->announcement(AnnouncementTarget::ClassRoom, $classB);

        $this->actingAs($student->user)
            ->get(route('announcements.show', $otherClass))
            ->assertForbidden();
    }

    public function test_opening_an_announcement_records_a_read(): void
    {
        [, $classA] = $this->makeClasses();
        $student = Student::factory()->create(['class_id' => $classA->id]);
        $announcement = $this->announcement(AnnouncementTarget::Students);

        $this->actingAs($student->user)
            ->get(route('announcements.show', $announcement))
            ->assertOk();

        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id' => $student->user_id,
        ]);
    }

    public function test_admin_can_create_published_announcement_with_whatsapp(): void
    {
        $admin = User::factory()->asSuperAdmin()->create();
        $recipient = User::factory()->asStudent()->create(['phone' => '081100000001']);
        Student::factory()->create(['user_id' => $recipient->id]);

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => 'Libur Semester Ganjil',
                'body' => 'Sekolah diliburkan selama dua minggu.',
                'target_type' => 'all',
                'status' => 'published',
                'is_important' => '1',
                'send_whatsapp' => '1',
            ])
            ->assertRedirect();

        $announcement = Announcement::query()->where('title', 'Libur Semester Ganjil')->firstOrFail();

        $this->assertSame(AnnouncementStatus::Published, $announcement->status);
        $this->assertTrue($announcement->is_important);
        $this->assertNotNull($announcement->published_at);

        $this->assertDatabaseHas('whatsapp_notification_logs', [
            'announcement_id' => $announcement->id,
            'recipient_user_id' => $recipient->id,
        ]);
        $this->assertDatabaseHas('system_logs', [
            'module' => 'announcement',
            'action' => 'create',
        ]);
    }

    public function test_admin_can_create_class_targeted_announcement(): void
    {
        [, $classA] = $this->makeClasses();
        $admin = User::factory()->asSuperAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => 'Rapat Wali Murid',
                'body' => 'Rapat akan dilaksanakan Sabtu pagi.',
                'target_type' => 'class',
                'class_ids' => [$classA->id],
                'status' => 'draft',
            ])
            ->assertRedirect();

        $announcement = Announcement::query()->where('title', 'Rapat Wali Murid')->firstOrFail();

        $this->assertDatabaseHas('announcement_targets', [
            'announcement_id' => $announcement->id,
            'class_id' => $classA->id,
        ]);
    }

    public function test_announcement_requires_title_and_body(): void
    {
        $admin = User::factory()->asSuperAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => '',
                'body' => '',
                'target_type' => 'all',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors(['title', 'body']);
    }

    public function test_non_admin_cannot_manage_announcements(): void
    {
        $teacher = User::factory()->asTeacher()->create();

        $this->actingAs($teacher)->get(route('admin.announcements.index'))->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.announcements.store'), [
            'title' => 'Tidak boleh',
            'body' => 'Tidak boleh',
            'target_type' => 'all',
            'status' => 'draft',
        ])->assertForbidden();
    }

    public function test_admin_can_publish_and_archive(): void
    {
        $admin = User::factory()->asSuperAdmin()->create();
        $draft = Announcement::factory()->create();
        $published = Announcement::factory()->published()->create();

        $this->actingAs($admin)
            ->post(route('admin.announcements.publish', $draft))
            ->assertRedirect();

        $draft->refresh();
        $this->assertSame(AnnouncementStatus::Published, $draft->status);
        $this->assertNotNull($draft->published_at);

        $this->actingAs($admin)
            ->post(route('admin.announcements.archive', $published))
            ->assertRedirect();

        $this->assertSame(AnnouncementStatus::Archived, $published->fresh()->status);
    }

    public function test_attachment_can_be_uploaded_and_downloaded_by_scope(): void
    {
        Storage::fake('local');
        [, $classA] = $this->makeClasses();
        $admin = User::factory()->asSuperAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                'title' => 'Formulir Pendaftaran',
                'body' => 'Lampiran berisi formulir kegiatan.',
                'target_type' => 'class',
                'class_ids' => [$classA->id],
                'status' => 'published',
                'attachment' => UploadedFile::fake()->create('pengumuman.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $announcement = Announcement::query()->where('title', 'Formulir Pendaftaran')->firstOrFail();

        Storage::disk('local')->assertExists($announcement->attachment_path);

        $insider = Student::factory()->create(['class_id' => $classA->id]);
        $this->actingAs($insider->user)
            ->get(route('announcements.attachment', $announcement))
            ->assertOk();

        $outsider = Student::factory()->create(['class_id' => SchoolClass::factory()->create()->id]);
        $this->actingAs($outsider->user)
            ->get(route('announcements.attachment', $announcement))
            ->assertForbidden();
    }

    public function test_admin_management_pages_render(): void
    {
        [, $classA] = $this->makeClasses();
        $admin = User::factory()->asSuperAdmin()->create();
        $announcement = $this->announcement(AnnouncementTarget::ClassRoom, $classA, ['title' => 'Undangan Rapat']);

        $this->actingAs($admin)->get(route('admin.announcements.index'))->assertOk()->assertSee('Undangan Rapat');
        $this->actingAs($admin)->get(route('admin.announcements.create'))->assertOk()->assertSee('Buat Pengumuman');
        $this->actingAs($admin)->get(route('admin.announcements.show', $announcement))->assertOk();
        $this->actingAs($admin)->get(route('admin.announcements.edit', $announcement))->assertOk();
    }

    /**
     * @return array{0: SchoolYear, 1: SchoolClass, 2: SchoolClass}
     */
    private function makeClasses(): array
    {
        $year = SchoolYear::factory()->active()->create(['name' => '2026/2027']);

        return [
            $year,
            SchoolClass::factory()->create(['school_year_id' => $year->id]),
            SchoolClass::factory()->create(['school_year_id' => $year->id]),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function announcement(AnnouncementTarget $target, ?SchoolClass $class = null, array $attributes = []): Announcement
    {
        $announcement = Announcement::factory()->published()->create(array_merge([
            'title' => 'Pengumuman '.$target->value.($class ? '-'.$class->id : ''),
            'body' => 'Isi pengumuman resmi sekolah untuk pengujian.',
            'target_type' => $target,
        ], $attributes));

        if ($target === AnnouncementTarget::ClassRoom && $class !== null) {
            AnnouncementTargetModel::factory()->create([
                'announcement_id' => $announcement->id,
                'target_type' => AnnouncementTarget::ClassRoom,
                'class_id' => $class->id,
            ]);
        }

        return $announcement;
    }
}
