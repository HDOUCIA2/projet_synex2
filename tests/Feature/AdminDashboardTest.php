<?php

namespace Tests\Feature;

use App\Models\ConflictEvent;
use App\Models\ContactSubmission;
use App\Models\PreventionInitiative;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_is_admin_only(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('auth-login-basic'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_uses_existing_observations_and_shows_data_quality(): void
    {
        ConflictEvent::query()->create([
            'source_row' => 900001,
            'event_year' => 2025,
            'province' => 'Mandoul',
            'event_type' => 'Conflit communautaire',
            'theme' => 'Accès aux ressources',
            'status' => 'approved',
            'latitude' => 8.65,
            'longitude' => 16.85,
        ]);
        ConflictEvent::query()->create([
            'source_row' => 900002,
            'status' => 'pending',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewIs('content.admin.dashboard')
            ->assertViewHas('totalEvents', 2)
            ->assertViewHas('completeEvents', 1)
            ->assertViewHas('geolocatedEvents', 1)
            ->assertViewHas('charts', fn (array $charts) => $charts['annual']['labels'] === ['2025']
                && $charts['annual']['values'] === [1]
                && $charts['provinces']['labels'] === ['Mandoul']
                && $charts['themes']['labels'] === ['Accès aux ressources'])
            ->assertViewHas('mapData', fn (array $mapData) => count($mapData['points']) === 1
                && $mapData['provinceCounts']['Mandoul'] === 1
                && isset($mapData['eventTypeColors']['Conflit communautaire']))
            ->assertSee('Mandoul')
            ->assertSee('Accès aux ressources')
            ->assertSee('50,0');
    }

    public function test_admin_map_excludes_coordinates_outside_chad(): void
    {
        ConflictEvent::query()->create([
            'source_row' => 900004,
            'province' => 'Mandoul',
            'event_type' => 'Conflit communautaire',
            'status' => 'approved',
            'latitude' => 8.65,
            'longitude' => 16.85,
        ]);
        ConflictEvent::query()->create([
            'source_row' => 900005,
            'province' => 'Mandoul',
            'event_type' => 'Conflit communautaire',
            'status' => 'approved',
            'latitude' => 35.0,
            'longitude' => 40.0,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('mapData', fn (array $mapData) => count($mapData['points']) === 1
                && $mapData['provinceCounts']['Mandoul'] === 2);
    }

    public function test_public_navigation_routes_dashboard_and_profile_buttons_by_role(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('href="'.route('dashboard-analytics-two').'" class="btn btn-primary', false)
            ->assertSee('href="'.route('admin.dashboard').'"', false)
            ->assertSee('aria-label="Mon profil" title="Mon profil"', false)
            ->assertDontSee('<span>Mon profil</span>', false);

        $this->actingAs(User::factory()->create())
            ->get(route('contact'))
            ->assertOk()
            ->assertSee('href="'.route('dashboard-analytics-two').'" class="btn btn-primary', false)
            ->assertSee('href="'.route('pages-account-settings-account').'"', false)
            ->assertDontSee('<span>Mon profil</span>', false);
    }

    public function test_admin_home_redirects_to_the_admin_dashboard_without_changing_user_dashboard_access(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.home'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard-analytics'))
            ->assertOk();
    }

    public function test_admin_notifications_show_unread_contact_messages_and_events_to_review(): void
    {
        $contact = ContactSubmission::query()->create([
            'name' => 'Amina Test',
            'email' => 'amina@example.test',
            'subject' => 'Demande de suivi',
            'message' => 'Merci de me recontacter.',
        ]);
        $event = ConflictEvent::query()->create([
            'source_row' => 900003,
            'event_year' => 2026,
            'province' => 'Mandoul',
            'event_type' => 'Observation récente',
            'theme' => 'Cohésion sociale',
            'status' => 'pending',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Nouveau message de Amina Test')
            ->assertSee('Demande de suivi')
            ->assertSee(route('admin.contact.show', $contact), false)
            ->assertSee('Observation à valider')
            ->assertSee('Observation récente · Mandoul')
            ->assertSee(route('admin.events.edit', $event), false);
    }

    public function test_prevention_cards_use_published_record_totals_by_initiative_type(): void
    {
        $publishedCampaignRecords = (int) PreventionInitiative::query()
            ->where('type', 'campaign')
            ->where('is_published', true)
            ->sum('records');

        PreventionInitiative::query()->create([
            'title' => 'Campagne publiée de test',
            'type' => 'campaign',
            'province' => 'Mandoul',
            'records' => 7,
            'is_published' => true,
        ]);
        PreventionInitiative::query()->create([
            'title' => 'Campagne brouillon de test',
            'type' => 'campaign',
            'province' => 'Mandoul',
            'records' => 11,
            'is_published' => false,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord administrateur')
            ->assertViewHas('initiativeMetrics', fn (array $metrics) => $metrics['campaign'] === $publishedCampaignRecords + 7);
    }
}
