<?php

namespace Tests\Feature;

use App\Models\ConflictEvent;
use App\Models\ProgramNews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_home_uses_home_page_two_and_preserves_the_original_landing_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Une plateforme de suivi, d’analyse et de veille des conflits');

        $this->get(route('home.original'))
            ->assertOk()
            ->assertSee('id="aperçu"', false);
    }

    public function test_alternative_home_uses_approved_events_and_published_news(): void
    {
        ConflictEvent::query()->create([
            'source_row' => 1,
            'status' => 'approved',
            'event_year' => 2025,
            'event_type' => 'Conflit intercommunautaire',
            'province' => 'Mandoul',
            'latitude' => 8.44,
            'longitude' => 16.17599,
            'deaths' => 3,
        ]);
        ConflictEvent::query()->create([
            'source_row' => 2,
            'status' => 'pending',
            'event_year' => 2025,
            'event_type' => 'Type à exclure',
            'province' => 'Logone Oriental',
            'latitude' => 9,
            'longitude' => 19,
            'deaths' => 9,
        ]);
        ConflictEvent::query()->create([
            'source_row' => 3,
            'status' => 'approved',
            'event_year' => 2024,
            'event_type' => 'Affrontement',
            'province' => 'Mandoul',
            'deaths' => 0,
        ]);

        ProgramNews::query()->create([
            'title' => 'Actualité publiée',
            'summary' => 'Résumé de la publication.',
            'content' => 'Contenu publié.',
            'image_path' => 'program-news/published.jpg',
            'published_at' => now()->subDay(),
            'is_published' => true,
        ]);
        ProgramNews::query()->create([
            'title' => 'Actualité non publiée',
            'summary' => 'Résumé de brouillon.',
            'content' => 'Contenu brouillon.',
            'image_path' => 'program-news/draft.jpg',
            'published_at' => now()->subDay(),
            'is_published' => false,
        ]);

        $this->get(route('home.page-2'))
            ->assertOk()
            ->assertSee('aria-label="Nombre d’événements validés : 2"', false)
            ->assertSee('aria-label="Nombre de provinces renseignées : 1"', false)
            ->assertSee('aria-label="Nombre de décès signalés : 3"', false)
            ->assertSee('\u0022latitude\u0022:8.44', false)
            ->assertDontSee('\u0022latitude\u0022:9', false)
            ->assertSee('chad-adm1.geojson')
            ->assertSee('synexHome2HeroMap')
            ->assertSee('Incidents par type')
            ->assertSee('Conflit intercommunautaire')
            ->assertSee('Affrontement')
            ->assertSee('provinceEventTypeCounts')
            ->assertDontSee('Type à exclure')
            ->assertSee('Carte des incidents au Tchad')
            ->assertSee('synex-home2-trend-chart')
            ->assertSee('synex-home2-trend-line')
            ->assertSee('2024')
            ->assertSee('Actualité publiée')
            ->assertDontSee('Actualité non publiée');
    }
}
