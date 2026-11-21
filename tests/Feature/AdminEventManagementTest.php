<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\ConflictEvent;
use App\Models\EventType;
use App\Models\Province;
use App\Models\SubEvent;
use App\Models\Subtheme;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_an_event_from_managed_references(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $province = Province::create(['name' => 'Mandoul']);
        $eventType = EventType::create(['name' => 'Affrontement']);
        $subEvent = SubEvent::create(['event_type_id' => $eventType->id, 'name' => 'Affrontement armé']);
        $theme = Theme::create(['name' => 'Communautaire']);
        $subtheme = Subtheme::create(['theme_id' => $theme->id, 'name' => 'Accès aux ressources']);
        $actor = Actor::create(['name' => 'Eleveur']);

        $this->actingAs($user)
            ->get(route('admin.events.create'))
            ->assertOk()
            ->assertDontSee('Gérer les référentiels');

        $this->post(route('admin.events.store'), [
                'event_year' => 2025,
                'event_date_raw' => '12-mai-25',
                'province_id' => $province->id,
                'event_type_id' => $eventType->id,
                'sub_event_id' => $subEvent->id,
                'theme_id' => $theme->id,
                'subtheme_id' => $subtheme->id,
                'actor_1_id' => $actor->id,
                'locality' => 'Koumra',
                'latitude' => '8.1234567',
                'longitude' => '17.4567890',
                'deaths' => 2,
            ])
            ->assertRedirect();

        $event = ConflictEvent::where('locality', 'Koumra')->firstOrFail();
        $this->assertNull($event->source_row);
        $this->assertSame($user->id, $event->created_by_user_id);
        $this->assertSame('pending', $event->status);
        $this->assertSame('Mandoul', $event->province);
        $this->assertSame('Affrontement', $event->event_type);
        $this->assertDatabaseHas('conflict_event_actors', [
            'conflict_event_id' => $event->id,
            'actor_id' => $actor->id,
            'role' => 'actor_1',
        ]);
    }

    public function test_user_cannot_edit_another_users_or_imported_event(): void
    {
        $event = ConflictEvent::create([
            'source_row' => 1,
            'event_year' => 2025,
            'province' => 'Mandoul',
        ]);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.events.edit', $event))
            ->assertNotFound();

        $this->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Compte utilisateur')
            ->assertSee('Mandoul')
            ->assertDontSee(route('admin.events.edit', $event));
    }

    public function test_user_events_require_admin_approval_before_publication(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $province = Province::create(['name' => 'Mandoul']);
        $eventType = EventType::create(['name' => 'Affrontement']);
        $theme = Theme::create(['name' => 'Communautaire']);

        $this->actingAs($user)->post(route('admin.events.store'), [
            'event_year' => 2025,
            'province_id' => $province->id,
            'event_type_id' => $eventType->id,
            'theme_id' => $theme->id,
            'locality' => 'Koumra',
        ])->assertRedirect();

        $event = ConflictEvent::query()->where('locality', 'Koumra')->firstOrFail();
        $this->assertSame('pending', $event->status);
        $this->get(route('dashboard-analytics'))->assertDontSee('Koumra');
        $this->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('En attente')
            ->assertSee(route('admin.events.edit', $event));

        $this->patch(route('admin.events.review', $event), [
            'status' => 'approved',
        ])->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Compte administrateur')
            ->assertSee('Modifier')
            ->assertSee('Supprimer')
            ->assertDontSee('Refuser')
            ->assertSee('Publier');

        $this->patch(route('admin.events.review', $event), ['status' => 'approved'])
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('conflict_events', ['id' => $event->id, 'status' => 'approved']);
        $this->get(route('dashboard-analytics'))->assertSee('Koumra');
        $this->actingAs($admin)->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Publié')
            ->assertSee('Dépublier');

        $this->patch(route('admin.events.review', $event), ['status' => 'pending'])
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('conflict_events', ['id' => $event->id, 'status' => 'pending']);
        $this->get(route('dashboard-analytics'))->assertDontSee('Koumra');
    }

    public function test_users_cannot_submit_an_event_using_a_blocked_reference(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $province = Province::create(['name' => 'Mandoul']);
        $province->is_active = false;
        $province->save();
        $eventType = EventType::create(['name' => 'Affrontement']);
        $theme = Theme::create(['name' => 'Communautaire']);

        $this->actingAs($user)
            ->from(route('admin.events.create'))
            ->post(route('admin.events.store'), [
                'event_year' => 2025,
                'province_id' => $province->id,
                'event_type_id' => $eventType->id,
                'theme_id' => $theme->id,
            ])
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHasErrors('province_id');

        $this->assertDatabaseCount('conflict_events', 0);
    }

    public function test_admin_can_create_an_event_without_publishing_it(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $province = Province::create(['name' => 'Mandoul']);
        $eventType = EventType::create(['name' => 'Affrontement']);
        $theme = Theme::create(['name' => 'Communautaire']);

        $this->actingAs($admin)
            ->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('Gérer les référentiels')
            ->assertSee('Ne pas publier — en attente');

        $this->post(route('admin.events.store'), [
            'event_year' => 2025,
            'province_id' => $province->id,
            'event_type_id' => $eventType->id,
            'theme_id' => $theme->id,
            'locality' => 'Koumra',
            'status' => 'pending',
        ])->assertRedirect();

        $this->assertDatabaseHas('conflict_events', [
            'locality' => 'Koumra',
            'status' => 'pending',
        ]);
        $this->get(route('dashboard-analytics'))->assertDontSee('Koumra');
    }

    public function test_admin_can_edit_and_delete_an_existing_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = ConflictEvent::create([
            'source_row' => null,
            'event_year' => 2025,
            'province' => 'Mandoul',
            'locality' => 'Koumra',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.edit', $event))
            ->assertOk();

        $this->deleteJson(route('admin.events.destroy', $event))
            ->assertOk()
            ->assertJsonPath('message', 'L’événement a été supprimé.');

        $this->assertDatabaseMissing('conflict_events', ['id' => $event->id]);
    }

    public function test_ajax_event_list_returns_bootstrap_pagination_for_requested_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        foreach (range(1, 21) as $number) {
            ConflictEvent::create([
                'event_year' => 2025,
                'province' => 'Mandoul',
                'locality' => "Lieu {$number}",
                'status' => 'approved',
            ]);
        }

        $response = $this->actingAs($admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson(route('admin.events.index', ['page' => 2]));

        $response->assertOk()
            ->assertJsonPath('currentPage', 2)
            ->assertJsonPath('lastPage', 3);
        $html = $response->json('html');
        $this->assertStringContainsString('pagination', $html);
        $this->assertStringContainsString('table-sm', $html);
        $this->assertStringContainsString('Lieu 11', $html);
        $this->assertSame(11, substr_count($html, '<tr>'));
    }

    public function test_admin_can_publish_and_unpublish_event_with_ajax(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = ConflictEvent::create([
            'event_year' => 2025,
            'province' => 'Mandoul',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.events.review', $event), ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('message', 'L’événement a été publié.');
        $this->assertDatabaseHas('conflict_events', ['id' => $event->id, 'status' => 'approved']);

        $this->patchJson(route('admin.events.review', $event), ['status' => 'pending'])
            ->assertOk()
            ->assertJsonPath('message', 'L’événement a été dépublié et remis en attente.');
        $this->assertDatabaseHas('conflict_events', ['id' => $event->id, 'status' => 'pending']);
    }
}
