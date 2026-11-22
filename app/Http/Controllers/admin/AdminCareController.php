<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CareCenter;
use App\Models\CareService;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class AdminCareController extends Controller
{
    private const SERVICE_ICONS = [
        'bx-plus-medical' => 'Soins de santé',
        'bx-briefcase' => 'Assistance juridique',
        'bx-heart' => 'Soutien psychosocial',
        'bx-home-heart' => 'Hébergement',
        'bx-leaf' => 'Réinsertion économique',
        'bx-group' => 'Accompagnement',
        'bx-shield-quarter' => 'Protection',
        'bx-building-house' => 'Structure',
    ];

    private const PAGE_ICONS = [
        'bx-map-pin' => 'Localisation',
        'bx-lock-alt' => 'Confidentialité',
        'bx-group' => 'Accompagnement',
        'bx-headphone' => 'Assistance',
        'bx-handshake' => 'Prise en charge',
        'bx-layer' => 'Services',
        'bx-search-alt-2' => 'Recherche',
        'bx-bell' => 'Alerte',
        'bx-git-branch' => 'Parcours',
        'bx-heart' => 'Prise en charge',
        'bx-shield-quarter' => 'Prévention',
        'bx-news' => 'Actualités',
        'bx-buildings' => 'Structures',
        'bxs-business' => 'Guichet',
    ];

    public function index()
    {
        $centers = CareCenter::query()
            ->with('services')
            ->orderBy('name')
            ->paginate(15);
        $services = CareService::query()
            ->withCount('centers')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $serviceIcons = self::SERVICE_ICONS;
        $siteSettings = SiteSetting::query()->find(1)
            ?? new SiteSetting(['name' => config('variables.templateName')]);
        $pageContent = array_replace_recursive(
            config('synex-care.page_content', []),
            $siteSettings->care_page_content ?? []
        );
        $pageIcons = self::PAGE_ICONS;

        return view('content.admin.care.index', compact('centers', 'services', 'serviceIcons', 'siteSettings', 'pageContent', 'pageIcons'));
    }

    public function updatePageContent(Request $request)
    {
        $attributes = $request->validate([
            'content' => ['required', 'array'],
            'content.hero_title' => ['required', 'string', 'max:120'],
            'content.hero_description' => ['required', 'string', 'max:500'],
            'content.cism_title' => ['required', 'string', 'max:120'],
            'content.cism_description' => ['required', 'string', 'max:500'],
            'content.cism_benefits' => ['required', 'array', 'size:3'],
            'content.cism_benefits.*.icon' => ['required', 'string', Rule::in(array_keys(self::PAGE_ICONS))],
            'content.cism_benefits.*.text' => ['required', 'string', 'max:120'],
            'content.cism_link' => ['required', 'string', 'max:80'],
            'content.services_title' => ['required', 'string', 'max:120'],
            'content.services_disclaimer' => ['required', 'string', 'max:300'],
            'content.search_title' => ['required', 'string', 'max:120'],
            'content.province_label' => ['required', 'string', 'max:80'],
            'content.department_label' => ['required', 'string', 'max:80'],
            'content.query_summary' => ['required', 'string', 'max:100'],
            'content.query_placeholder' => ['required', 'string', 'max:120'],
            'content.search_button' => ['required', 'string', 'max:80'],
            'content.map_title' => ['required', 'string', 'max:120'],
            'content.map_attribution' => ['required', 'string', 'max:255'],
            'content.alert_title' => ['required', 'string', 'max:120'],
            'content.alert_description' => ['required', 'string', 'max:300'],
            'content.assistance_phone' => ['required', 'string', 'max:50'],
            'content.phone_label' => ['required', 'string', 'max:80'],
            'content.phone_action' => ['required', 'string', 'max:100'],
            'content.safety_note' => ['required', 'string', 'max:200'],
            'content.process_title' => ['required', 'string', 'max:120'],
            'content.process_steps' => ['required', 'array', 'size:3'],
            'content.process_steps.*.icon' => ['required', 'string', Rule::in(array_keys(self::PAGE_ICONS))],
            'content.process_steps.*.title' => ['required', 'string', 'max:100'],
            'content.process_steps.*.description' => ['required', 'string', 'max:180'],
            'content.prevention_title' => ['required', 'string', 'max:120'],
            'content.prevention_description' => ['required', 'string', 'max:300'],
            'content.prevention_items' => ['required', 'array', 'size:4'],
            'content.prevention_items.*' => ['required', 'string', 'max:160'],
            'content.news_title' => ['required', 'string', 'max:120'],
            'content.news_description' => ['required', 'string', 'max:300'],
            'content.news_link' => ['required', 'string', 'max:80'],
            'content.directory_title' => ['required', 'string', 'max:120'],
            'content.directory_description' => ['required', 'string', 'max:300'],
            'content.directory_empty_title' => ['required', 'string', 'max:120'],
            'content.directory_empty_description' => ['required', 'string', 'max:300'],
            'content.directory_results_label' => ['required', 'string', 'max:50'],
            'content.center_detail_link' => ['required', 'string', 'max:80'],
            'content.center_call_link' => ['required', 'string', 'max:80'],
            'content.method_note_title' => ['required', 'string', 'max:80'],
            'content.method_note_description' => ['required', 'string', 'max:500'],
            'content.section_icons' => ['required', 'array'],
            'content.section_icons.*' => ['required', 'string', Rule::in(array_keys(self::PAGE_ICONS))],
        ]);

        $settings = SiteSetting::query()->find(1) ?? new SiteSetting(['name' => config('variables.templateName')]);
        $settings->care_page_content = $attributes['content'];
        $settings->save();

        return redirect()->route('admin.care.index')
            ->with('status', 'Le contenu de la page Prise en charge a été enregistré.');
    }

    public function updateCismImage(Request $request)
    {
        $request->validate([
            'cism_image' => ['required_without:remove_cism_image', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_cism_image' => ['nullable', 'boolean'],
        ]);

        $image = $request->file('cism_image');
        $removeImage = $request->boolean('remove_cism_image');
        $settings = SiteSetting::query()->find(1) ?? new SiteSetting(['name' => config('variables.templateName')]);
        $previousImagePath = $settings->care_page_image_path;
        $newImagePath = $image?->store('care', 'public');

        if ($image && (!is_string($newImagePath) || $newImagePath === '')) {
            throw new RuntimeException('Impossible d’enregistrer l’image du CISM.');
        }

        if (is_string($newImagePath)) {
            $settings->care_page_image_path = $newImagePath;
        } elseif ($removeImage) {
            $settings->care_page_image_path = null;
        }

        try {
            $settings->save();
        } catch (\Illuminate\Database\QueryException $exception) {
            if (is_string($newImagePath)) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($previousImagePath && ($removeImage || is_string($newImagePath))) {
            $disk = Storage::disk('public');
            if ($disk->exists($previousImagePath) && !$disk->delete($previousImagePath)) {
                throw new RuntimeException('Impossible de supprimer l’ancienne image du CISM.');
            }
        }

        return redirect()->route('admin.care.index')->with('status', 'L’image du guichet unique CISM a été mise à jour.');
    }

    public function create()
    {
        return view('content.admin.care.form', [
            'center' => new CareCenter(),
            'services' => CareService::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validatedCenter($request);
        $serviceIds = $attributes['service_ids'] ?? [];
        unset($attributes['service_ids']);

        DB::transaction(function () use ($attributes, $serviceIds) {
            $attributes['slug'] = $this->uniqueSlug($attributes['name']);
            $center = CareCenter::query()->create($attributes);
            $center->services()->sync($serviceIds);
        });

        return redirect()->route('admin.care.index')->with('status', 'La structure a été créée.');
    }

    public function edit(CareCenter $careCenter)
    {
        $careCenter->load('services');

        return view('content.admin.care.form', [
            'center' => $careCenter,
            'services' => CareService::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, CareCenter $careCenter)
    {
        $attributes = $this->validatedCenter($request);
        $serviceIds = $attributes['service_ids'] ?? [];
        unset($attributes['service_ids']);

        DB::transaction(function () use ($attributes, $serviceIds, $careCenter) {
            if ($attributes['name'] !== $careCenter->name) {
                $attributes['slug'] = $this->uniqueSlug($attributes['name'], $careCenter->id);
            }

            $careCenter->update($attributes);
            $careCenter->services()->sync($serviceIds);
        });

        return redirect()->route('admin.care.index')->with('status', 'La structure a été mise à jour.');
    }

    public function togglePublication(CareCenter $careCenter)
    {
        $careCenter->update(['is_published' => !$careCenter->is_published]);

        return redirect()->route('admin.care.index')->with(
            'status',
            $careCenter->is_published ? 'La structure est publiée.' : 'La structure a été dépubliée.'
        );
    }

    public function destroy(CareCenter $careCenter)
    {
        $careCenter->delete();

        return redirect()->route('admin.care.index')->with('status', 'La structure a été supprimée.');
    }

    public function storeService(Request $request)
    {
        $attributes = $this->validatedService($request);
        $attributes['slug'] = $this->uniqueServiceSlug($attributes['name']);
        $attributes['is_active'] = $request->boolean('is_active');
        CareService::query()->create($attributes);

        return redirect(route('admin.care.index').'#care-services')
            ->with('status', 'Le service a été ajouté.');
    }

    public function updateService(Request $request, CareService $service)
    {
        $attributes = $this->validatedService($request);
        if ($attributes['name'] !== $service->name) {
            $attributes['slug'] = $this->uniqueServiceSlug($attributes['name'], $service->id);
        }
        $attributes['is_active'] = $request->boolean('is_active');
        $service->update($attributes);

        return redirect(route('admin.care.index').'#care-services')
            ->with('status', 'Le service a été mis à jour.');
    }

    public function destroyService(CareService $service)
    {
        if ($service->centers()->exists()) {
            return redirect(route('admin.care.index').'#care-services')
                ->withErrors(['service' => 'Ce service est associé à une structure. Désactivez-le plutôt que de le supprimer.']);
        }

        $service->delete();

        return redirect(route('admin.care.index').'#care-services')
            ->with('status', 'Le service a été supprimé.');
    }

    private function validatedCenter(Request $request): array
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'structure_type' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:3000'],
            'province' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'locality' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'required_if:show_on_public_map,1', 'numeric', 'between:7.4,23.5'],
            'longitude' => ['nullable', 'required_if:show_on_public_map,1', 'numeric', 'between:13.4,24'],
            'phone' => ['nullable', 'string', 'max:60'],
            'email' => ['nullable', 'email', 'max:180'],
            'opening_hours' => ['nullable', 'string', 'max:180'],
            'is_free' => ['nullable', 'boolean'],
            'confidentiality_note' => ['nullable', 'string', 'max:1000'],
            'is_published' => ['nullable', 'boolean'],
            'show_on_public_map' => ['nullable', 'boolean'],
            'show_public_detail' => ['nullable', 'boolean'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'distinct', Rule::exists('care_services', 'id')],
        ]);

        foreach (['is_free', 'is_published', 'show_on_public_map', 'show_public_detail'] as $field) {
            $attributes[$field] = $request->boolean($field);
        }

        return $attributes;
    }

    private function validatedService(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['required', 'string', Rule::in(array_keys(self::SERVICE_ICONS))],
            'sort_order' => ['required', 'integer', 'between:0,999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'structure';
        $slug = $base;
        $suffix = 2;

        while (CareCenter::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function uniqueServiceSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $suffix = 2;

        while (CareService::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
