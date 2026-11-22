<?php

namespace Tests\Feature;

use App\Models\PreventionInitiative;
use App\Models\PreventionResource;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PreventionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_prevention_page_shows_documented_indicators_initiatives_and_resources(): void
    {
        $response = $this->get(route('prevention'));
        $response
            ->assertOk()
            ->assertSee('Prévenir les conflits, renforcer la cohésion sociale')
            ->assertSee('129 enregistrements documentés')
            ->assertSee('L’information là où elle se lit')
            ->assertSee('Campagnes de prévention documentées à Doba')
            ->assertSee('Logone Oriental')
            ->assertSee('Ressources en cours de préparation')
            ->assertSee('bxs-megaphone')
            ->assertSee('bx-conversation')
            ->assertSee('bxs-heart')
            ->assertSee('bx-briefcase')
            ->assertDontSee('bx-bullhorn')
            ->assertDontSee('bx-handshake')
            ->assertSee(route('prevention'));

        $this->assertSame(3, substr_count($response->getContent(), 'class="prevention-initiative-row"'));
    }

    public function test_administrator_can_manage_page_content_and_statistics_must_balance_by_province(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = config('synex_prevention');
        $content['hero_title'] = 'Prévenir ensemble';

        $this->actingAs($admin)
            ->get(route('admin.prevention.index'))
            ->assertOk()
            ->assertSee('Contenu et indicateurs publics')
            ->assertSee('Initiatives documentées')
            ->assertSee('Ressources documentaires');

        $content['province_distribution'][0]['count'] = 66;
        $this->from(route('admin.prevention.index'))
            ->put(route('admin.prevention.content'), ['content' => $content])
            ->assertSessionHasErrors('content.province_distribution');
        $this->assertDatabaseMissing('site_settings', ['prevention_page_content->hero_title' => 'Prévenir ensemble']);

        $content['metrics']['campaigns'] = 80;
        $content['province_distribution'][0]['count'] = 66;
        $this->put(route('admin.prevention.content'), ['content' => $content])
            ->assertRedirect(route('admin.prevention.index'));

        $this->assertSame(
            'Prévenir ensemble',
            SiteSetting::query()->findOrFail(1)->prevention_page_content['hero_title']
        );
        $this->get(route('prevention'))
            ->assertSee('Prévenir ensemble')
            ->assertSee('130 enregistrements documentés');
    }

    public function test_administrator_can_create_edit_unpublish_and_remove_initiatives(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('admin.prevention.initiatives.store'), [
                'title' => 'Médiation testée',
                'type' => 'mediation',
                'initiative_date' => '2026-10-12',
                'province' => 'Mandoul',
                'location' => 'Koumra',
                'source' => 'Source vérifiée',
                'records' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('admin.prevention.index'));

        $initiative = PreventionInitiative::query()->where('title', 'Médiation testée')->firstOrFail();
        $this->get(route('prevention'))->assertSee('Médiation testée');

        $this->put(route('admin.prevention.initiatives.update', $initiative), [
            'title' => 'Médiation en brouillon',
            'type' => 'mediation',
            'initiative_date' => '2026-10-12',
            'province' => 'Mandoul',
            'records' => 1,
        ])->assertRedirect(route('admin.prevention.index'));
        $this->get(route('prevention'))->assertDontSee('Médiation en brouillon');

        $this->delete(route('admin.prevention.initiatives.destroy', $initiative))
            ->assertRedirect(route('admin.prevention.index'));
        $this->assertDatabaseMissing('prevention_initiatives', ['id' => $initiative->id]);
    }

    public function test_administrator_can_upload_and_remove_a_validated_pdf_resource(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $pdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<<>>\n%%EOF";

        $this->actingAs($admin)
            ->post(route('admin.prevention.resources.store'), [
                'title' => 'Guide de sensibilisation',
                'description' => 'Document vérifié.',
                'file' => UploadedFile::fake()->createWithContent('guide.pdf', $pdf),
                'is_published' => 0,
            ])
            ->assertRedirect(route('admin.prevention.index'));

        $resource = PreventionResource::query()->firstOrFail();
        $previousPath = $resource->file_path;
        Storage::disk('public')->assertExists($resource->file_path);
        $this->get(route('prevention'))->assertDontSee('Guide de sensibilisation');

        $this->put(route('admin.prevention.resources.update', $resource), [
            'title' => 'Guide publié',
            'description' => 'Document validé.',
            'file' => UploadedFile::fake()->createWithContent('guide-updated.pdf', $pdf),
            'is_published' => 1,
        ])->assertRedirect(route('admin.prevention.index'));

        $resource->refresh();
        $this->assertNotSame($previousPath, $resource->file_path);
        Storage::disk('public')->assertMissing($previousPath);
        Storage::disk('public')->assertExists($resource->file_path);
        $this->get(route('prevention'))
            ->assertSee('Guide publié')
            ->assertSee(Storage::disk('public')->url($resource->file_path));

        $this->delete(route('admin.prevention.resources.destroy', $resource))
            ->assertRedirect(route('admin.prevention.index'));
        Storage::disk('public')->assertMissing($resource->file_path);
    }

    public function test_only_administrators_can_manage_prevention_content(): void
    {
        $this->get(route('admin.prevention.index'))->assertRedirect(route('auth-login-basic'));
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.prevention.index'))
            ->assertForbidden();
    }
}
