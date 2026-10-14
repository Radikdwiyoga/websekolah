<?php

namespace Tests\Feature\Admin;

use App\Enums\AgendaMediaType;
use App\Models\SchoolAgenda;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AgendaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_agenda_index(): void
    {
        $admin = User::factory()->asAdmin()->create();
        SchoolAgenda::factory()->create(['title' => 'Peringatan Hari Kemerdekaan']);

        $this->actingAs($admin)
            ->get(route('admin.agendas.index'))
            ->assertOk()
            ->assertSee('Agenda')
            ->assertSee('Peringatan Hari Kemerdekaan');
    }

    public function test_admin_can_view_the_agenda_create_form(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.agendas.create'))
            ->assertOk()
            ->assertSee('Tambah Agenda')
            ->assertSee('Jenis Media');
    }

    public function test_admin_can_create_a_photo_agenda(): void
    {
        Storage::fake('public');
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.agendas.store'), [
                'title' => 'Kegiatan Pramuka',
                'description' => 'Latihan mingguan.',
                'media_type' => AgendaMediaType::Photo->value,
                'media' => UploadedFile::fake()->image('pramuka.jpg', 800, 600),
                'event_date' => '2026-08-17',
                'sort_order' => 2,
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.agendas.index'));

        $agenda = SchoolAgenda::query()->firstOrFail();
        $this->assertSame('Kegiatan Pramuka', $agenda->title);
        $this->assertTrue($agenda->isPhoto());
        $this->assertTrue($agenda->is_published);
        $this->assertSame(2, $agenda->sort_order);
        $this->assertNotNull($agenda->media_path);
        Storage::disk('public')->assertExists($agenda->media_path);
    }

    public function test_admin_can_create_a_video_agenda_from_a_url(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.agendas.store'), [
                'title' => 'Video Profil Sekolah',
                'media_type' => AgendaMediaType::Video->value,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.agendas.index'));

        $agenda = SchoolAgenda::query()->firstOrFail();
        $this->assertTrue($agenda->isVideo());
        $this->assertNull($agenda->media_path);
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $agenda->videoEmbedUrl());
    }

    public function test_a_photo_agenda_requires_an_uploaded_file(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.agendas.create'))
            ->post(route('admin.agendas.store'), [
                'title' => 'Tanpa Foto',
                'media_type' => AgendaMediaType::Photo->value,
            ])
            ->assertSessionHasErrors('media');

        $this->assertSame(0, SchoolAgenda::query()->count());
    }

    public function test_a_video_agenda_requires_a_url_or_file(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.agendas.create'))
            ->post(route('admin.agendas.store'), [
                'title' => 'Tanpa Video',
                'media_type' => AgendaMediaType::Video->value,
            ])
            ->assertSessionHasErrors('video_url');

        $this->assertSame(0, SchoolAgenda::query()->count());
    }

    public function test_a_title_is_required(): void
    {
        $admin = User::factory()->asAdmin()->create();

        $this->actingAs($admin)
            ->from(route('admin.agendas.create'))
            ->post(route('admin.agendas.store'), [
                'title' => '',
                'media_type' => AgendaMediaType::Video->value,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ])
            ->assertSessionHasErrors('title');
    }

    public function test_admin_can_update_an_agenda_replacing_the_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->asAdmin()->create();
        $oldPath = UploadedFile::fake()->image('lama.jpg')->store('agenda', 'public');
        $agenda = SchoolAgenda::factory()->create(['title' => 'Lama', 'media_path' => $oldPath]);

        $this->actingAs($admin)
            ->put(route('admin.agendas.update', $agenda), [
                'title' => 'Baru',
                'media_type' => AgendaMediaType::Photo->value,
                'media' => UploadedFile::fake()->image('baru.jpg'),
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.agendas.index'));

        $agenda->refresh();
        $this->assertSame('Baru', $agenda->title);
        $this->assertNotSame($oldPath, $agenda->media_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($agenda->media_path);
    }

    public function test_admin_can_update_without_replacing_the_existing_photo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->asAdmin()->create();
        $path = UploadedFile::fake()->image('tetap.jpg')->store('agenda', 'public');
        $agenda = SchoolAgenda::factory()->create(['title' => 'Tetap', 'media_path' => $path]);

        $this->actingAs($admin)
            ->put(route('admin.agendas.update', $agenda), [
                'title' => 'Judul Baru',
                'media_type' => AgendaMediaType::Photo->value,
                'is_published' => '1',
            ])
            ->assertRedirect(route('admin.agendas.index'));

        $agenda->refresh();
        $this->assertSame('Judul Baru', $agenda->title);
        $this->assertSame($path, $agenda->media_path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_can_delete_an_agenda_and_its_media(): void
    {
        Storage::fake('public');
        $admin = User::factory()->asAdmin()->create();
        $path = UploadedFile::fake()->image('hapus.jpg')->store('agenda', 'public');
        $agenda = SchoolAgenda::factory()->create(['media_path' => $path]);

        $this->actingAs($admin)
            ->delete(route('admin.agendas.destroy', $agenda))
            ->assertRedirect(route('admin.agendas.index'));

        $this->assertDatabaseMissing('school_agendas', ['id' => $agenda->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_toggle_agenda_publication(): void
    {
        $admin = User::factory()->asAdmin()->create();
        $agenda = SchoolAgenda::factory()->create(['is_published' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.agendas.toggle-status', $agenda))
            ->assertRedirect(route('admin.agendas.index'));

        $this->assertFalse($agenda->refresh()->is_published);
    }

    public function test_homepage_shows_published_agendas_only(): void
    {
        SchoolAgenda::factory()->create(['title' => 'Agenda Terbit', 'is_published' => true]);
        SchoolAgenda::factory()->draft()->create(['title' => 'Agenda Draf']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Agenda Terbit')
            ->assertDontSee('Agenda Draf');
    }

    public function test_homepage_embeds_a_youtube_video_agenda(): void
    {
        SchoolAgenda::factory()->video()->create(['title' => 'Video Promosi']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Video Promosi')
            ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ', false);
    }

    public function test_non_admin_cannot_manage_agendas(): void
    {
        $student = User::factory()->asStudent()->create();
        $agenda = SchoolAgenda::factory()->create();

        $this->actingAs($student)->get(route('admin.agendas.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.agendas.create'))->assertForbidden();
        $this->actingAs($student)->post(route('admin.agendas.store'), [])->assertForbidden();
        $this->actingAs($student)->delete(route('admin.agendas.destroy', $agenda))->assertForbidden();
    }
}
