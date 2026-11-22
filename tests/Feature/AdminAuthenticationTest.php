<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_the_synex_brand_and_username_form(): void
    {
        $this->get(route('auth-login-basic'))
            ->assertOk()
            ->assertSee('Connexion à Synex')
            ->assertSee('assets/img/branding/svg/symbole_dore.svg')
            ->assertSee('assets/img/branding/favicon/favicon.ico')
            ->assertSee('name="identifier"', false)
            ->assertSee('name="password"', false)
            ->assertSee(route('auth-register-basic'));
    }

    public function test_public_dashboard_is_accessible_but_event_management_requires_login(): void
    {
        $this->get(route('dashboard-analytics'))
            ->assertOk();

        $this->get(route('admin.events.index'))
            ->assertRedirect(route('auth-login-basic'));
    }

    public function test_user_can_sign_in_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'synex-admin',
            'password' => 'a-strong-password',
        ]);

        $this->post(route('auth.authenticate'), [
            'identifier' => 'synex-admin',
            'password' => 'a-strong-password',
        ])->assertRedirect(route('dashboard-analytics'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_can_create_a_user_profile_but_not_an_admin_account(): void
    {
        $this->get(route('auth-register-basic'))
            ->assertOk()
            ->assertSee('Créer un profil Synex');

        $this->post(route('auth.register'), [
            'name' => 'Synex User',
            'username' => 'synex-user',
            'email' => 'user@synex.test',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
            'role' => 'admin',
        ])->assertRedirect(route('dashboard-analytics'));

        $user = User::where('username', 'synex-user')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_sign_in_with_email(): void
    {
        $user = User::factory()->create([
            'username' => 'synex-admin',
            'email' => 'admin@synex.test',
            'password' => 'a-strong-password',
            'role' => 'admin',
        ]);

        $this->post(route('auth.authenticate'), [
            'identifier' => 'admin@synex.test',
            'password' => 'a-strong-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_do_not_sign_in(): void
    {
        User::factory()->create([
            'username' => 'synex-admin',
            'password' => 'a-strong-password',
        ]);

        $this->from(route('auth-login-basic'))
            ->post(route('auth.authenticate'), [
                'identifier' => 'synex-admin',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('auth-login-basic'))
            ->assertSessionHasErrors('identifier');

        $this->assertGuest();
    }

    public function test_admin_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('auth-login-basic'));

        $this->assertGuest();
    }
}
