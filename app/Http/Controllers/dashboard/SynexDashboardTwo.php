<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\ConflictEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SynexDashboardTwo extends Controller
{
    public function index(Request $request)
    {
        $approvedEvents = ConflictEvent::query()->approved();
        $years = (clone $approvedEvents)
            ->whereNotNull('event_year')
            ->distinct()
            ->orderBy('event_year')
            ->pluck('event_year')
            ->all();
        $provinces = $this->distinctValues($approvedEvents, 'province');
        $departments = $this->distinctValues($approvedEvents, 'department');
        $themes = $this->distinctValues($approvedEvents, 'theme');
        $eventTypes = $this->distinctValues($approvedEvents, 'event_type');
        $actors = Actor::query()->orderBy('name')->pluck('name')->all();

        $filters = $request->validate([
            'year' => ['nullable', 'integer', Rule::in($years)],
            'province' => ['nullable', 'string', Rule::in($provinces)],
            'department' => ['nullable', 'string', Rule::in($departments)],
            'theme' => ['nullable', 'string', Rule::in($themes)],
            'event_type' => ['nullable', 'string', Rule::in($eventTypes)],
            'actor' => ['nullable', 'string', Rule::in($actors)],
        ]);

        $query = (clone $approvedEvents)
            ->when($filters['year'] ?? null, fn (Builder $events, $year) => $events->where('event_year', $year))
            ->when($filters['province'] ?? null, fn (Builder $events, $province) => $events->where('province', $province))
            ->when($filters['department'] ?? null, fn (Builder $events, $department) => $events->where('department', $department))
            ->when($filters['theme'] ?? null, fn (Builder $events, $theme) => $events->where('theme', $theme))
            ->when($filters['event_type'] ?? null, fn (Builder $events, $type) => $events->where('event_type', $type))
            ->when($filters['actor'] ?? null, fn (Builder $events, $actor) => $events->where(function (Builder $events) use ($actor) {
                $events->where('actor_1', $actor)->orWhere('actor_2', $actor);
            }));

        $total = (clone $query)->count();
        $gpsCount = (clone $query)
            ->whereBetween('latitude', [7.4, 23.5])
            ->whereBetween('longitude', [13.4, 24])
            ->count();
        $linkedEvents = (clone $query)
            ->whereNotNull('conflict_number')
            ->where('conflict_number', '<>', '')
            ->count();

        $metrics = [
            'observations' => $total,
            'provinces_covered' => (clone $query)
                ->whereNotNull('province')
                ->where('province', '<>', '')
                ->distinct()
                ->count('province'),
            'gps_valid' => $gpsCount,
            'gps_rate' => $total > 0 ? round(($gpsCount / $total) * 100, 1) : 0,
            'conflict_references' => (clone $query)
                ->whereNotNull('conflict_number')
                ->where('conflict_number', '<>', '')
                ->distinct()
                ->count('conflict_number'),
            'linked_observations' => $linkedEvents,
        ];

        $provinceDistribution = (clone $query)
            ->select('province')
            ->selectRaw('COUNT(*) AS count')
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->groupBy('province')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => ['province' => $row->province, 'count' => (int) $row->count])
            ->all();

        $annualTrend = (clone $query)
            ->select('event_year')
            ->selectRaw('COUNT(*) AS count')
            ->whereNotNull('event_year')
            ->groupBy('event_year')
            ->orderBy('event_year')
            ->get();

        $allThemes = (clone $query)
            ->selectRaw("COALESCE(NULLIF(TRIM(theme), ''), 'Autres thématiques') AS label, COUNT(*) AS count")
            ->groupBy('label')
            ->orderByDesc('count')
            ->get();
        $namedThemes = $allThemes->reject(fn ($row) => $row->label === 'Autres thématiques')->values();
        $themeDistribution = $namedThemes->take(5)->map(fn ($row, $index) => [
                'label' => $row->label,
                'count' => (int) $row->count,
                'color' => ['#a96500', '#f0a900', '#ffdb66', '#f2e3c9', '#b9a58d', '#88847e'][$index],
            ])->all();
        $otherThemeCount = (int) $allThemes->firstWhere('label', 'Autres thématiques')?->count
            + (int) $namedThemes->skip(5)->sum('count');
        if ($otherThemeCount > 0) {
            $themeDistribution[] = [
                'label' => 'Autres thématiques',
                'count' => $otherThemeCount,
                'color' => '#88847e',
            ];
        }

        $actorPairs = (clone $query)
            ->get(['actor_1', 'actor_2'])
            ->map(function (ConflictEvent $event) {
                $actors = collect([$event->actor_1, $event->actor_2])
                    ->map(fn ($actor) => trim((string) $actor))
                    ->filter()
                    ->sort(SORT_NATURAL | SORT_FLAG_CASE)
                    ->values();

                return $actors->isEmpty() ? null : $actors->join(' ↔ ');
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->map(fn ($count, $label) => ['label' => $label, 'count' => $count])
            ->values()
            ->all();

        $peaceResponses = (clone $query)
            ->get(['event_type', 'sub_event', 'other_event', 'interaction_type'])
            ->filter(fn (ConflictEvent $event) => Str::contains(
                Str::lower((string) $event->event_type),
                'développement stratégique'
            ))
            ->map(function (ConflictEvent $event) {
                return trim((string) ($event->sub_event ?: $event->other_event ?: $event->interaction_type))
                    ?: 'Autres / non renseignés';
            })
            ->countBy()
            ->sortDesc()
            ->take(6)
            ->map(fn ($count, $label) => [
                'label' => $label,
                'count' => $count,
                'icon' => $this->peaceResponseIcon($label),
            ])
            ->values()
            ->all();

        $recentObservations = (clone $query)
            ->orderByDesc('event_year')
            ->orderByDesc('source_row')
            ->limit(5)
            ->get(['event_date_raw', 'event_type', 'theme', 'province'])
            ->map(fn (ConflictEvent $event) => [
                'date' => $event->event_date_raw ?: '—',
                'event' => $event->event_type ?: 'Non renseigné',
                'theme' => $event->theme ?: 'Non renseignée',
                'province' => $event->province ?: 'Non renseignée',
            ])
            ->all();

        return view('content.dashboard.dashboards-synex-two', [
            'actors' => $actors,
            'annualTrend' => [
                'labels' => $annualTrend->pluck('event_year')->map(fn ($year) => (string) $year)->all(),
                'values' => $annualTrend->pluck('count')->map(fn ($count) => (int) $count)->all(),
            ],
            'actorPairs' => $actorPairs,
            'departments' => $departments,
            'eventTypes' => $eventTypes,
            'filters' => $filters,
            'metrics' => $metrics,
            'peaceResponses' => $peaceResponses,
            'provinces' => $provinces,
            'provinceDistribution' => $provinceDistribution,
            'recentObservations' => $recentObservations,
            'themes' => $themes,
            'themeDistribution' => $themeDistribution,
            'years' => $years,
        ]);
    }

    private function distinctValues(Builder $query, string $column): array
    {
        return (clone $query)
            ->whereNotNull($column)
            ->where($column, '<>', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    private function peaceResponseIcon(string $label): string
    {
        $label = Str::lower($label);

        return match (true) {
            Str::contains($label, ['médiat', 'dialogue', 'concertation']) => 'bx-group',
            Str::contains($label, ['arbitr', 'justice']) => 'bx-scale',
            Str::contains($label, ['accord', 'paix', 'réconciliation']) => 'bx-dove',
            Str::contains($label, ['enquête', 'investigation']) => 'bx-search-alt',
            Str::contains($label, ['prévention', 'campagne', 'sensibilisation']) => 'bx-bullhorn',
            Str::contains($label, ['négociation', 'pourparlers']) => 'bx-chat',
            Str::contains($label, ['autres', 'non renseign']) => 'bx-dots-horizontal-rounded',
            default => 'bx-handshake',
        };
    }
}
