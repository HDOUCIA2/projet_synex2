<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ConflictEvent;
use App\Models\PreventionInitiative;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $events = ConflictEvent::query();
        $totalEvents = (clone $events)->count();
        $pendingEvents = (clone $events)->where('status', 'pending')->count();
        $approvedEvents = (clone $events)->where('status', 'approved')->count();
        $rejectedEvents = (clone $events)->where('status', 'rejected')->count();

        $completeEvents = (clone $events)
            ->whereNotNull('event_year')
            ->whereNotNull('province')
            ->whereRaw("TRIM(province) <> ''")
            ->whereNotNull('event_type')
            ->whereRaw("TRIM(event_type) <> ''")
            ->whereNotNull('theme')
            ->whereRaw("TRIM(theme) <> ''")
            ->count();
        $geolocatedEvents = (clone $events)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-90, 90])
            ->whereBetween('longitude', [-180, 180])
            ->count();

        $annualRows = (clone $events)
            ->select('event_year')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('event_year')
            ->groupBy('event_year')
            ->orderBy('event_year')
            ->get();
        $provinceRows = (clone $events)
            ->select('province as label')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('province')
            ->whereRaw("TRIM(province) <> ''")
            ->groupBy('province')
            ->orderByDesc('total')
            ->limit(7)
            ->get();
        $themeRows = (clone $events)
            ->select('theme as label')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('theme')
            ->whereRaw("TRIM(theme) <> ''")
            ->groupBy('theme')
            ->orderByDesc('total')
            ->limit(7)
            ->get();

        $mapProvinceRows = (clone $events)
            ->select('province')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('province')
            ->whereRaw("TRIM(province) <> ''")
            ->groupBy('province')
            ->get();
        $mapTypeRows = (clone $events)
            ->selectRaw("COALESCE(NULLIF(TRIM(event_type), ''), 'Type non renseigné') AS label, COUNT(*) AS total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();
        $typePalette = ['#d64545', '#e49a20', '#3985b9', '#7460a8', '#31836f', '#cb5d88', '#66798c', '#aa7a36'];
        $eventTypeColors = $mapTypeRows
            ->values()
            ->mapWithKeys(fn ($row, $index) => [$row->label => $typePalette[$index % count($typePalette)]])
            ->all();
        $mapPoints = (clone $events)
            ->whereBetween('latitude', [7.44107, 23.4975])
            ->whereBetween('longitude', [13.47348, 24])
            ->get(['latitude', 'longitude', 'province', 'event_type', 'event_year', 'event_date_raw', 'status'])
            ->map(fn (ConflictEvent $event) => [
                'latitude' => (float) $event->latitude,
                'longitude' => (float) $event->longitude,
                'province' => $event->province,
                'eventType' => trim((string) $event->event_type) ?: 'Type non renseigné',
                'year' => $event->event_year,
                'date' => $event->event_date_raw,
                'status' => $event->status,
            ])
            ->all();

        $charts = [
            'annual' => [
                'labels' => $annualRows->pluck('event_year')->map(fn ($year) => (string) $year)->all(),
                'values' => $annualRows->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'provinces' => [
                'labels' => $provinceRows->pluck('label')->all(),
                'values' => $provinceRows->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
            'themes' => [
                'labels' => $themeRows->pluck('label')->all(),
                'values' => $themeRows->pluck('total')->map(fn ($total) => (int) $total)->all(),
            ],
        ];
        $mapData = [
            'geoJsonUrl' => asset('assets/geo/chad-adm1.geojson'),
            'provinceCounts' => $mapProvinceRows->mapWithKeys(fn ($row) => [$row->province => (int) $row->total])->all(),
            'eventTypeColors' => $eventTypeColors,
            'points' => $mapPoints,
        ];
        $mapTypeLegend = $mapTypeRows->values()->map(fn ($row, $index) => [
            'label' => $row->label,
            'count' => (int) $row->total,
            'color' => $typePalette[$index % count($typePalette)],
        ])->all();

        $recentEvents = (clone $events)
            ->select([
                'id',
                'event_year',
                'event_date_raw',
                'province',
                'locality',
                'event_type',
                'theme',
                'status',
            ])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $initiativeTotals = PreventionInitiative::query()
            ->where('is_published', true)
            ->select('type')
            ->selectRaw('SUM(records) AS total')
            ->groupBy('type')
            ->pluck('total', 'type');
        $initiativeMetrics = collect(PreventionInitiative::TYPES)
            ->keys()
            ->mapWithKeys(fn (string $type) => [$type => (int) $initiativeTotals->get($type, 0)])
            ->all();

        return view('content.admin.dashboard', [
            'totalEvents' => $totalEvents,
            'pendingEvents' => $pendingEvents,
            'approvedEvents' => $approvedEvents,
            'rejectedEvents' => $rejectedEvents,
            'initiativeMetrics' => $initiativeMetrics,
            'completeEvents' => $completeEvents,
            'geolocatedEvents' => $geolocatedEvents,
            'completionRate' => $totalEvents > 0 ? ($completeEvents / $totalEvents) * 100 : 0,
            'charts' => $charts,
            'mapData' => $mapData,
            'mapTypeLegend' => $mapTypeLegend,
            'recentEvents' => $recentEvents,
        ]);
    }
}
