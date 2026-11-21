@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', $event->exists ? 'Modifier un événement' : 'Saisir un événement')

@section('content')
@php($editing = $event->exists)

<div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mb-6">
  <div>
    <h4 class="mb-1">{{ $editing ? 'Modifier un événement' : 'Saisir un événement' }}</h4>
    <p class="text-muted mb-0">
      @if (auth()->user()->isAdmin())
        Les champs marqués d’un astérisque sont obligatoires.
      @else
        Votre contribution sera publiée après validation par un administrateur. Les champs marqués d’un astérisque sont obligatoires.
      @endif
    </p>
  </div>
  <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Retour à la liste</a>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

@if ($editing && $event->status === 'rejected' && $event->review_notes)
  <div class="alert alert-warning" role="status">
    <strong>Motif du refus :</strong> {{ $event->review_notes }}
    <div>En enregistrant les corrections, l’événement sera soumis à nouveau à validation.</div>
  </div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (auth()->user()->isAdmin())
  <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <span class="text-muted me-1">Gérer les référentiels :</span>
    @foreach ([
      'provinces' => 'Provinces',
      'event-types' => 'Types',
      'sub-events' => 'Sous-événements',
      'other-events' => 'Autres événements',
      'themes' => 'Thématiques',
      'subthemes' => 'Sous-thématiques',
      'actors' => 'Acteurs',
    ] as $catalog => $label)
      <a href="{{ route('admin.references.index', $catalog) }}" class="btn btn-sm btn-outline-primary">{{ $label }}</a>
    @endforeach
  </div>
@endif

<form method="POST" action="{{ $editing ? route('admin.events.update', $event) : route('admin.events.store') }}">
  @csrf
  @if ($editing)
    @method('PUT')
  @endif
  <div class="card mb-6">
    <div class="card-header"><h5 class="card-title mb-0">Classification</h5></div>
    <div class="card-body row g-4">
      @if (auth()->user()->isAdmin())
        @php($publicationStatus = old('status', $event->exists ? $event->status : 'approved'))
        <div class="col-md-4">
          <label for="status" class="form-label">Publication</label>
          <select id="status" name="status" class="form-select" required>
            <option value="pending" @selected($publicationStatus === 'pending')>Ne pas publier — en attente</option>
            <option value="approved" @selected($publicationStatus === 'approved')>Publier sur le tableau public</option>
            @if ($editing && $event->status === 'rejected')
              <option value="rejected" @selected($publicationStatus === 'rejected')>Refusé</option>
            @endif
          </select>
        </div>
      @endif
      <div class="col-md-4">
        <label for="event_year" class="form-label">Année *</label>
        <input id="event_year" name="event_year" type="number" min="1900" max="2100" class="form-control" value="{{ old('event_year', $event->event_year) }}" required>
      </div>
      <div class="col-md-4">
        <label for="event_date_raw" class="form-label">Date de l’événement</label>
        <input id="event_date_raw" name="event_date_raw" type="text" class="form-control" value="{{ old('event_date_raw', $event->event_date_raw) }}" placeholder="Ex. 12-mai-25">
      </div>
      <div class="col-md-4">
        <label for="province_id" class="form-label">Province *</label>
        <select id="province_id" name="province_id" class="form-select" required>
          <option value="">Choisir…</option>
          @foreach ($provinces as $province)
            <option value="{{ $province->id }}" @selected(old('province_id', $provinces->firstWhere('name', $event->province)?->id) == $province->id)>{{ $province->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="event_type_id" class="form-label">Type d’événement *</label>
        <select id="event_type_id" name="event_type_id" class="form-select" required>
          <option value="">Choisir…</option>
          @foreach ($eventTypes as $type)
            <option value="{{ $type->id }}" @selected(old('event_type_id', $event->event_type_id) == $type->id)>{{ $type->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="sub_event_id" class="form-label">Sous-événement</label>
        <select id="sub_event_id" name="sub_event_id" class="form-select">
          <option value="">Aucun</option>
          @foreach ($eventTypes as $type)
            @foreach ($type->subEvents as $subEvent)
              <option value="{{ $subEvent->id }}" @selected(old('sub_event_id', $event->sub_event_id) == $subEvent->id)>{{ $type->name }} — {{ $subEvent->name }}</option>
            @endforeach
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="other_event_id" class="form-label">Autre événement</label>
        <select id="other_event_id" name="other_event_id" class="form-select">
          <option value="">Aucun</option>
          @foreach ($otherEvents as $otherEvent)
            <option value="{{ $otherEvent->id }}" @selected(old('other_event_id', $event->other_event_id) == $otherEvent->id)>{{ $otherEvent->description }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="theme_id" class="form-label">Thématique *</label>
        <select id="theme_id" name="theme_id" class="form-select" required>
          <option value="">Choisir…</option>
          @foreach ($themes as $theme)
            <option value="{{ $theme->id }}" @selected(old('theme_id', $event->theme_id) == $theme->id)>{{ $theme->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="subtheme_id" class="form-label">Sous-thématique</label>
        <select id="subtheme_id" name="subtheme_id" class="form-select">
          <option value="">Aucune</option>
          @foreach ($themes as $theme)
            @foreach ($theme->subthemes as $subtheme)
              <option value="{{ $subtheme->id }}" @selected(old('subtheme_id', $event->subtheme_id) == $subtheme->id)>{{ $theme->name }} — {{ $subtheme->name }}</option>
            @endforeach
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="time_period" class="form-label">Période / heure</label>
        <input id="time_period" name="time_period" type="number" min="0" class="form-control" value="{{ old('time_period', $event->time_period) }}">
      </div>
    </div>
  </div>

  <div class="card mb-6">
    <div class="card-header"><h5 class="card-title mb-0">Lieu et coordonnées</h5></div>
    <div class="card-body row g-4">
      @foreach (['department' => 'Département', 'commune' => 'Commune', 'locality' => 'Localité', 'neighborhood' => 'Quartier', 'street' => 'Rue'] as $field => $label)
        <div class="col-md-4">
          <label for="{{ $field }}" class="form-label">{{ $label }}</label>
          <input id="{{ $field }}" name="{{ $field }}" type="text" class="form-control" value="{{ old($field, $event->{$field}) }}">
        </div>
      @endforeach
      <div class="col-md-4">
        <label for="latitude" class="form-label">Latitude</label>
        <input id="latitude" name="latitude" type="number" step="any" min="-90" max="90" class="form-control" value="{{ old('latitude', $event->latitude) }}">
      </div>
      <div class="col-md-4">
        <label for="longitude" class="form-label">Longitude</label>
        <input id="longitude" name="longitude" type="number" step="any" min="-180" max="180" class="form-control" value="{{ old('longitude', $event->longitude) }}">
      </div>
      <div class="col-md-4">
        <label for="geoprecision" class="form-label">Précision géographique</label>
        <input id="geoprecision" name="geoprecision" type="number" min="0" max="255" class="form-control" value="{{ old('geoprecision', $event->geoprecision) }}">
      </div>
    </div>
  </div>

  <div class="card mb-6">
    <div class="card-header"><h5 class="card-title mb-0">Acteurs</h5></div>
    <div class="card-body row g-4">
      @foreach ([1, 2] as $number)
        <div class="col-md-6">
          <label for="actor_{{ $number }}_id" class="form-label">Acteur {{ $number }}</label>
          <select id="actor_{{ $number }}_id" name="actor_{{ $number }}_id" class="form-select">
            <option value="">Aucun</option>
            @foreach ($actors as $actor)
              <option value="{{ $actor->id }}" @selected(old("actor_{$number}_id", $event->{"actor_{$number}_id"}) == $actor->id)>{{ $actor->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label for="association_{{ $number }}" class="form-label">Association {{ $number }}</label>
          <input id="association_{{ $number }}" name="association_{{ $number }}" type="text" class="form-control" value="{{ old("association_{$number}", $event->{"association_{$number}"}) }}">
        </div>
        <div class="col-md-3">
          <label for="interaction_{{ $number }}" class="form-label">Interaction {{ $number }}</label>
          <input id="interaction_{{ $number }}" name="interaction_{{ $number }}" type="text" class="form-control" value="{{ old("interaction_{$number}", $event->{"interaction_{$number}"}) }}">
        </div>
      @endforeach
    </div>
  </div>

  <div class="card mb-6">
    <div class="card-header"><h5 class="card-title mb-0">Impacts et source</h5></div>
    <div class="card-body row g-4">
      @foreach (['deaths' => 'Décès', 'injuries' => 'Blessés', 'kidnappings' => 'Enlèvements', 'arrests' => 'Arrestations', 'property_damage' => 'Dégâts matériels', 'sexual_violence' => 'Violences sexuelles', 'displaced_people' => 'Personnes déplacées'] as $field => $label)
        <div class="col-sm-6 col-lg-3">
          <label for="{{ $field }}" class="form-label">{{ $label }}</label>
          <input id="{{ $field }}" name="{{ $field }}" type="number" min="0" class="form-control" value="{{ old($field, $event->{$field}) }}">
        </div>
      @endforeach
      <div class="col-md-6">
        <label for="source" class="form-label">Source</label>
        <input id="source" name="source" type="text" class="form-control" value="{{ old('source', $event->source) }}">
      </div>
      <div class="col-md-6">
        <label for="source_type" class="form-label">Type de source</label>
        <input id="source_type" name="source_type" type="text" class="form-control" value="{{ old('source_type', $event->source_type) }}">
      </div>
      <div class="col-12">
        <label for="source_url_1" class="form-label">Lien vers la source</label>
        <input id="source_url_1" name="source_url_1" type="url" class="form-control" value="{{ old('source_url_1', $event->source_url_1) }}">
      </div>
      <div class="col-12">
        <label for="event_notes" class="form-label">Notes</label>
        <textarea id="event_notes" name="event_notes" class="form-control" rows="4">{{ old('event_notes', $event->event_notes) }}</textarea>
      </div>
      <div class="col-12 form-check ms-3">
        <input id="pastoral" name="pastoral" type="checkbox" class="form-check-input" value="1" @checked(old('pastoral', $event->pastoral))>
        <label for="pastoral" class="form-check-label">Événement lié au pastoralisme</label>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-3 mb-6">
    <button type="submit" class="btn btn-primary">{{ $editing ? 'Enregistrer les modifications' : 'Enregistrer l’événement' }}</button>
    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Annuler</a>
  </div>
</form>

@if ($editing)
  <form method="POST" action="{{ route('admin.events.destroy', $event) }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger">Supprimer l’événement</button>
  </form>
@endif
@endsection
