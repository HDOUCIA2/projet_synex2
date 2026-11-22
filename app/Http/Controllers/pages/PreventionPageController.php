<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\PreventionInitiative;
use App\Models\PreventionResource;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class PreventionPageController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::query()->find(1);
        $prevention = array_replace_recursive(
            config('synex_prevention'),
            $settings?->prevention_page_content ?? []
        );
        $metrics = $prevention['metrics'];
        $actionsTotal = array_sum($metrics);
        $actionsByType = collect($prevention['actions_by_type'])
            ->map(function (array $action) use ($metrics) {
                $action['count'] = (int) ($metrics[$action['metric']] ?? 0);

                return $action;
            });
        $provinceDistribution = collect($prevention['province_distribution']);
        $initiatives = PreventionInitiative::query()
            ->where('is_published', true)
            ->orderByDesc('initiative_date')
            ->orderBy('display_order')
            ->limit(3)
            ->get();
        $resources = PreventionResource::query()
            ->where('is_published', true)
            ->latest('id')
            ->get()
            ->map(fn (PreventionResource $resource) => [
                'title' => $resource->title,
                'description' => $resource->description,
                'url' => Storage::disk('public')->url($resource->file_path),
            ]);

        return view('content.pages.prevention', compact(
            'prevention',
            'metrics',
            'actionsTotal',
            'actionsByType',
            'provinceDistribution',
            'initiatives',
            'resources'
        ));
    }
}
