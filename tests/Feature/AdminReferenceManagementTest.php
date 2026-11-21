<?php

namespace Tests\Feature;

use App\Models\ConflictEvent;
use App\Models\EventType;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReferenceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_manage_event_types_and_sub_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->get(route('admin.references.index', 'event-types'))
            ->assertOk()
            ->assertSee('Types d’événement');

        $this->post(route('admin.references.store', 'event-types'), [
            'name' => 'Affrontement',
        ])->assertRedirect(route('admin.references.index', 'event-types'));

        $eventType = EventType::where('name', 'Affrontement')->firstOrFail();
        $this->post(route('admin.references.store', 'sub-events'), [
            'name' => 'Affrontement armé',
            'event_type_id' => $eventType->id,
        ])->assertRedirect(route('admin.references.index', 'sub-events'));

        $this->assertDatabaseHas('sub_events', [
            'name' => 'Affrontement armé',
            'event_type_id' => $eventType->id,
        ]);
    }

    public function test_regular_users_cannot_manage_references(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.references.index', 'event-types'))
            ->assertForbidden();
    }

    public function test_used_event_type_cannot_be_deleted_and_renames_update_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $eventType = EventType::create(['name' => 'Affrontement']);
        ConflictEvent::create([
            'source_row' => 1,
            'event_type_id' => $eventType->id,
            'event_type' => 'Affrontement',
            'event_year' => 2025,
            'province' => 'Mandoul',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.references.update', ['catalog' => 'event-types', 'item' => $eventType->id]), [
                'name' => 'Affrontement armé',
            ])
            ->assertRedirect(route('admin.references.index', 'event-types'));

        $this->assertDatabaseHas('conflict_events', ['event_type' => 'Affrontement armé']);

        $this->delete(route('admin.references.destroy', ['catalog' => 'event-types', 'item' => $eventType->id]))
            ->assertSessionHasErrors('catalog');
    }

    public function test_administrator_can_block_and_reactivate_a_reference(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $province = Province::create(['name' => 'Mandoul']);

        $this->actingAs($admin)
            ->patch(route('admin.references.status', ['catalog' => 'provinces', 'item' => $province->id]))
            ->assertRedirect(route('admin.references.index', 'provinces'));

        $this->assertDatabaseHas('provinces', ['id' => $province->id, 'is_active' => false]);

        $this->patch(route('admin.references.status', ['catalog' => 'provinces', 'item' => $province->id]))
            ->assertRedirect(route('admin.references.index', 'provinces'));

        $this->assertDatabaseHas('provinces', ['id' => $province->id, 'is_active' => true]);
    }
}
