<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_redirects_user_to_their_role_dashboard(): void
    {
        $this->actingAs(User::factory()->asTeacher()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('dashboard.teacher'));
    }

    public function test_each_role_can_access_their_own_dashboard(): void
    {
        $cases = [
            'dashboard.student' => User::factory()->asStudent()->create(),
            'dashboard.teacher' => User::factory()->asTeacher()->create(),
            'dashboard.parent' => User::factory()->asParent()->create(),
            'dashboard.admin' => User::factory()->asAdmin()->create(),
        ];

        foreach ($cases as $route => $user) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_student_can_not_access_other_role_dashboards(): void
    {
        $student = User::factory()->asStudent()->create();

        $this->actingAs($student)->get(route('dashboard.teacher'))->assertForbidden();
        $this->actingAs($student)->get(route('dashboard.parent'))->assertForbidden();
        $this->actingAs($student)->get(route('dashboard.admin'))->assertForbidden();
    }

    public function test_teacher_can_not_access_admin_dashboard(): void
    {
        $teacher = User::factory()->asTeacher()->create();

        $this->actingAs($teacher)->get(route('dashboard.admin'))->assertForbidden();
    }

    public function test_parent_can_not_access_student_dashboard(): void
    {
        $parent = User::factory()->asParent()->create();

        $this->actingAs($parent)->get(route('dashboard.student'))->assertForbidden();
    }
}
