<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Models\ConflictEvent;
use App\Models\EventType;
use App\Models\OtherEvent;
use App\Models\SubEvent;
use App\Models\Subtheme;
use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SynexDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_related_filters_limit_the_event_list_and_map_points(): void
    {
        $eventType = EventType::create(['name' => 'Affrontement']);
        $subEvent = SubEvent::create(['event_type_id' => $eventType->id, 'name' => 'Affrontement armé']);
        $otherEvent = OtherEvent::create([
            'value_hash' => hash('sha256', 'Assassinat'),
            'description' => 'Assassinat',
        ]);
        $theme = Theme::create(['name' => 'Eleveur/agriculteur']);
        $subtheme = Subtheme::create(['theme_id' => $theme->id, 'name' => 'Dévastation de champ']);
        $actor = Actor::create(['name' => 'Eleveur']);

        $matchingEvent = ConflictEvent::create([
            'source_row' => 1,
            'event_year' => 2025,
            'event_date_raw' => '12-mai-25',
            'event_type' => 'Affrontement',
            'event_type_id' => $eventType->id,
            'sub_event' => 'Affrontement armé',
            'sub_event_id' => $subEvent->id,
            'other_event' => 'Assassinat',
            'other_event_id' => $otherEvent->id,
            'theme' => 'Eleveur/agriculteur',
            'theme_id' => $theme->id,
            'subtheme' => 'Dévastation de champ',
            'subtheme_id' => $subtheme->id,
            'province' => 'Mandoul',
            'locality' => 'Koumra',
            'latitude' => 8.1234567,
            'longitude' => 17.4567890,
            'deaths' => 4,
            'injuries' => 2,
        ]);

        ConflictEvent::create([
            'source_row' => 2,
            'event_year' => 2025,
            'event_type' => 'Affrontement',
            'event_type_id' => $eventType->id,
            'sub_event' => 'Affrontement armé',
            'sub_event_id' => $subEvent->id,
            'other_event' => 'Assassinat',
            'other_event_id' => $otherEvent->id,
            'theme' => 'Eleveur/agriculteur',
            'theme_id' => $theme->id,
            'subtheme' => 'Dévastation de champ',
            'subtheme_id' => $subtheme->id,
            'province' => 'Mandoul',
            'locality' => 'Doba',
            'latitude' => 9.9876543,
            'longitude' => 18.7654321,
        ]);

        DB::table('conflict_event_actors')->insert([
            'conflict_event_id' => $matchingEvent->id,
            'actor_id' => $actor->id,
            'role' => 'actor_1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('dashboard-analytics', [
            'year' => 2025,
            'province' => 'Mandoul',
            'event_type_id' => $eventType->id,
            'sub_event_id' => $subEvent->id,
            'other_event_id' => $otherEvent->id,
            'theme_id' => $theme->id,
            'subtheme_id' => $subtheme->id,
            'actor_id' => $actor->id,
        ]))
            ->assertOk()
            ->assertSee('sur 1 événements')
            ->assertSee('window.synexMapPoints')
        ->assertSee('window.synexMapProvinceCounts')
            ->assertSee('8.1234567')
        ->assertDontSee('9.9876543')
        ->assertViewHas('mapProvinceCounts', fn (array $counts) => $counts === ['Mandoul' => 1]);
    }
}
