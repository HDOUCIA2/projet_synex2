<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\ConflictEvent;
use App\Models\ProgramNews;
use App\Models\VeilleSection;
use App\Support\ConflictEventDateParser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VeillePage extends Controller
{
    public function index(Request $request)
    {
        $veilleSections = VeilleSection::query()
            ->with(['cards' => fn ($query) => $query->where('is_active', true)])
            ->where('page', 'home')
            ->whereIn('key', ['home_features', 'home_severity'])
            ->get()
            ->keyBy('key');

        $approvedEvents = ConflictEvent::query()->approved();
        $provinces = (clone $approvedEvents)
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->distinct()
            ->orderBy('province')
            ->pluck('province')
            ->all();
        $eventTypes = (clone $approvedEvents)
            ->whereNotNull('event_type')
            ->where('event_type', '<>', '')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type')
            ->all();
        $periodOptions = [
            '7' => '7 derniers jours',
            '30' => '30 derniers jours',
            '90' => '90 derniers jours',
            '365' => '12 derniers mois',
            'all' => 'Toute la période',
        ];

        $filters = $request->validate([
            'period' => ['nullable', Rule::in(array_keys($periodOptions))],
            'province' => ['nullable', 'string', Rule::in($provinces)],
            'event_type' => ['nullable', 'string', Rule::in($eventTypes)],
        ]);
        $period = $filters['period'] ?? 'all';
        $events = (clone $approvedEvents)
            ->when($filters['province'] ?? null, fn (Builder $query, $province) => $query->where('province', $province))
            ->when($filters['event_type'] ?? null, fn (Builder $query, $eventType) => $query->where('event_type', $eventType))
            ->get([
                'id',
                'source_row',
                'event_year',
                'event_date_raw',
                'event_type',
                'province',
                'locality',
                'latitude',
                'longitude',
                'deaths',
                'injuries',
                'source',
                'source_url_1',
                'source_url_2',
                'source_url_3',
            ]);

        $today = CarbonImmutable::today();
        $periodStart = $period === 'all' ? null : $today->subDays((int) $period - 1);
        $unparsedDateCount = $period === 'all'
            ? 0
            : $events->filter(fn (ConflictEvent $event) => ConflictEventDateParser::parse($event->event_date_raw, $event->event_year) === null)->count();
        $filteredEvents = $period === 'all'
            ? $events
            : $events->filter(function (ConflictEvent $event) use ($periodStart, $today) {
                $date = ConflictEventDateParser::parse($event->event_date_raw, $event->event_year);

                return $date !== null && $date->greaterThanOrEqualTo($periodStart) && $date->lessThanOrEqualTo($today);
            })->values();

        $eventCount = $filteredEvents->count();
        $deathsTotal = (int) $filteredEvents->sum(fn (ConflictEvent $event) => $event->deaths ?? 0);
        $eventsWithDeaths = $filteredEvents->filter(fn (ConflictEvent $event) => ($event->deaths ?? 0) > 0)->count();
        $provinceCount = $filteredEvents->pluck('province')->filter()->unique()->count();
        $provinceEventCounts = $filteredEvents
            ->filter(fn (ConflictEvent $event) => filled($event->province))
            ->groupBy('province')
            ->map(fn ($items) => $items->count())
            ->all();
        $mapPoints = $filteredEvents
            ->filter(fn (ConflictEvent $event) => $event->latitude !== null && $event->longitude !== null
                && $event->latitude >= 7.44107 && $event->latitude <= 23.4975
                && $event->longitude >= 13.47348 && $event->longitude <= 24)
            ->map(fn (ConflictEvent $event) => [
                'latitude' => (float) $event->latitude,
                'longitude' => (float) $event->longitude,
                'province' => $event->province,
                'locality' => $event->locality,
                'date' => $event->event_date_raw,
                'eventType' => $event->event_type,
                'deaths' => $event->deaths,
                'injuries' => $event->injuries,
            ])
            ->values()
            ->all();
        $recentEvents = $filteredEvents
            ->sortByDesc(fn (ConflictEvent $event) => ConflictEventDateParser::parse($event->event_date_raw, $event->event_year)?->timestamp
                ?? ((int) $event->event_year * 100000 + (int) $event->source_row))
            ->take(4)
            ->values();
        $trend = $period === 'all'
            ? $filteredEvents
                ->filter(fn (ConflictEvent $event) => $event->event_year !== null)
                ->groupBy('event_year')
                ->map(fn ($items, $year) => ['label' => (string) $year, 'total' => $items->count()])
                ->sortKeys()
                ->values()
            : collect(range(0, min(6, (int) $period - 1)))->map(function (int $index) use ($period, $periodStart, $filteredEvents) {
                $days = (int) $period;
                $bucketCount = min(7, $days);
                $startOffset = (int) floor($index * $days / $bucketCount);
                $start = $periodStart->addDays($startOffset);
                $label = $start->locale('fr')->isoFormat('D MMM');
                $count = $filteredEvents->filter(function (ConflictEvent $event) use ($periodStart, $days, $bucketCount, $index) {
                    $date = ConflictEventDateParser::parse($event->event_date_raw, $event->event_year);
                    if ($date === null) {
                        return false;
                    }

                    $dayOffset = $periodStart->diffInDays($date);
                    $eventBucket = min($bucketCount - 1, (int) floor($dayOffset * $bucketCount / $days));

                    return $eventBucket === $index;
                })->count();

                return ['label' => $label, 'total' => $count];
            });

        $bulletinsQuery = ProgramNews::query()->published()->where('category', 'veille');
        if ($periodStart !== null) {
            $bulletinsQuery->whereBetween('published_at', [$periodStart, $today->endOfDay()]);
        }
        $bulletins = (clone $bulletinsQuery)->orderByDesc('published_at')->limit(4)->get();
        $bulletinCount = (clone $bulletinsQuery)->count();
        $sources = $filteredEvents
            ->flatMap(function (ConflictEvent $event) {
                return collect([$event->source_url_1, $event->source_url_2, $event->source_url_3])
                    ->filter(fn ($url) => is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true))
                    ->map(fn ($url) => [
                        'label' => $event->source ?: (parse_url($url, PHP_URL_HOST) ?: 'Source externe'),
                        'url' => $url,
                    ]);
            })
            ->unique('url')
            ->take(6)
            ->values();

        $veilleData = [
            'geoJsonUrl' => asset('assets/geo/chad-adm1.geojson'),
            'points' => $mapPoints,
            'provinceEventCounts' => $provinceEventCounts,
            'trend' => [
                'labels' => $trend->pluck('label')->all(),
                'values' => $trend->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
        ];

        return view('content.pages.veille', [
            'bulletinCount' => $bulletinCount,
            'bulletins' => $bulletins,
            'deathsTotal' => $deathsTotal,
            'eventCount' => $eventCount,
            'eventsWithDeaths' => $eventsWithDeaths,
            'eventTypes' => $eventTypes,
            'filters' => $filters,
            'mapPoints' => $mapPoints,
            'period' => $period,
            'periodLabel' => $periodOptions[$period],
            'periodOptions' => $periodOptions,
            'provinceCount' => $provinceCount,
            'provinces' => $provinces,
            'recentEvents' => $recentEvents,
            'sources' => $sources,
            'trend' => $trend,
            'unparsedDateCount' => $unparsedDateCount,
            'veilleData' => $veilleData,
            'veilleSections' => $veilleSections,
        ]);
    }
}
