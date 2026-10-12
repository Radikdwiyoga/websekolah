<?php

namespace Tests\Feature\Auth;

use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_loading_state_and_back_to_home_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-submit-loading')
            ->assertSee('Memproses')
            ->assertSee('Kembali ke Beranda');
    }

    public function test_login_page_shows_school_name_from_settings(): void
    {
        ApplicationSetting::factory()->create(['key' => 'school_name', 'value' => 'SMA Harapan Bangsa']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('SMA Harapan Bangsa');
    }

    public function test_forgot_and_reset_pages_have_loading_state(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('data-submit-loading')
            ->assertSee('Kembali ke Beranda');

        $this->get(route('password.reset', ['token' => 'reset-token']))
            ->assertOk()
            ->assertSee('data-submit-loading')
            ->assertSee('Kembali ke Beranda');
    }

    public function test_invalid_login_shows_indonesian_message(): void
    {
        User::factory()->asStudent()->create(['username' => 'siswa1']);

        $this->from('/login')
            ->followingRedirects()
            ->post('/login', [
                'username' => 'siswa1',
                'password' => 'wrong-password',
            ])
            ->assertOk()
            ->assertSee('Kredensial yang diberikan tidak cocok dengan catatan kami.');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_with_indonesian_message(): void
    {
        $credentials = [
            'username' => 'ratelimit',
            'password' => 'wrong-password',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->followingRedirects()->post('/login', $credentials)
                ->assertOk()
                ->assertSee('Kredensial yang diberikan tidak cocok dengan catatan kami.');
        }

        $this->from('/login')->followingRedirects()->post('/login', $credentials)
            ->assertOk()
            ->assertSee('Terlalu banyak percobaan masuk');

        $this->assertGuest();

        RateLimiter::clear('ratelimit|127.0.0.1');
    }
}
