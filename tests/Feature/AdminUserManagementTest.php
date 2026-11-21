<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_administrator_can_open_the_account_creation_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Utilisateur simple')
            ->assertSee('Administrateur');

        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.users.create'))
            ->assertForbidden();
    }

    public function test_administrator_can_create_either_account_type(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        foreach (['user', 'admin'] as $role) {
            $username = "new-{$role}";
            $password = 'a-strong-password';

            $this->post(route('admin.users.store'), [
                'name' => "New {$role}",
                'username' => $username,
                'email' => "{$username}@synex.test",
                'password' => $password,
                'password_confirmation' => $password,
                'role' => $role,
            ])->assertRedirect(route('admin.users.create'))
                ->assertSessionHas('status');

            $createdUser = User::where('username', $username)->firstOrFail();
            $this->assertSame($role, $createdUser->role);
            $this->assertTrue(Hash::check($password, $createdUser->password));
        }
    }

    public function test_regular_user_cannot_create_an_administrator(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('admin.users.store'), [
                'name' => 'Unauthorized Admin',
                'username' => 'unauthorized-admin',
                'email' => 'unauthorized@synex.test',
                'password' => 'a-strong-password',
                'password_confirmation' => 'a-strong-password',
                'role' => 'admin',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', ['username' => 'unauthorized-admin']);
    }
}
