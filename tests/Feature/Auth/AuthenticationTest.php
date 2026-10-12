<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $user = User::factory()->asStudent()->create(['username' => 'siswa1']);

        $response = $this->post('/login', [
            'username' => 'siswa1',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.student'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        User::factory()->create(['username' => 'siswa1']);

        $this->post('/login', [
            'username' => 'siswa1',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_inactive_users_can_not_authenticate(): void
    {
        User::factory()->inactive()->create(['username' => 'nonaktif']);

        $this->post('/login', [
            'username' => 'nonaktif',
            'password' => 'password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_login_rejects_mismatched_role(): void
    {
        User::factory()->asTeacher()->create(['username' => 'guru1']);

        $this->from('/login')->post('/login', [
            'username' => 'guru1',
            'password' => 'password',
            'role' => 'student',
        ])->assertRedirect('/login')->assertSessionHasErrors('role');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
