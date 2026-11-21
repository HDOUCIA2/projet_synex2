<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\ConflictEvent;
use App\Models\EventType;
use App\Models\OtherEvent;
use App\Models\SubEvent;
use App\Models\Subtheme;
use App\Models\Theme;
use App\Models\VeilleSection;
use App\Services\DeathRateCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SynexDashboard extends Controller
{
    public function index(Request $request, DeathRateCalculator $rateCalculator)
    {
        $years = ConflictEvent::query()
            ->approved()
            ->whereNotNull('event_year')
            ->distinct()
            ->orderByDesc('event_year')
            ->pluck('event_year')
            ->all();
        $provinces = ConflictEvent::query()
            ->approved()
            ->whereNotNull('province')
            ->distinct()
            ->orderBy('province')
            ->pluck('province')
            ->all();
        $eventTypes = EventType::query()->orderBy('name')->get(['id', 'name']);
        $subEvents = SubEvent::query()->with('eventType')->orderBy('name')->get();
        $otherEvents = OtherEvent::query()->orderBy('description')->get(['id', 'description']);
        $themes = Theme::query()->orderBy('name')->get(['id', 'name']);
        $subthemes = Subtheme::query()->with('theme')->orderBy('name')->get();
        $actors = Actor::query()->orderBy('name')->get(['id', 'name']);

        $filters = $request->validate([
            'year' => ['nullable', 'integer', Rule::in($years)],
            'province' => ['nullable', 'string', Rule::in($provinces)],
            'event_type_id' => ['nullable', 'integer', Rule::exists('event_types', 'id')],
            'sub_event_id' => ['nullable', 'integer', Rule::exists('sub_events', 'id')],
            'other_event_id' => ['nullable', 'integer', Rule::exists('other_events', 'id')],
            'theme_id' => ['nullable', 'integer', Rule::exists('themes', 'id')],
            'subtheme_id' => ['nullable', 'integer', Rule::exists('subthemes', 'id')],
            'actor_id' => ['nullable', 'integer', Rule::exists('actors', 'id')],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $query = ConflictEvent::query()
            ->approved()
            ->when($filters['year'] ?? null, fn (Builder $query, $year) => $query->where('event_year', $year))
            ->when($filters['province'] ?? null, fn (Builder $query, $province) => $query->where('province', $province))
            ->when($filters['event_type_id'] ?? null, fn (Builder $query, $id) => $query->where('event_type_id', $id))
            ->when($filters['sub_event_id'] ?? null, fn (Builder $query, $id) => $query->where('sub_event_id', $id))
            ->when($filters['other_event_id'] ?? null, fn (Builder $query, $id) => $query->where('other_event_id', $id))
            ->when($filters['theme_id'] ?? null, fn (Builder $query, $id) => $query->where('theme_id', $id))
            ->when($filters['subtheme_id'] ?? null, fn (Builder $query, $id) => $query->where('subtheme_id', $id))
            ->when($filters['actor_id'] ?? null, fn (Builder $query, $id) => $query->whereHas(
                'actors',
                fn (Builder $actorQuery) => $actorQuery->where('actors.id', $id)
            ))
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('province', 'like', "%{$search}%")
                        ->orWhere('locality', 'like', "%{$search}%")
                        ->orWhere('event_notes', 'like', "%{$search}%")
                        ->orWhereHas('eventTypeRecord', fn (Builder $related) => $related->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subEventRecord', fn (Builder $related) => $related->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('otherEventRecord', fn (Builder $related) => $related->where('description', 'like', "%{$search}%"))
                        ->orWhereHas('themeRecord', fn (Builder $related) => $related->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('subthemeRecord', fn (Builder $related) => $related->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('actors', fn (Builder $related) => $related->where('actors.name', 'like', "%{$search}%"));
                });
            });

        $totals = (clone $query)->selectRaw(
            'COUNT(*) AS event_count,
            COALESCE(SUM(deaths), 0) AS deaths_total,
            COALESCE(SUM(injuries), 0) AS injuries_total,
            COALESCE(SUM(displaced_people), 0) AS displaced_total'
        )->first();

        $localityCount = (clone $query)
            ->whereNotNull('locality')
            ->where('locality', '<>', '')
            ->distinct()
            ->count('locality');

        $eventsByYear = (clone $query)
            ->select('event_year')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('event_year')
            ->groupBy('event_year')
            ->orderBy('event_year')
            ->get();
        $eventsByType = (clone $query)
            ->join('event_types as dimension', 'conflict_events.event_type_id', '=', 'dimension.id')
            ->selectRaw('dimension.name AS label, COUNT(*) AS total')
            ->groupBy('dimension.id', 'dimension.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
        $eventsByProvince = (clone $query)
            ->select('province as label')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('province')
            ->groupBy('province')
            ->orderByDesc('total')
            ->get();
        $eventsByTheme = (clone $query)
            ->join('themes as dimension', 'conflict_events.theme_id', '=', 'dimension.id')
            ->selectRaw('dimension.name AS label, COUNT(*) AS total')
            ->groupBy('dimension.id', 'dimension.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $chartData = [
            'eventsByYear' => [
                'labels' => $eventsByYear->pluck('event_year')->map(fn ($year) => (string) $year)->all(),
                'values' => $eventsByYear->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'eventsByType' => [
                'labels' => $eventsByType->pluck('label')->all(),
                'values' => $eventsByType->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'eventsByProvince' => [
                'labels' => $eventsByProvince->pluck('label')->all(),
                'values' => $eventsByProvince->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'eventsByTheme' => [
                'labels' => $eventsByTheme->pluck('label')->all(),
                'values' => $eventsByTheme->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'impacts' => [
                'labels' => ['Décès', 'Blessés', 'Personnes déplacées'],
                'values' => [
                    (int) $totals->deaths_total,
                    (int) $totals->injuries_total,
                    (int) $totals->displaced_total,
                ],
            ],
        ];

        $populationYears = $rateCalculator->availableYears();
        $deathRateYear = isset($filters['year'])
            ? (int) $filters['year']
            : $populationYears->first();
        $deathRates = $rateCalculator->forYear($deathRateYear, $query);
        $chartData['deathRate'] = [
            'labels' => $deathRates->pluck('province')->all(),
            'values' => $deathRates->pluck('rate')->all(),
        ];

        $dashboardSections = VeilleSection::query()
            ->with(['cards' => fn ($cardQuery) => $cardQuery->where('is_active', true)])
            ->where('page', 'dashboard')
            ->orderBy('display_order')
            ->get()
            ->keyBy('key');
        $metricValues = [
            'event_count' => (int) $totals->event_count,
            'deaths_total' => (int) $totals->deaths_total,
            'injuries_total' => (int) $totals->injuries_total,
            'locality_count' => $localityCount,
        ];

        $mapPoints = (clone $query)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180])
            ->with(['eventTypeRecord', 'subEventRecord', 'actors'])
            ->get()
            ->map(fn (ConflictEvent $event) => [
                'latitude' => (float) $event->latitude,
                'longitude' => (float) $event->longitude,
                'date' => $event->event_date_raw,
                'eventType' => $event->eventTypeRecord?->name,
                'subEvent' => $event->subEventRecord?->name,
                'province' => $event->province,
                'locality' => $event->locality,
                'actors' => $event->actors->pluck('name')->all(),
                'deaths' => $event->deaths,
                'injuries' => $event->injuries,
            ])
            ->values()
            ->all();

        $events = (clone $query)
            ->with([
                'eventTypeRecord',
                'subEventRecord',
                'otherEventRecord',
                'themeRecord',
                'subthemeRecord',
                'actors',
            ])
            ->orderByDesc('event_year')
            ->orderByDesc('source_row')
            ->paginate(15)
            ->withQueryString();

        return view('content.dashboard.dashboards-synex', compact(
            'actors',
            'chartData',
            'dashboardSections',
            'deathRateYear',
            'deathRates',
            'events',
            'eventTypes',
            'filters',
            'localityCount',
            'mapPoints',
            'metricValues',
            'otherEvents',
            'provinces',
            'subEvents',
            'subthemes',
            'themes',
            'totals',
            'years'
        ));
    }
}
