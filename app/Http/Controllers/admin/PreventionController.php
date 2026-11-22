<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PreventionInitiative;
use App\Models\PreventionResource;
use App\Models\SiteSetting;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RuntimeException;

class PreventionController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::query()->find(1)
            ?? new SiteSetting(['name' => config('variables.templateName')]);
        $prevention = array_replace_recursive(
            config('synex_prevention'),
            $settings->prevention_page_content ?? []
        );
        $initiatives = PreventionInitiative::query()
            ->orderByDesc('initiative_date')
            ->orderBy('display_order')
            ->paginate(10, ['*'], 'initiatives');
        $resources = PreventionResource::query()->latest('id')->get();
        $types = PreventionInitiative::TYPES;

        return view('content.admin.prevention.index', compact(
            'prevention',
            'initiatives',
            'resources',
            'types'
        ));
    }

    public function updateContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => ['required', 'array'],
            'content.hero_title' => ['required', 'string', 'max:180'],
            'content.hero_description' => ['required', 'string', 'max:600'],
            'content.approach_title' => ['required', 'string', 'max:120'],
            'content.approach_points' => ['required', 'array', 'size:4'],
            'content.approach_points.*' => ['required', 'string', 'max:180'],
            'content.source.from' => ['required', 'date'],
            'content.source.to' => ['required', 'date', 'after_or_equal:content.source.from'],
            'content.source.note' => ['required', 'string', 'max:2000'],
            'content.metrics' => ['required', 'array'],
            'content.metrics.campaigns' => ['required', 'integer', 'between:0,1000000'],
            'content.metrics.mediations' => ['required', 'integer', 'between:0,1000000'],
            'content.metrics.arbitrations' => ['required', 'integer', 'between:0,1000000'],
            'content.metrics.peace_agreements' => ['required', 'integer', 'between:0,1000000'],
            'content.axes' => ['required', 'array', 'size:4'],
            'content.axes.*.title' => ['required', 'string', 'max:120'],
            'content.axes.*.description' => ['required', 'string', 'max:300'],
            'content.axes.*.tag' => ['required', 'string', 'max:80'],
            'content.province_distribution' => ['required', 'array', 'size:3'],
            'content.province_distribution.*.province' => ['required', 'string', 'max:100', 'distinct'],
            'content.province_distribution.*.count' => ['required', 'integer', 'between:0,1000000'],
        ]);
        $validator->after(function ($validator) use ($request) {
            $content = $request->input('content', []);
            $metricsTotal = collect($content['metrics'] ?? [])->sum(fn ($value) => (int) $value);
            $provinceTotal = collect($content['province_distribution'] ?? [])->sum(fn ($row) => (int) ($row['count'] ?? 0));

            if ($metricsTotal !== $provinceTotal) {
                $validator->errors()->add(
                    'content.province_distribution',
                    'La somme des enregistrements par province doit correspondre au total des indicateurs.'
                );
            }
        });
        $content = $validator->validate()['content'];

        $settings = SiteSetting::query()->find(1)
            ?? new SiteSetting(['name' => config('variables.templateName')]);
        $mergedContent = array_replace_recursive(
            config('synex_prevention'),
            $settings->prevention_page_content ?? []
        );
        $mergedContent['hero_title'] = $content['hero_title'];
        $mergedContent['hero_description'] = $content['hero_description'];
        $mergedContent['approach_title'] = $content['approach_title'];
        $mergedContent['approach_points'] = array_values($content['approach_points']);
        $mergedContent['source']['from'] = $content['source']['from'];
        $mergedContent['source']['to'] = $content['source']['to'];
        $mergedContent['source']['note'] = $content['source']['note'];
        $mergedContent['metrics'] = $content['metrics'];
        $mergedContent['province_distribution'] = array_values($content['province_distribution']);
        foreach ($content['axes'] as $index => $axis) {
            $mergedContent['axes'][$index] = array_merge($mergedContent['axes'][$index], $axis);
        }

        $settings->prevention_page_content = $mergedContent;
        $settings->save();

        return redirect()->route('admin.prevention.index')
            ->with('status', 'Le contenu de la page Prévention a été enregistré.');
    }

    public function createInitiative()
    {
        return view('content.admin.prevention.initiative-form', [
            'initiative' => new PreventionInitiative(['records' => 1, 'is_published' => true]),
            'types' => PreventionInitiative::TYPES,
        ]);
    }

    public function storeInitiative(Request $request)
    {
        $attributes = $this->validatedInitiative($request);
        $attributes['is_published'] = $request->boolean('is_published');
        $attributes['display_order'] = ((int) PreventionInitiative::query()->max('display_order')) + 1;
        PreventionInitiative::query()->create($attributes);

        return redirect()->route('admin.prevention.index')
            ->with('status', 'L’initiative a été ajoutée.');
    }

    public function editInitiative(PreventionInitiative $initiative)
    {
        return view('content.admin.prevention.initiative-form', [
            'initiative' => $initiative,
            'types' => PreventionInitiative::TYPES,
        ]);
    }

    public function updateInitiative(Request $request, PreventionInitiative $initiative)
    {
        $attributes = $this->validatedInitiative($request);
        $attributes['is_published'] = $request->boolean('is_published');
        $initiative->update($attributes);

        return redirect()->route('admin.prevention.index')
            ->with('status', 'L’initiative a été mise à jour.');
    }

    public function destroyInitiative(PreventionInitiative $initiative)
    {
        $initiative->delete();

        return redirect()->route('admin.prevention.index')
            ->with('status', 'L’initiative a été supprimée.');
    }

    public function storeResource(Request $request)
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $path = $this->storeResourceFile($attributes['file']);

        try {
            PreventionResource::query()->create([
                'title' => $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'file_path' => $path,
                'is_published' => $request->boolean('is_published'),
            ]);
        } catch (QueryException $exception) {
            if (!$this->deleteResourceFile($path)) {
                throw new RuntimeException('La ressource n’a pas été créée et son fichier temporaire n’a pas pu être supprimé.', previous: $exception);
            }

            throw $exception;
        }

        return redirect()->route('admin.prevention.index')
            ->with('status', 'La ressource a été ajoutée.');
    }

    public function updateResource(Request $request, PreventionResource $resource)
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        $file = $attributes['file'] ?? null;
        unset($attributes['file']);
        $previousPath = $resource->file_path;
        if ($file) {
            $attributes['file_path'] = $this->storeResourceFile($file);
        }
        $attributes['is_published'] = $request->boolean('is_published');
        try {
            $resource->update($attributes);
        } catch (QueryException $exception) {
            if ($file && !$this->deleteResourceFile($attributes['file_path'])) {
                throw new RuntimeException('La ressource n’a pas été mise à jour et son nouveau fichier n’a pas pu être supprimé.', previous: $exception);
            }

            throw $exception;
        }

        if ($file) {
            if (!$this->deleteResourceFile($previousPath)) {
                throw new RuntimeException('La ressource a été mise à jour, mais son ancien fichier n’a pas pu être supprimé.');
            }
        }

        return redirect()->route('admin.prevention.index')
            ->with('status', 'La ressource a été mise à jour.');
    }

    public function destroyResource(PreventionResource $resource)
    {
        $path = $resource->file_path;
        $resource->delete();
        if (!$this->deleteResourceFile($path)) {
            throw new RuntimeException('La ressource a été retirée, mais son fichier n’a pas pu être supprimé.');
        }

        return redirect()->route('admin.prevention.index')
            ->with('status', 'La ressource a été supprimée.');
    }

    private function storeResourceFile(UploadedFile $file): string
    {
        $path = $file->store('prevention/resources', 'public');
        if (!is_string($path) || $path === '') {
            throw new RuntimeException('Impossible d’enregistrer le document de prévention.');
        }

        return $path;
    }

    private function deleteResourceFile(string $path): bool
    {
        $disk = Storage::disk('public');

        return !$disk->exists($path) || $disk->delete($path);
    }

    private function validatedInitiative(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', 'string', Rule::in(array_keys(PreventionInitiative::TYPES))],
            'initiative_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:180'],
            'province' => ['required', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:180'],
            'note' => ['nullable', 'string', 'max:2000'],
            'records' => ['required', 'integer', 'between:1,10000'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }
}
