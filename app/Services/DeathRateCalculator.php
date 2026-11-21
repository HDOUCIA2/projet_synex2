<?php

namespace App\Services;

use App\Models\ConflictEvent;
use App\Models\ProvincePopulation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DeathRateCalculator
{
    public function availableYears(): Collection
    {
        return ProvincePopulation::query()
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }

    public function forYear(?int $year, ?Builder $events = null): Collection
    {
        if ($year === null) {
            return collect();
        }

        $events ??= ConflictEvent::query()->approved();
        $deathsByProvince = (clone $events)
            ->where('event_year', $year)
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->select('province')
            ->selectRaw('COALESCE(SUM(deaths), 0) AS deaths')
            ->groupBy('province')
            ->get()
            ->mapWithKeys(fn ($row) => [mb_strtolower(trim($row->province)) => (int) $row->deaths]);

        return ProvincePopulation::query()
            ->with('province')
            ->where('year', $year)
            ->orderBy('province_id')
            ->get()
            ->map(function (ProvincePopulation $population) use ($deathsByProvince) {
                $deaths = $deathsByProvince->get(mb_strtolower(trim($population->province->name)), 0);

                return [
                    'province' => $population->province->name,
                    'year' => $population->year,
                    'population' => $population->population,
                    'deaths' => $deaths,
                    'rate' => round($deaths / $population->population * 100000, 2),
                ];
            });
    }
}
