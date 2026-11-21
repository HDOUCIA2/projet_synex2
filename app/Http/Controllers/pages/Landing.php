<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\VeilleSection;
use App\Services\DeathRateCalculator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Landing extends Controller
{
    public function index(Request $request, DeathRateCalculator $rateCalculator)
    {
        $populationYears = $rateCalculator->availableYears();
        $filters = $request->validate([
            'population_year' => ['nullable', 'integer', Rule::in($populationYears->all())],
        ]);
        $rateYear = isset($filters['population_year'])
            ? (int) $filters['population_year']
            : $populationYears->first();
        $deathRates = $rateCalculator->forYear($rateYear);
        $homeSections = VeilleSection::query()
            ->with(['cards' => fn ($query) => $query->where('is_active', true)])
            ->where('page', 'home')
            ->orderBy('display_order')
            ->get()
            ->keyBy('key');

        return view('content.pages.landing', compact(
            'deathRates',
            'homeSections',
            'populationYears',
            'rateYear'
        ));
    }
}
