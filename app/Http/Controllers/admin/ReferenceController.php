<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\ConflictEvent;
use App\Models\EventType;
use App\Models\OtherEvent;
use App\Models\Province;
use App\Models\ProvincePopulation;
use App\Models\SubEvent;
use App\Models\Subtheme;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReferenceController extends Controller
{
    private const CATALOGS = [
        'provinces' => ['title' => 'Provinces', 'model' => Province::class, 'field' => 'name', 'label' => 'Nom'],
        'event-types' => ['title' => 'Types d’événement', 'model' => EventType::class, 'field' => 'name', 'label' => 'Nom'],
        'sub-events' => [
            'title' => 'Sous-événements',
            'model' => SubEvent::class,
            'field' => 'name',
            'label' => 'Nom',
            'parent' => ['field' => 'event_type_id', 'label' => 'Type d’événement', 'model' => EventType::class, 'relation' => 'eventType'],
        ],
        'other-events' => ['title' => 'Autres événements', 'model' => OtherEvent::class, 'field' => 'description', 'label' => 'Description'],
        'themes' => ['title' => 'Thématiques', 'model' => Theme::class, 'field' => 'name', 'label' => 'Nom'],
        'subthemes' => [
            'title' => 'Sous-thématiques',
            'model' => Subtheme::class,
            'field' => 'name',
            'label' => 'Nom',
            'parent' => ['field' => 'theme_id', 'label' => 'Thématique', 'model' => Theme::class, 'relation' => 'theme'],
        ],
        'actors' => ['title' => 'Acteurs', 'model' => Actor::class, 'field' => 'name', 'label' => 'Nom'],
    ];

    public function index(string $catalog)
    {
        $config = $this->catalogConfig($catalog);
        $model = $config['model'];
        $query = $model::query();

        if (isset($config['parent'])) {
            $query->with($config['parent']['relation']);
        }

        $items = $query->orderBy($config['field'])->paginate(20)->withQueryString();
        $parentOptions = isset($config['parent'])
            ? $config['parent']['model']::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('content.admin.references.index', compact('catalog', 'config', 'items', 'parentOptions'));
    }

    public function store(Request $request, string $catalog)
    {
        $config = $this->catalogConfig($catalog);
        $attributes = $this->validatedAttributes($request, $catalog, $config);
        $config['model']::create($attributes);

        return redirect()->route('admin.references.index', $catalog)->with('status', 'La valeur a été ajoutée.');
    }

    public function update(Request $request, string $catalog, int $item)
    {
        $config = $this->catalogConfig($catalog);
        $model = $config['model']::query()->findOrFail($item);
        $attributes = $this->validatedAttributes($request, $catalog, $config, $model);
        $oldValue = $model->{$config['field']};

        DB::transaction(function () use ($catalog, $config, $model, $attributes, $oldValue) {
            $model->fill($attributes)->save();
            $this->syncEventLabels($catalog, $model, $oldValue, $attributes[$config['field']]);
        });

        return redirect()->route('admin.references.index', $catalog)->with('status', 'La valeur a été mise à jour.');
    }

    public function destroy(string $catalog, int $item)
    {
        $config = $this->catalogConfig($catalog);
        $model = $config['model']::query()->findOrFail($item);

        if ($this->isInUse($catalog, $model)) {
            return back()->withErrors(['catalog' => 'Cette valeur est utilisée par des événements ou d’autres références.']);
        }

        $model->delete();

        return redirect()->route('admin.references.index', $catalog)->with('status', 'La valeur a été supprimée.');
    }

    public function toggleStatus(string $catalog, int $item)
    {
        $config = $this->catalogConfig($catalog);
        $model = $config['model']::query()->findOrFail($item);
        $model->is_active = !$model->is_active;
        $model->save();

        return redirect()->route('admin.references.index', $catalog)
            ->with('status', $model->is_active ? 'La valeur a été réactivée.' : 'La valeur a été bloquée.');
    }

    private function catalogConfig(string $catalog): array
    {
        abort_unless(isset(self::CATALOGS[$catalog]), 404);

        return self::CATALOGS[$catalog];
    }

    private function validatedAttributes(Request $request, string $catalog, array $config, ?Model $model = null): array
    {
        $field = $config['field'];
        $request->merge([$field => trim((string) $request->input($field))]);

        if ($catalog === 'other-events') {
            $attributes = $request->validate([
                'description' => ['required', 'string', 'max:2000'],
            ]);
            $hash = hash('sha256', $attributes['description']);
            $duplicate = OtherEvent::query()
                ->where('value_hash', $hash)
                ->when($model, fn ($query) => $query->where('id', '<>', $model->getKey()))
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'description' => 'Cette description existe déjà.',
                ]);
            }

            return ['description' => $attributes['description'], 'value_hash' => $hash];
        }

        $rules = [
            $field => ['required', 'string', 'max:255'],
        ];

        if ($catalog === 'sub-events') {
            $rules['event_type_id'] = ['required', 'integer', 'exists:event_types,id'];
            $rules[$field][] = Rule::unique('sub_events', 'name')
                ->where('event_type_id', $request->input('event_type_id'))
                ->ignore($model?->getKey());
        } elseif ($catalog === 'subthemes') {
            $rules['theme_id'] = ['required', 'integer', 'exists:themes,id'];
            $rules[$field][] = Rule::unique('subthemes', 'name')
                ->where('theme_id', $request->input('theme_id'))
                ->ignore($model?->getKey());
        } else {
            $modelClass = $config['model'];
            $table = $model?->getTable() ?? (new $modelClass)->getTable();
            $rules[$field][] = Rule::unique($table, $field)->ignore($model?->getKey());
        }

        $attributes = $request->validate($rules);

        if (isset($config['parent'])) {
            $attributes[$config['parent']['field']] = (int) $request->input($config['parent']['field']);
        }

        return $attributes;
    }

    private function syncEventLabels(string $catalog, Model $model, string $oldValue, string $newValue): void
    {
        if ($oldValue === $newValue) {
            return;
        }

        $relation = match ($catalog) {
            'provinces' => ['column' => 'province', 'key' => $oldValue],
            'event-types' => ['column' => 'event_type', 'key' => $model->getKey(), 'foreign_key' => 'event_type_id'],
            'sub-events' => ['column' => 'sub_event', 'key' => $model->getKey(), 'foreign_key' => 'sub_event_id'],
            'other-events' => ['column' => 'other_event', 'key' => $model->getKey(), 'foreign_key' => 'other_event_id'],
            'themes' => ['column' => 'theme', 'key' => $model->getKey(), 'foreign_key' => 'theme_id'],
            'subthemes' => ['column' => 'subtheme', 'key' => $model->getKey(), 'foreign_key' => 'subtheme_id'],
            default => null,
        };

        if ($relation !== null) {
            $query = ConflictEvent::query();

            if (isset($relation['foreign_key'])) {
                $query->where($relation['foreign_key'], $relation['key']);
            } else {
                $query->where($relation['column'], $relation['key']);
            }

            $query->update([$relation['column'] => $newValue]);
        }

        if ($catalog === 'actors') {
            $links = DB::table('conflict_event_actors')
                ->where('actor_id', $model->getKey())
                ->get(['conflict_event_id', 'role']);

            foreach ($links as $link) {
                $column = $link->role === 'actor_1' ? 'actor_1' : 'actor_2';
                ConflictEvent::query()->whereKey($link->conflict_event_id)->update([$column => $newValue]);
            }
        }
    }

    private function isInUse(string $catalog, Model $model): bool
    {
        return match ($catalog) {
            'provinces' => ConflictEvent::query()->where('province', $model->name)->exists()
                || ProvincePopulation::query()->where('province_id', $model->getKey())->exists(),
            'event-types' => ConflictEvent::query()->where('event_type_id', $model->getKey())->exists()
                || SubEvent::query()->where('event_type_id', $model->getKey())->exists(),
            'sub-events' => ConflictEvent::query()->where('sub_event_id', $model->getKey())->exists(),
            'other-events' => ConflictEvent::query()->where('other_event_id', $model->getKey())->exists(),
            'themes' => ConflictEvent::query()->where('theme_id', $model->getKey())->exists()
                || Subtheme::query()->where('theme_id', $model->getKey())->exists(),
            'subthemes' => ConflictEvent::query()->where('subtheme_id', $model->getKey())->exists(),
            'actors' => DB::table('conflict_event_actors')->where('actor_id', $model->getKey())->exists(),
            default => false,
        };
    }
}
