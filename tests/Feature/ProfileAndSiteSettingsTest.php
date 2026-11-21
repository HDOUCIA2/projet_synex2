<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAndSiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_profile_and_password_without_changing_role(): void
    {
        $user = User::factory()->create(['role' => 'user', 'username' => 'old-name']);

        $this->actingAs($user)
            ->put(route('pages-account-settings-account.update'), [
                'name' => 'Updated Name',
                'username' => 'updated-name',
                'email' => $user->email,
                'current_password' => 'password',
                'password' => 'a-strong-new-password',
                'password_confirmation' => 'a-strong-new-password',
                'role' => 'admin',
            ])
            ->assertRedirect(route('pages-account-settings-account'));

        $user->refresh();
        $this->assertSame('Updated Name', $user->name);
        $this->assertSame('updated-name', $user->username);
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('a-strong-new-password', $user->password));
    }

    public function test_only_admin_can_change_public_site_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.settings.site.edit'))
            ->assertForbidden();

        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.settings.site.update'), [
                'name' => 'Synex Tchad',
                'address' => 'N’Djamena',
                'contact_email' => 'contact@synex.example',
                'contact_phone' => '+235 00 00 00',
                'location' => 'Tchad',
                'logo' => UploadedFile::fake()->create('logo.png', 1, 'image/png'),
            ])
            ->assertRedirect(route('admin.settings.site.edit'));

        $settings = SiteSetting::query()->findOrFail(1);
        $this->assertSame('Synex Tchad', $settings->name);
        $this->assertSame('N’Djamena', $settings->address);
        $this->assertSame('Tchad', $settings->location);
        Storage::disk('public')->assertExists($settings->logo_path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Synex Tchad')
            ->assertSee('contact@synex.example');
    }

    public function test_profile_photo_is_optional_and_uploaded_photo_is_shown(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'user', 'username' => 'profile-user']);

        $this->actingAs($user)
            ->get(route('pages-account-settings-account'))
            ->assertOk()
            ->assertSee(asset('assets/img/avatars/1.png'));

        $this->put(route('pages-account-settings-account.update'), [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('avatar.png', 1, 'image/png'),
        ])->assertRedirect(route('pages-account-settings-account'))->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);

        $this->get(route('pages-account-settings-account'))
            ->assertOk()
            ->assertSee(asset('storage/'.$user->avatar_path));
    }
}
