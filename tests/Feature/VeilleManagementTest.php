<?php

namespace Tests\Feature;

use App\Models\ConflictEvent;
use App\Models\ProgramNews;
use App\Models\Province;
use App\Models\ProvincePopulation;
use App\Models\User;
use App\Models\VeilleSection;
use App\Services\DeathRateCalculator;
use App\Support\ConflictEventDateParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VeilleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_date_parser_reads_french_month_names(): void
    {
        $this->assertSame('2025-05-12', ConflictEventDateParser::parse('12-mai-25', 2025)?->toDateString());
        $this->assertNull(ConflictEventDateParser::parse('31-fevrier-25', 2025));
    }

    public function test_public_veille_page_displays_managed_content_and_links_to_dashboard(): void
    {
        $this->get(route('veille'))
            ->assertOk()
            ->assertSee('Suivi des dynamiques de conflits')
            ->assertSee('Fil d’Ariane')
            ->assertSee('Cinq lectures du terrain')
            ->assertSee('Cinq angles complémentaires pour comprendre la situation sur le terrain.')
            ->assertSee('Décès pour 100 000 habitants')
            ->assertSee('< 3')
            ->assertSee('3 – 8')
            ->assertSee('> 8')
            ->assertSee('id="recorded-deaths"', false)
            ->assertSee('id="territory-population"', false)
            ->assertSee('data-death-level="low"', false)
            ->assertSee('data-death-level="moderate"', false)
            ->assertSee('data-death-level="high"', false)
            ->assertDontSee('href="#calculateur"', false)
            ->assertDontSee('href="'.route('admin.veille.index').'"', false)
            ->assertDontSee('population_year')
            ->assertSee(route('dashboard-analytics'));
    }

    public function test_administrator_can_create_publish_unpublish_and_delete_a_veille_reading(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $section = VeilleSection::where('key', 'home_features')->firstOrFail();
        $this->actingAs($admin);

        $this->post(route('admin.veille.cards.store', $section), [
            'title' => 'Lecture de test',
            'description' => 'Texte administrable.',
            'icon' => 'bx-book-open',
            'is_active' => 1,
        ])->assertRedirect();

        $card = $section->cards()->where('title', 'Lecture de test')->firstOrFail();
        $this->get(route('veille'))->assertSee('Lecture de test');

        $this->put(route('admin.veille.cards.update', $card), [
            'title' => 'Lecture modifiée',
            'description' => 'Texte mis à jour.',
            'icon' => 'bx-bolt',
        ])->assertRedirect();

        $this->get(route('veille'))->assertDontSee('Lecture modifiée');

        $this->put(route('admin.veille.cards.update', $card), [
            'title' => 'Lecture modifiée',
            'description' => 'Texte mis à jour.',
            'icon' => 'bx-bolt',
            'is_active' => 1,
        ])->assertRedirect();

        $this->get(route('veille'))->assertSee('Lecture modifiée');
        $this->delete(route('admin.veille.cards.destroy', $card))->assertRedirect();
        $this->assertDatabaseMissing('veille_cards', ['id' => $card->id]);
    }

    public function test_administrator_can_upload_and_replace_a_reading_image(): void
    {
        Storage::fake('public');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j0X8AAAAASUVORK5CYII=');
        $admin = User::factory()->create(['role' => 'admin']);
        $section = VeilleSection::where('key', 'home_features')->firstOrFail();
        $this->actingAs($admin);

        $this->post(route('admin.veille.cards.store', $section), [
            'title' => 'Lecture illustrée',
            'description' => 'Une image de lecture.',
            'icon' => 'bx-bolt',
            'image' => UploadedFile::fake()->createWithContent('incident.png', $png),
            'is_active' => 1,
        ])->assertRedirect();

        $card = $section->cards()->where('title', 'Lecture illustrée')->firstOrFail();
        $oldImage = $card->image_path;
        $this->assertNotNull($oldImage);
        Storage::disk('public')->assertExists($oldImage);
        $this->get(route('veille'))->assertSee(asset('storage/'.$oldImage));

        $this->put(route('admin.veille.cards.update', $card), [
            'title' => 'Lecture illustrée',
            'description' => 'Image remplacée.',
            'icon' => 'bx-bolt',
            'image' => UploadedFile::fake()->createWithContent('incident-updated.png', $png),
            'is_active' => 1,
        ])->assertRedirect();

        $card->refresh();
        $this->assertNotSame($oldImage, $card->image_path);
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($card->image_path);

        $replacementImage = $card->image_path;
        $this->put(route('admin.veille.cards.update', $card), [
            'title' => 'Lecture illustrée',
            'description' => 'Retour à l’icône.',
            'icon' => 'bx-bolt',
            'remove_image' => 1,
            'is_active' => 1,
        ])->assertRedirect();

        $card->refresh();
        $this->assertNull($card->image_path);
        Storage::disk('public')->assertMissing($replacementImage);
        $this->get(route('veille'))->assertSee('bx-bolt');

        $this->delete(route('admin.veille.cards.destroy', $card))->assertRedirect();
        $this->assertDatabaseMissing('veille_cards', ['id' => $card->id]);
    }

    public function test_administrator_can_manage_only_first_capture_content(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $section = VeilleSection::where('key', 'home_features')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.veille.index'))
            ->assertOk()
            ->assertSee('Cinq lectures du terrain')
            ->assertDontSee('Populations par province')
            ->assertDontSee('name="color"', false)
            ->assertDontSee('name="animation"', false)
            ->assertDontSee('name="display_order"', false)
            ->assertSee('name="icon"', false)
            ->assertSee('name="image"', false);

        $this->put(route('admin.veille.sections.update', $section), [
            'title' => 'Lectures actualisées',
            'description' => 'Texte de présentation actualisé.',
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('veille_sections', [
            'id' => $section->id,
            'title' => 'Lectures actualisées',
            'description' => 'Texte de présentation actualisé.',
        ]);

        $severitySection = VeilleSection::where('key', 'home_severity')->firstOrFail();
        $this->put(route('admin.veille.sections.update', $severitySection), [
            'title' => 'Modification interdite',
            'is_active' => 1,
        ])->assertNotFound();
    }

    public function test_regular_user_cannot_access_veille_management(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.veille.index'))
            ->assertForbidden();
    }

    public function test_death_rate_uses_approved_events_and_population_for_the_selected_year(): void
    {
        $province = Province::create(['name' => 'Mandoul']);
        ProvincePopulation::create([
            'province_id' => $province->id,
            'year' => 2025,
            'population' => 100000,
        ]);

        ConflictEvent::create([
            'source_row' => 1,
            'event_year' => 2025,
            'province' => 'mandoul',
            'deaths' => 12,
            'status' => 'approved',
        ]);
        ConflictEvent::create([
            'source_row' => 2,
            'event_year' => 2025,
            'province' => 'Mandoul',
            'deaths' => 50,
            'status' => 'pending',
        ]);

        $rates = app(DeathRateCalculator::class)->forYear(2025);

        $this->assertSame(12, $rates->first()['deaths']);
        $this->assertSame(12.0, $rates->first()['rate']);
    }

    public function test_public_veille_uses_approved_events_for_metrics_maps_and_filters(): void
    {
        ConflictEvent::query()->create([
            'source_row' => 1,
            'event_year' => now()->year,
            'event_date_raw' => now()->subDays(10)->format('j-M-y'),
            'event_type' => 'Affrontement',
            'province' => 'Mandoul',
            'locality' => 'Koundoul',
            'latitude' => 8.44,
            'longitude' => 16.17599,
            'deaths' => 3,
            'status' => 'approved',
        ]);
        ConflictEvent::query()->create([
            'source_row' => 2,
            'event_year' => now()->subDays(50)->year,
            'event_date_raw' => now()->subDays(50)->format('j-M-y'),
            'event_type' => 'Conflit intercommunautaire',
            'province' => 'Mandoul',
            'deaths' => 2,
            'status' => 'approved',
        ]);
        ConflictEvent::query()->create([
            'source_row' => 3,
            'event_year' => now()->year,
            'event_date_raw' => now()->subDays(5)->format('j-M-y'),
            'event_type' => 'Événement en attente',
            'province' => 'Mandoul',
            'latitude' => 8.44,
            'longitude' => 16.17599,
            'deaths' => 20,
            'status' => 'pending',
        ]);
        ProgramNews::query()->create([
            'title' => 'Bulletin de veille publié',
            'summary' => 'Publication consultable.',
            'content' => 'Contenu publié.',
            'image_path' => 'bulletins/veille.jpg',
            'category' => 'veille',
            'published_at' => now()->subDays(5),
            'is_published' => true,
        ]);
        ProgramNews::query()->create([
            'title' => 'Brouillon de veille',
            'summary' => 'Publication non visible.',
            'content' => 'Contenu brouillon.',
            'image_path' => 'bulletins/draft.jpg',
            'category' => 'veille',
            'published_at' => now()->subDays(2),
            'is_published' => false,
        ]);

        $this->get(route('veille'))
            ->assertOk()
            ->assertSee('value="all" selected', false)
            ->assertSee('aria-label="Nombre d’événements approuvés : 2"', false)
            ->assertSee('aria-label="Nombre d’événements avec décès signalés : 2"', false)
            ->assertSee('aria-label="Nombre de provinces renseignées : 1"', false)
            ->assertSee('chad-adm1.geojson')
            ->assertSee('\u0022latitude\u0022:8.44', false)
            ->assertSee('Affrontement')
            ->assertSee('Bulletin de veille publié')
            ->assertDontSee('Brouillon de veille')
            ->assertDontSee('Événement en attente')
            ->assertSee('synexVeilleTrendChart')
            ->assertSee('Derniers bulletins de veille');

        $this->get(route('veille', [
            'period' => 'all',
            'province' => 'Mandoul',
        ]))
            ->assertOk()
            ->assertSee('aria-label="Nombre d’événements approuvés : 2"', false)
            ->assertSee('aria-label="Nombre d’événements avec décès signalés : 2"', false)
            ->assertSee('Conflit intercommunautaire');

        $this->get(route('veille', [
            'period' => '30',
            'province' => 'Mandoul',
            'event_type' => 'Affrontement',
        ]))
            ->assertOk()
            ->assertSee('aria-label="Nombre d’événements approuvés : 1"', false)
            ->assertSee('aria-label="Nombre d’événements avec décès signalés : 1"', false)
            ->assertSee('Koundoul');
    }
}
