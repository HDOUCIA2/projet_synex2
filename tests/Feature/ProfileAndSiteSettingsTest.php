<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\SiteFooterLink;
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
        $footerContent = [
            'description' => 'Présentation modifiée du site.',
            'copyright_text' => 'Tous droits réservés.',
        ];
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->put(route('admin.settings.site.update'), [
                'name' => 'Synex Tchad',
                'address' => 'N’Djamena',
                'contact_email' => 'contact@synex.example',
                'contact_phone' => '+235 00 00 00',
                'location' => 'Tchad',
                'logo' => UploadedFile::fake()->create('logo.png', 1, 'image/png'),
                'footer_content' => $footerContent,
            ])
            ->assertRedirect(route('admin.settings.site.edit'));

        $settings = SiteSetting::query()->findOrFail(1);
        $this->assertSame('Synex Tchad', $settings->name);
        $this->assertSame('N’Djamena', $settings->address);
        $this->assertSame('Tchad', $settings->location);
        $this->assertSame('Présentation modifiée du site.', $settings->footer_content['description']);
        Storage::disk('public')->assertExists($settings->logo_path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Synex Tchad')
            ->assertSee('contact@synex.example')
            ->assertSee('Présentation modifiée du site.')
            ->assertSee('Carte des provinces du Tchad')
            ->assertDontSee('Ressources')
            ->assertDontSee('Qui sommes-nous')
            ->assertDontSee('dropdown-toggle', false);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.settings.site.edit'))
            ->assertOk()
            ->assertDontSee('content-footer');
    }

    public function test_footer_links_are_database_managed_and_useful_pages_can_be_hidden(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $pageLink = SiteFooterLink::query()->where('route_name', 'veille')->firstOrFail();

        $this->patch(route('admin.settings.site.footer-pages.visibility', $pageLink), ['is_visible' => false])
            ->assertRedirect(route('admin.settings.site.edit'));
        $this->assertFalse($pageLink->fresh()->is_visible);

        $this->post(route('admin.settings.site.footer-links.store'), [
            'type' => 'legal',
            'label' => 'Mentions légales',
            'url' => '/mentions-legales',
        ])->assertRedirect(route('admin.settings.site.edit'));
        $legalLink = SiteFooterLink::query()->where('type', 'legal')->firstOrFail();
        $this->assertSame('/mentions-legales', $legalLink->url);

        $this->post(route('admin.settings.site.footer-links.store'), [
            'type' => 'social',
            'label' => 'LinkedIn SYNEX',
            'url' => 'https://www.linkedin.com/company/synex',
            'icon' => 'bxl-linkedin-square',
        ])->assertRedirect(route('admin.settings.site.edit'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mentions légales')
            ->assertSee('LinkedIn SYNEX')
            ->assertDontSee('<a href="'.route('veille').'">Veille</a>', false);

        $this->put(route('admin.settings.site.footer-links.update', $legalLink), [
            'label' => 'Informations légales',
            'url' => '/informations-legales',
        ])->assertRedirect(route('admin.settings.site.edit'));
        $this->assertSame('Informations légales', $legalLink->fresh()->label);

        $this->delete(route('admin.settings.site.footer-links.destroy', $legalLink))
            ->assertRedirect(route('admin.settings.site.edit'));
        $this->assertDatabaseMissing('site_footer_links', ['id' => $legalLink->id]);
    }

    public function test_footer_links_reject_unsafe_urls_and_unapproved_icons(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('admin.settings.site.footer-links.store'), [
                'type' => 'legal',
                'label' => 'Lien dangereux',
                'url' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('url');

        $this->post(route('admin.settings.site.footer-links.store'), [
            'type' => 'social',
            'label' => 'Faux réseau',
            'url' => 'https://example.com',
            'icon' => 'bx-hack',
        ])->assertSessionHasErrors('icon');

        $this->assertDatabaseCount('site_footer_links', 6);
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
