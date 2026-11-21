<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\ConflictEvent;
use App\Models\ProgramNews;

class HomePageTwo extends Controller
{
    public function __invoke()
    {
        $approvedEvents = ConflictEvent::query()->approved();
        $eventCount = (clone $approvedEvents)->count();
        $deathCount = (clone $approvedEvents)->sum('deaths');
        $provinceCount = (clone $approvedEvents)
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->distinct()
            ->count('province');
        $provinceEventCounts = (clone $approvedEvents)
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->select('province')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('province')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->province => (int) $row->total])
            ->all();
        $provinceEventTypeCounts = (clone $approvedEvents)
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->select('province', 'event_type')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('province', 'event_type')
            ->get()
            ->groupBy('province')
            ->map(fn ($types) => $types
                ->groupBy(fn ($row) => trim((string) $row->event_type) ?: 'Type non renseigné')
                ->map(fn ($rows) => (int) $rows->sum('total'))
                ->sortDesc()
                ->all())
            ->all();
        $eventTypeCounts = (clone $approvedEvents)
            ->select('event_type')
            ->selectRaw('COUNT(*) AS total')
            ->groupBy('event_type')
            ->get()
            ->map(fn ($row) => [
                'label' => trim((string) $row->event_type) ?: 'Type non renseigné',
                'total' => (int) $row->total,
            ])
            ->groupBy('label')
            ->map(fn ($types) => [
                'label' => $types->first()['label'],
                'total' => (int) $types->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();
        $eventTypePalette = ['#D64545', '#E49A20', '#3985B9'];
        $eventTypeColors = $eventTypeCounts
            ->values()
            ->mapWithKeys(fn ($type, $index) => [
                $type['label'] => $eventTypePalette[$index] ?? '#AF8807',
            ])
            ->all();
        $heroEventTypes = $eventTypeCounts->take(3)->values();
        if ($eventTypeCounts->count() > 3) {
            $heroEventTypes->push([
                'label' => 'Autres types',
                'total' => (int) $eventTypeCounts->skip(3)->sum('total'),
            ]);
        }
        $mapPoints = (clone $approvedEvents)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [7.44107, 23.4975])
            ->whereBetween('longitude', [13.47348, 24])
            ->get(['latitude', 'longitude', 'province', 'event_date_raw', 'event_type', 'deaths', 'injuries'])
            ->map(fn (ConflictEvent $event) => [
                'latitude' => (float) $event->latitude,
                'longitude' => (float) $event->longitude,
                'province' => $event->province,
                'date' => $event->event_date_raw,
                'eventType' => $event->event_type,
                'deaths' => $event->deaths,
                'injuries' => $event->injuries,
            ])
            ->all();

        $eventsByYear = (clone $approvedEvents)
            ->select('event_year')
            ->selectRaw('COUNT(*) AS total')
            ->whereNotNull('event_year')
            ->groupBy('event_year')
            ->orderByDesc('event_year')
            ->limit(6)
            ->get()
            ->sortBy('event_year')
            ->values();

        $publishedNews = ProgramNews::query()->published();
        $newsCount = (clone $publishedNews)->count();
        $newsItems = (clone $publishedNews)
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('content.pages.home-page-2', compact(
            'deathCount',
            'eventCount',
            'eventTypeColors',
            'heroEventTypes',
            'eventsByYear',
            'newsCount',
            'newsItems',
            'mapPoints',
            'provinceCount',
            'provinceEventCounts',
            'provinceEventTypeCounts'
        ));
    }
}
