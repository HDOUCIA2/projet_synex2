<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Actor;
use App\Models\ConflictEvent;
use App\Models\EventType;
use App\Models\OtherEvent;
use App\Models\Province;
use App\Models\SubEvent;
use App\Models\Subtheme;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Closure;

class ConflictEventController extends Controller
{
    public function index(Request $request)
    {
        $query = ConflictEvent::query()
            ->with(['eventTypeRecord', 'subEventRecord', 'themeRecord', 'creator'])
            ->when(!Auth::user()->isAdmin(), fn (Builder $query) => $query->where(function (Builder $query) {
                $query->where('created_by_user_id', Auth::id())
                    ->orWhere('status', 'approved');
            }))
            ->when($request->input('status'), fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($request->string('search')->trim()->value(), function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('locality', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('event_notes', 'like', "%{$search}%")
                        ->orWhere('event_type', 'like', "%{$search}%");
                });
            });

        $events = $query->orderByDesc('event_year')->orderByDesc('id')->paginate(10)->withQueryString();
        $pendingCount = ConflictEvent::query()
            ->where('status', 'pending')
            ->when(!Auth::user()->isAdmin(), fn (Builder $query) => $query->where('created_by_user_id', Auth::id()))
            ->count();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('content.admin.events._results', compact('events'))->render(),
                'pendingCount' => $pendingCount,
                'currentPage' => $events->currentPage(),
                'lastPage' => $events->lastPage(),
            ]);
        }

        return view('content.admin.events.index', compact('events', 'pendingCount'));
    }

    public function create()
    {
        $event = new ConflictEvent();

        return view('content.admin.events.form', [
            'event' => $event,
            ...$this->formOptions($event),
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validatedAttributes($request);
        $event = new ConflictEvent();
        $this->saveEvent($event, $attributes, Auth::id());

        return redirect()->route('admin.events.edit', $event)->with(
            'status',
            Auth::user()->isAdmin()
                ? 'L’événement a été enregistré et publié.'
                : 'L’événement a été envoyé à l’administrateur pour validation.'
        );
    }

    public function edit(int $event)
    {
        $event = $this->findAccessibleEvent($event);
        $event->load('actors');
        $event->setAttribute('actor_1_id', $event->actors->first(fn (Actor $actor) => $actor->pivot->role === 'actor_1')?->id);
        $event->setAttribute('actor_2_id', $event->actors->first(fn (Actor $actor) => $actor->pivot->role === 'actor_2')?->id);

        return view('content.admin.events.form', [
            'event' => $event,
            ...$this->formOptions($event),
        ]);
    }

    public function update(Request $request, int $event)
    {
        $event = $this->findAccessibleEvent($event);
        $attributes = $this->validatedAttributes($request, $event);
        $this->saveEvent($event, $attributes);

        return redirect()->route('admin.events.edit', $event)->with(
            'status',
            Auth::user()->isAdmin()
                ? 'L’événement a été mis à jour.'
                : 'Les corrections ont été soumises à nouveau pour validation.'
        );
    }

    public function destroy(Request $request, int $event)
    {
        $event = $this->findAccessibleEvent($event);
        $event->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'L’événement a été supprimé.']);
        }

        return redirect()->route('admin.events.index')->with('status', 'L’événement a été supprimé.');
    }

    public function review(Request $request, int $event)
    {
        $attributes = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $event = ConflictEvent::query()->findOrFail($event);
        $event->update([
            ...$attributes,
            'review_notes' => $attributes['status'] === 'rejected' ? ($attributes['review_notes'] ?? null) : null,
        ]);

        $message = match ($attributes['status']) {
            'approved' => 'L’événement a été publié.',
            'pending' => 'L’événement a été dépublié et remis en attente.',
            default => 'L’événement a été refusé.',
        };

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.events.index')->with('status', $message);
    }

    private function findAccessibleEvent(int $id): ConflictEvent
    {
        return ConflictEvent::query()
            ->when(!Auth::user()->isAdmin(), fn (Builder $query) => $query
                ->where('created_by_user_id', Auth::id())
                ->whereIn('status', ['pending', 'rejected']))
            ->findOrFail($id);
    }

    private function formOptions(?ConflictEvent $event = null): array
    {
        $activeOrCurrent = static function (Builder|Relation $query, ?int $currentId): Builder|Relation {
            return $query->where(function (Builder $query) use ($currentId) {
                $query->where('is_active', true)
                    ->when($currentId, fn (Builder $query) => $query->orWhereKey($currentId));
            });
        };
        $currentProvinceId = $event?->province
            ? Province::query()->where('name', $event->province)->value('id')
            : null;
        $currentActorIds = $event?->exists
            ? DB::table('conflict_event_actors')->where('conflict_event_id', $event->id)->pluck('actor_id')->map(fn ($id) => (int) $id)->all()
            : [];

        return [
            'provinces' => $activeOrCurrent(Province::query(), $currentProvinceId)->orderBy('name')->get(),
            'eventTypes' => $activeOrCurrent(EventType::query(), $event?->event_type_id)
                ->with(['subEvents' => fn (Relation $query) => $activeOrCurrent($query, $event?->sub_event_id)])
                ->orderBy('name')->get(),
            'otherEvents' => $activeOrCurrent(OtherEvent::query(), $event?->other_event_id)->orderBy('description')->get(),
            'themes' => $activeOrCurrent(Theme::query(), $event?->theme_id)
                ->with(['subthemes' => fn (Relation $query) => $activeOrCurrent($query, $event?->subtheme_id)])
                ->orderBy('name')->get(),
            'actors' => Actor::query()->where(fn (Builder $query) => $query->where('is_active', true)
                ->when($currentActorIds !== [], fn (Builder $query) => $query->orWhereIn('id', $currentActorIds)))
                ->orderBy('name')->get(),
        ];
    }

    private function validatedAttributes(Request $request, ?ConflictEvent $event = null): array
    {
        $currentProvinceId = $event?->province
            ? Province::query()->where('name', $event->province)->value('id')
            : null;
        $attributes = $request->validate([
            'event_year' => ['required', 'integer', 'between:1900,2100'],
            'event_date_raw' => ['nullable', 'string', 'max:64'],
            'time_period' => ['nullable', 'integer', 'min:0'],
            'province_id' => ['required', 'integer', $this->activeReferenceRule(Province::class, $currentProvinceId)],
            'event_type_id' => ['required', 'integer', $this->activeReferenceRule(EventType::class, $event?->event_type_id)],
            'sub_event_id' => [
                'nullable',
                'integer',
                Rule::exists('sub_events', 'id')->where('event_type_id', $request->input('event_type_id')),
                $this->activeReferenceRule(SubEvent::class, $event?->sub_event_id),
            ],
            'other_event_id' => ['nullable', 'integer', $this->activeReferenceRule(OtherEvent::class, $event?->other_event_id)],
            'theme_id' => ['required', 'integer', $this->activeReferenceRule(Theme::class, $event?->theme_id)],
            'subtheme_id' => [
                'nullable',
                'integer',
                Rule::exists('subthemes', 'id')->where('theme_id', $request->input('theme_id')),
                $this->activeReferenceRule(Subtheme::class, $event?->subtheme_id),
            ],
            'actor_1_id' => ['nullable', 'integer', $this->activeReferenceRule(Actor::class, $this->actorIdForRole($event, 'actor_1'))],
            'actor_2_id' => ['nullable', 'integer', 'different:actor_1_id', $this->activeReferenceRule(Actor::class, $this->actorIdForRole($event, 'actor_2'))],
            'association_1' => ['nullable', 'string', 'max:255'],
            'interaction_1' => ['nullable', 'string', 'max:255'],
            'association_2' => ['nullable', 'string', 'max:255'],
            'interaction_2' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'geoprecision' => ['nullable', 'integer', 'between:0,255'],
            'source' => ['nullable', 'string', 'max:255'],
            'source_type' => ['nullable', 'string', 'max:80'],
            'source_url_1' => ['nullable', 'url', 'max:2000'],
            'event_notes' => ['nullable', 'string'],
            'pastoral' => ['nullable', 'boolean'],
            'deaths' => ['nullable', 'integer', 'min:0'],
            'injuries' => ['nullable', 'integer', 'min:0'],
            'kidnappings' => ['nullable', 'integer', 'min:0'],
            'arrests' => ['nullable', 'integer', 'min:0'],
            'property_damage' => ['nullable', 'integer', 'min:0'],
            'sexual_violence' => ['nullable', 'integer', 'min:0'],
            'displaced_people' => ['nullable', 'integer', 'min:0'],
        ]);

        if (Auth::user()->isAdmin() && $request->has('status')) {
            $attributes['status'] = $request->validate([
                'status' => ['required', Rule::in($event?->exists
                    ? ['pending', 'approved', 'rejected']
                    : ['pending', 'approved'])],
            ])['status'];
        }

        $attributes['event_type'] = EventType::findOrFail($attributes['event_type_id'])->name;
        $attributes['sub_event'] = isset($attributes['sub_event_id']) ? SubEvent::findOrFail($attributes['sub_event_id'])->name : null;
        $attributes['other_event'] = isset($attributes['other_event_id']) ? OtherEvent::findOrFail($attributes['other_event_id'])->description : null;
        $attributes['theme'] = Theme::findOrFail($attributes['theme_id'])->name;
        $attributes['subtheme'] = isset($attributes['subtheme_id']) ? Subtheme::findOrFail($attributes['subtheme_id'])->name : null;
        $attributes['province'] = Province::findOrFail($attributes['province_id'])->name;
        $attributes['actor_1'] = isset($attributes['actor_1_id']) ? Actor::findOrFail($attributes['actor_1_id'])->name : null;
        $attributes['actor_2'] = isset($attributes['actor_2_id']) ? Actor::findOrFail($attributes['actor_2_id'])->name : null;

        return $attributes;
    }

    private function saveEvent(ConflictEvent $event, array $attributes, ?int $creatorId = null): void
    {
        $actorIds = [
            'actor_1_id' => $attributes['actor_1_id'] ?? null,
            'actor_2_id' => $attributes['actor_2_id'] ?? null,
        ];
        $pivotAttributes = [
            'actor_1' => $attributes['association_1'] ?? null,
            'actor_2' => $attributes['association_2'] ?? null,
        ];
        $interactionAttributes = [
            'actor_1' => $attributes['interaction_1'] ?? null,
            'actor_2' => $attributes['interaction_2'] ?? null,
        ];
        unset(
            $attributes['province_id'],
            $attributes['actor_1_id'],
            $attributes['actor_2_id']
        );

        if (!Auth::user()->isAdmin()) {
            $attributes['status'] = 'pending';
            $attributes['review_notes'] = null;
        }

        if (!$event->exists && Auth::user()->isAdmin()) {
            $attributes['status'] ??= 'approved';
        } elseif (Auth::user()->isAdmin() && isset($attributes['status']) && $attributes['status'] !== 'rejected') {
            $attributes['review_notes'] = null;
        }

        DB::transaction(function () use ($event, $attributes, $actorIds, $pivotAttributes, $interactionAttributes, $creatorId) {
            $event->fill([
                ...$attributes,
                'source_row' => $event->exists ? $event->source_row : null,
                'created_by_user_id' => $event->exists ? $event->created_by_user_id : $creatorId,
            ])->save();

            $sync = [];
            foreach ($actorIds as $field => $actorId) {
                if ($actorId !== null) {
                    $role = $field === 'actor_1_id' ? 'actor_1' : 'actor_2';
                    $sync[$actorId] = [
                        'role' => $role,
                        'association' => $pivotAttributes[$role],
                        'interaction' => $interactionAttributes[$role],
                    ];
                }
            }

            $event->actors()->sync($sync);
        });
    }

    private function activeReferenceRule(string $modelClass, ?int $currentId = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($modelClass, $currentId) {
            if ($currentId !== null && (int) $value === $currentId) {
                return;
            }

            if (!$modelClass::query()->whereKey($value)->where('is_active', true)->exists()) {
                $fail('La valeur choisie est bloquée ou n’existe plus.');
            }
        };
    }

    private function actorIdForRole(?ConflictEvent $event, string $role): ?int
    {
        if (!$event?->exists) {
            return null;
        }

        $id = DB::table('conflict_event_actors')
            ->where('conflict_event_id', $event->id)
            ->where('role', $role)
            ->value('actor_id');

        return $id === null ? null : (int) $id;
    }
}
