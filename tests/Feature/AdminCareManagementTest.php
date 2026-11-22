<?php

namespace Tests\Feature;

use App\Models\CareCenter;
use App\Models\CareService;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCareManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_edit_publish_and_remove_a_care_center(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $service = CareService::query()->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('admin.care.index'))
            ->assertOk()
            ->assertSee('Prise en charge et orientation')
            ->assertSee($service->name);
        $this->get(route('admin.care.create'))
            ->assertOk()
            ->assertSee('Informations sur la structure')
            ->assertSee('Afficher la localisation publique sur la carte');

        $this->post(route('admin.care.store'), $this->centerAttributes($service->id))
            ->assertRedirect(route('admin.care.index'));

        $center = CareCenter::query()->where('name', 'Centre de test')->firstOrFail();
        $this->assertDatabaseHas('care_center_service', [
            'care_center_id' => $center->id,
            'care_service_id' => $service->id,
        ]);
        $this->get(route('public.care.index'))->assertSee('Centre de test');

        $this->put(route('admin.care.update', $center), $this->centerAttributes($service->id, [
            'name' => 'Centre de test modifié',
            'show_on_public_map' => '0',
            'latitude' => '',
            'longitude' => '',
        ]))->assertRedirect(route('admin.care.index'));
        $center->refresh();
        $this->assertSame('centre-de-test-modifie', $center->slug);

        $this->patch(route('admin.care.publication', $center))
            ->assertRedirect(route('admin.care.index'));
        $this->assertDatabaseHas('care_centers', ['id' => $center->id, 'is_published' => false]);

        $this->delete(route('admin.care.destroy', $center))
            ->assertRedirect(route('admin.care.index'));
        $this->assertDatabaseMissing('care_centers', ['id' => $center->id]);
    }

    public function test_admin_cannot_publish_map_location_without_verified_chad_coordinates(): void
    {
        $service = CareService::query()->firstOrFail();

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('admin.care.create'))
            ->post(route('admin.care.store'), $this->centerAttributes($service->id, [
                'latitude' => '',
                'longitude' => '',
            ]))
            ->assertSessionHasErrors(['latitude', 'longitude']);

        $this->assertDatabaseMissing('care_centers', ['name' => 'Centre de test']);
    }

    public function test_administrator_can_manage_services_without_deleting_assigned_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $service = CareService::query()->firstOrFail();
        $center = CareCenter::query()->create(array_merge($this->centerAttributes($service->id, [
            'is_published' => '0',
            'show_on_public_map' => '0',
            'show_public_detail' => '0',
            'latitude' => '',
            'longitude' => '',
        ]), ['slug' => 'centre-de-test']));
        $center->services()->sync([$service->id]);

        $this->actingAs($admin)
            ->put(route('admin.care.services.update', $service), [
                'name' => $service->name,
                'description' => $service->description,
                'icon' => $service->icon,
                'sort_order' => $service->sort_order,
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.care.index').'#care-services');

        $this->assertDatabaseHas('care_services', ['id' => $service->id, 'is_active' => false]);
        $this->delete(route('admin.care.services.destroy', $service))
            ->assertSessionHasErrors('service');
        $this->assertDatabaseHas('care_services', ['id' => $service->id]);
    }

    public function test_administrator_can_upload_and_remove_the_public_cism_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->put(route('admin.care.image'), [
                'cism_image' => new UploadedFile(
                    base_path('public/assets/img/branding/favicon/favicon-32x32.png'),
                    'cism.png',
                    'image/png',
                    null,
                    true
                ),
            ])
            ->assertRedirect(route('admin.care.index'));

        $settings = SiteSetting::query()->findOrFail(1);
        $imagePath = $settings->care_page_image_path;
        Storage::disk('public')->assertExists($imagePath);
        $this->get(route('public.care.index'))->assertSee($imagePath, false);

        $this->put(route('admin.care.image'), ['remove_cism_image' => '1'])
            ->assertRedirect(route('admin.care.index'));

        $this->assertNull($settings->fresh()->care_page_image_path);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_administrator_can_edit_the_content_of_static_public_sections(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $content = config('synex-care.page_content');
        $content['process_title'] = 'Un parcours clair en trois étapes';
        $content['process_steps'][0]['title'] = '1. Être écouté';

        $this->actingAs($admin)
            ->put(route('admin.care.content'), ['content' => $content])
            ->assertRedirect(route('admin.care.index'));

        $this->assertSame(
            'Un parcours clair en trois étapes',
            SiteSetting::query()->findOrFail(1)->care_page_content['process_title']
        );
        $this->get(route('public.care.index'))
            ->assertSee('Un parcours clair en trois étapes')
            ->assertSee('1. Être écouté');
    }

    public function test_only_administrators_can_manage_the_care_page(): void
    {
        $this->get(route('admin.care.index'))->assertRedirect(route('auth-login-basic'));
        $this->put(route('admin.care.content'))->assertRedirect(route('auth-login-basic'));

        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.care.index'))
            ->assertForbidden();
        $this->put(route('admin.care.content'))->assertForbidden();
    }

    private function centerAttributes(int $serviceId, array $overrides = []): array
    {
        return array_replace([
            'name' => 'Centre de test',
            'structure_type' => 'CISM',
            'description' => 'Description vérifiée.',
            'province' => 'Mayo-Kebbi Est',
            'department' => 'Mayo-Boneye',
            'locality' => 'Bongor',
            'address' => 'Adresse institutionnelle',
            'latitude' => '10.2800000',
            'longitude' => '15.3700000',
            'phone' => '+235 22 00 00 00',
            'email' => 'contact@example.org',
            'opening_hours' => 'Du lundi au vendredi',
            'is_free' => '1',
            'confidentiality_note' => 'Accueil confidentiel.',
            'is_published' => '1',
            'show_on_public_map' => '1',
            'show_public_detail' => '1',
            'service_ids' => [$serviceId],
        ], $overrides);
    }
}
