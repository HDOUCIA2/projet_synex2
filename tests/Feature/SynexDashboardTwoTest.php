<?php

namespace Tests\Feature;

use App\Models\ConflictEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SynexDashboardTwoTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_dashboard_shows_only_approved_aggregates_and_filters(): void
    {
        ConflictEvent::create([
            'source_row' => 1,
            'status' => 'approved',
            'event_year' => 2025,
            'event_date_raw' => '10-janv-25',
            'event_type' => 'Développement stratégique',
            'sub_event' => 'Médiation',
            'theme' => 'Communautaire',
            'province' => 'Mandoul',
            'department' => 'Koumra',
            'actor_1' => 'Éleveur',
            'actor_2' => 'Agriculteur',
            'latitude' => 8.1234567,
            'longitude' => 16.7654321,
            'conflict_number' => 'C-1',
        ]);
        ConflictEvent::create([
            'source_row' => 2,
            'status' => 'pending',
            'event_year' => 2025,
            'event_type' => 'Événement privé',
            'province' => 'Mandoul',
        ]);
        foreach ([
            3 => 'Arbitrage',
            4 => 'Accord de paix',
            5 => 'Campagne de prévention',
            6 => null,
        ] as $sourceRow => $response) {
            ConflictEvent::create([
                'source_row' => $sourceRow,
                'status' => 'approved',
                'event_year' => 2025,
                'event_type' => 'Développement stratégique',
                'sub_event' => $response,
                'theme' => 'Communautaire',
                'province' => 'Mandoul',
            ]);
        }

        $this->get(route('dashboard-analytics-two', ['year' => 2025, 'province' => 'Mandoul']))
            ->assertOk()
            ->assertSee('Tableau de bord des dynamiques de conflits au Tchad')
            ->assertSee(route('dashboard-analytics-two'))
            ->assertSee('Observations datées')
            ->assertSee('10-janv-25')
            ->assertSee('Médiation')
            ->assertSee('bx-group')
            ->assertSee('bx-scale')
            ->assertSee('bx-dove')
            ->assertSee('bx-bullhorn')
            ->assertSee('bx-dots-horizontal-rounded')
            ->assertDontSee('Événement privé')
            ->assertDontSee('8.1234567')
            ->assertDontSee('16.7654321');
    }
}
