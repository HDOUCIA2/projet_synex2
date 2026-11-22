<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\CareCenter;
use App\Models\CareService;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CarePageController extends Controller
{
    public function index(Request $request)
    {
        $centers = collect();
        $mapCenters = collect();
        $provinces = collect();
        $departments = collect();
        $serviceTypes = collect();

        if (Schema::hasTable('care_centers')) {
            $published = CareCenter::query()->where('is_published', true);
            $provinces = (clone $published)
                ->whereNotNull('province')
                ->where('province', '<>', '')
                ->distinct()
                ->orderBy('province')
                ->pluck('province');

            $filters = $request->validate([
                'province' => ['nullable', 'string', 'max:100', Rule::in($provinces->all())],
                'q' => ['nullable', 'string', 'max:120'],
            ]);

            $departmentQuery = clone $published;
            if (!empty($filters['province'])) {
                $departmentQuery->where('province', $filters['province']);
            }
            $departments = $departmentQuery
                ->whereNotNull('department')
                ->where('department', '<>', '')
                ->distinct()
                ->orderBy('department')
                ->pluck('department');

            $filters['department'] = $request->validate([
                'department' => ['nullable', 'string', 'max:100', Rule::in($departments->all())],
            ])['department'] ?? null;

            $filtered = clone $published;
            $hasServiceRelations = Schema::hasTable('care_services') && Schema::hasTable('care_center_service');
            if ($hasServiceRelations) {
                $filtered->with(['services' => fn ($query) => $query->where('is_active', true)]);
            }
            if (!empty($filters['province'])) {
                $filtered->where('province', $filters['province']);
            }
            if (!empty($filters['department'])) {
                $filtered->where('department', $filters['department']);
            }
            if (!empty($filters['q'])) {
                $term = trim($filters['q']);
                $filtered->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%'.$term.'%')
                        ->orWhere('locality', 'like', '%'.$term.'%');
                });
            }

            $centers = (clone $filtered)->orderBy('name')->paginate(8)->withQueryString();
            if (!$hasServiceRelations) {
                foreach ($centers as $center) {
                    $center->setRelation('services', collect());
                }
            }

            $mapCenters = (clone $filtered)
                ->where('show_on_public_map', true)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(['name', 'slug', 'structure_type', 'province', 'department', 'locality', 'latitude', 'longitude'])
                ->filter(fn (CareCenter $center) => $center->latitude >= 7.4
                    && $center->latitude <= 23.5
                    && $center->longitude >= 13.4
                    && $center->longitude <= 24)
                ->values();
        } else {
            $filters = $request->validate([
                'province' => ['nullable', 'string', 'max:100'],
                'department' => ['nullable', 'string', 'max:100'],
                'q' => ['nullable', 'string', 'max:120'],
            ]);
        }

        if (Schema::hasTable('care_services')) {
            $serviceTypes = CareService::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        if ($serviceTypes->isEmpty()) {
            $serviceTypes = collect([
                ['name' => 'Soins de santé', 'icon' => 'bx-plus-medical', 'description' => 'Orientation vers des services de santé adaptés.'],
                ['name' => 'Assistance juridique', 'icon' => 'bx-scale', 'description' => 'Information et orientation juridique.'],
                ['name' => 'Soutien psychosocial', 'icon' => 'bx-heart', 'description' => 'Orientation vers un accompagnement psychosocial.'],
                ['name' => 'Hébergement', 'icon' => 'bx-home-heart', 'description' => 'Information sur les possibilités d’hébergement.'],
                ['name' => 'Réinsertion économique', 'icon' => 'bx-leaf', 'description' => 'Orientation vers des dispositifs d’autonomisation.'],
            ]);
        }

        $siteSettings = SiteSetting::query()->find(1);
        $pageContent = array_replace_recursive(
            config('synex-care.page_content', []),
            $siteSettings?->care_page_content ?? []
        );

        return view('content.pages.prise-en-charge.index', [
            'centers' => $centers,
            'cismImageUrl' => $siteSettings?->care_page_image_path
                ? Storage::disk('public')->url($siteSettings->care_page_image_path)
                : null,
            'departments' => $departments,
            'filters' => $filters,
            'mapCenters' => $mapCenters,
            'pageContent' => $pageContent,
            'provinces' => $provinces,
            'serviceTypes' => $serviceTypes,
            'assistancePhone' => $pageContent['assistance_phone'],
        ]);
    }

    public function show(string $slug)
    {
        abort_unless(Schema::hasTable('care_centers'), 404);

        $careCenter = CareCenter::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->where('show_public_detail', true)
            ->with(['services' => fn ($query) => $query->where('is_active', true)])
            ->firstOrFail();

        return view('content.pages.prise-en-charge.show', compact('careCenter'));
    }
}
