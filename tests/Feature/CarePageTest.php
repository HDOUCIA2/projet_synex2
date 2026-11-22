<?php

namespace Tests\Feature;

use App\Models\CareCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_shows_assistance_number_and_service_categories(): void
    {
        $this->get(route('public.care.index'))
            ->assertOk()
            ->assertSee('Prise en charge et orientation')
            ->assertSee('href="tel:1390"', false)
            ->assertSee('1390')
            ->assertSee('Soins de santé')
            ->assertSee('Soutien psychosocial')
            ->assertSee('icon-base bx bx-briefcase', false)
            ->assertSee('icon-base bx bx-heart', false)
            ->assertSee('care-map', false);
    }

    public function test_only_published_centers_are_listed_and_public_map_permissions_are_respected(): void
    {
        CareCenter::create([
            'name' => 'Centre public de test',
            'slug' => 'centre-public-test',
            'structure_type' => 'CISM',
            'province' => 'Mayo-Kebbi Est',
            'locality' => 'Bongor',
            'latitude' => 10.28,
            'longitude' => 15.37,
            'is_published' => true,
            'show_on_public_map' => true,
            'show_public_detail' => true,
        ]);
        CareCenter::create([
            'name' => 'Centre privé de test',
            'slug' => 'centre-prive-test',
            'structure_type' => 'Relais',
            'province' => 'Mayo-Kebbi Est',
            'locality' => 'Bongor',
            'latitude' => 10.29,
            'longitude' => 15.38,
            'is_published' => false,
            'show_on_public_map' => true,
            'show_public_detail' => true,
        ]);

        $this->get(route('public.care.index'))
            ->assertOk()
            ->assertSee('Centre public de test')
            ->assertDontSee('Centre privé de test');

        $this->get(route('public.care.show', 'centre-public-test'))
            ->assertOk()
            ->assertSee('Centre public de test');

        $this->get(route('public.care.show', 'centre-prive-test'))
            ->assertNotFound();
    }
}
