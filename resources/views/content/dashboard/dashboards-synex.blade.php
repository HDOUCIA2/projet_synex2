@extends('layouts/contentNavbarLayout')

@section('title', 'Salle de situation')

@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@vite('resources/assets/css/veille.css')
@endsection

@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

@section('page-script')
@vite(['resources/assets/js/dashboards-synex.js', 'resources/assets/js/veille-animations.js'])
@endsection

@section('content')
@php
  $dashboardHeader = $dashboardSections->get('dashboard_header');
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mb-6">
  <div>
    @if ($dashboardHeader?->is_active)
      <h4 class="mb-1" data-veille-animation="fade-up">{{ $dashboardHeader->title }}</h4>
      @if ($dashboardHeader->description)<p class="text-muted mb-0" data-veille-animation="fade-up">{{ $dashboardHeader->description }}</p>@endif
    @endif
  </div>
  <div class="d-flex flex-wrap gap-2">
    @if (auth()->user()?->isAdmin())
      <a href="{{ route('admin.veille.index') }}" class="btn btn-outline-primary"><i class="icon-base bx bx-slider-alt me-1"></i>Gérer le contenu</a>
    @endif
    <a href="{{ route('dashboard-analytics-base') }}" class="btn btn-outline-secondary"><i class="icon-base bx bx-copy me-1"></i>Vue analytique de base</a>
  </div>
</div>

<div class="alert alert-info d-flex align-items-start gap-2 mb-6" role="status">
  <i class="icon-base bx bx-info-circle mt-1"></i>
  <div>
    <strong>Données du fichier fourni</strong>
    <span class="d-block">Les graphiques portent sur les événements importés. Les champs d’impact vides ne sont pas comptés comme des valeurs renseignées.</span>
  </div>
</div>

<form method="GET" action="{{ route('dashboard-analytics') }}" class="card mb-6">
  <div class="card-body">
    <div class="row g-4 align-items-end">
      <div class="col-sm-6 col-xl-2">
        <label for="filter-year" class="form-label">Année</label>
        <select id="filter-year" name="year" class="form-select">
          <option value="">Toutes</option>
          @foreach ($years as $year)
            <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-2">
        <label for="filter-province" class="form-label">Province</label>
        <select id="filter-province" name="province" class="form-select">
          <option value="">Toutes</option>
          @foreach ($provinces as $province)
            <option value="{{ $province }}" @selected(($filters['province'] ?? '') === $province)>{{ $province }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-2">
        <label for="filter-event-type" class="form-label">Type d’événement</label>
        <select id="filter-event-type" name="event_type_id" class="form-select">
          <option value="">Tous</option>
          @foreach ($eventTypes as $eventType)
            <option value="{{ $eventType->id }}" @selected(($filters['event_type_id'] ?? '') == $eventType->id)>{{ $eventType->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-2">
        <label for="filter-sub-event" class="form-label">Sous-événement</label>
        <select id="filter-sub-event" name="sub_event_id" class="form-select">
          <option value="">Tous</option>
          @foreach ($subEvents as $subEvent)
            <option value="{{ $subEvent->id }}" @selected(($filters['sub_event_id'] ?? '') == $subEvent->id)>
              {{ $subEvent->name }}{{ $subEvent->eventType ? ' — '.$subEvent->eventType->name : '' }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-2">
        <label for="filter-other-event" class="form-label">Autre</label>
        <select id="filter-other-event" name="other_event_id" class="form-select">
          <option value="">Tous</option>
          @foreach ($otherEvents as $otherEvent)
            <option value="{{ $otherEvent->id }}" @selected(($filters['other_event_id'] ?? '') == $otherEvent->id)>{{ $otherEvent->description }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-2">
        <label for="filter-theme" class="form-label">Thématique</label>
        <select id="filter-theme" name="theme_id" class="form-select">
          <option value="">Toutes</option>
          @foreach ($themes as $theme)
            <option value="{{ $theme->id }}" @selected(($filters['theme_id'] ?? '') == $theme->id)>{{ $theme->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-2">
        <label for="filter-subtheme" class="form-label">Sous-thématique</label>
        <select id="filter-subtheme" name="subtheme_id" class="form-select">
          <option value="">Toutes</option>
          @foreach ($subthemes as $subtheme)
            <option value="{{ $subtheme->id }}" @selected(($filters['subtheme_id'] ?? '') == $subtheme->id)>
              {{ $subtheme->name }}{{ $subtheme->theme ? ' — '.$subtheme->theme->name : '' }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-2">
        <label for="filter-actor" class="form-label">Acteur</label>
        <select id="filter-actor" name="actor_id" class="form-select">
          <option value="">Tous</option>
          @foreach ($actors as $actor)
            <option value="{{ $actor->id }}" @selected(($filters['actor_id'] ?? '') == $actor->id)>{{ $actor->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-xl-3">
        <label for="filter-search" class="form-label">Recherche</label>
        <input id="filter-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Lieu, type, acteur, note…">
      </div>
      <div class="col-12 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="icon-base bx bx-filter-alt me-1"></i>
          Appliquer les filtres
        </button>
        <a href="{{ route('dashboard-analytics') }}" class="btn btn-outline-secondary">Réinitialiser</a>
      </div>
    </div>
  </div>
</form>

@php
  $metricSection = $dashboardSections->get('dashboard_metrics');
@endphp
@if ($metricSection?->is_active)
  <div class="mb-6">
    @if ($metricSection->title)<h5 class="mb-1">{{ $metricSection->title }}</h5>@endif
    @if ($metricSection->description)<p class="text-muted mb-4">{{ $metricSection->description }}</p>@endif
    <div class="row g-6">
      @php
        $metricColumn = max(3, (int) floor(12 / max(1, $metricSection->cards->count())));
      @endphp
      @foreach ($metricSection->cards as $card)
        <div class="col-sm-6 col-xl-{{ $metricColumn }}">
          <article class="card h-100 veille-content-card" data-veille-animation="{{ $card->animation }}">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <span class="text-muted">{{ $card->title }}</span>
                  <h3 class="mb-0 mt-2">{{ number_format($metricValues[$card->component_key] ?? 0, 0, ',', ' ') }}</h3>
                  @if ($card->description)<small class="text-muted d-block mt-2">{{ $card->description }}</small>@endif
                </div>
                @if ($card->icon)<span class="avatar-initial rounded bg-label-{{ $card->color }} p-2"><i class="icon-base bx {{ $card->icon }} icon-lg"></i></span>@endif
              </div>
            </div>
          </article>
        </div>
      @endforeach
    </div>
  </div>
@endif

@php
  $chartSection = $dashboardSections->get('dashboard_charts');
@endphp
@if ($chartSection?->is_active && $chartSection->cards->isNotEmpty())
  <section class="mb-6">
    <div class="mb-4">
      @if ($chartSection->title)<h5 class="mb-1">{{ $chartSection->title }}</h5>@endif
      @if ($chartSection->description)<p class="text-muted mb-0">{{ $chartSection->description }}</p>@endif
    </div>
    <div class="row g-6">
      @foreach ($chartSection->cards as $card)
        @php
          $chartIds = [
              'eventsByYear' => ['eventsByYearChart', 'col-12 col-xl-8'],
              'eventsByProvince' => ['eventsByProvinceChart', 'col-12 col-xl-4'],
              'eventsByType' => ['eventsByTypeChart', 'col-12 col-xl-6'],
              'eventsByTheme' => ['eventsByThemeChart', 'col-12 col-xl-6'],
              'impacts' => ['recordedImpactsChart', 'col-12'],
              'death_rate' => ['deathsPer100kChart', 'col-12'],
          ];
          $chartConfig = $chartIds[$card->component_key] ?? null;
        @endphp
        @if ($chartConfig)
          <div class="{{ $chartConfig[1] }}">
            <article class="card h-100 veille-content-card" data-veille-animation="{{ $card->animation }}">
              <div class="card-header">
                <h6 class="card-title mb-1">{{ $card->title }}</h6>
                @if ($card->description)<p class="card-subtitle mb-0">{{ $card->description }}</p>@endif
                @if ($card->component_key === 'death_rate' && $deathRateYear)<small class="text-muted">Année {{ $deathRateYear }}</small>@endif
              </div>
              <div class="card-body">
                <div id="{{ $chartConfig[0] }}" aria-label="{{ $card->title }}"></div>
                @if ($card->component_key === 'death_rate' && $deathRates->isEmpty())
                  <p class="text-muted mb-0">Saisissez les populations par province et par année dans la gestion de la veille pour calculer ce taux.</p>
                @endif
              </div>
            </article>
          </div>
        @endif
      @endforeach
    </div>
  </section>
@endif

@php
  $mapSection = $dashboardSections->get('dashboard_map');
@endphp
@if ($mapSection?->is_active)
  <div class="card mb-6 veille-content-card" data-veille-animation="fade-up">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h5 class="card-title mb-1">{{ $mapSection->title }}</h5>
        @if ($mapSection->description)<p class="card-subtitle mb-0">{{ $mapSection->description }}</p>@endif
      </div>
      <span class="badge bg-label-primary">{{ number_format(count($mapPoints), 0, ',', ' ') }} points</span>
    </div>
    <div class="card-body pt-0">
      <div id="synexEventsMap" role="application" aria-label="{{ $mapSection->title }}" style="height: 460px; min-height: 320px;"></div>
    </div>
  </div>
@endif

<script>
  window.synexDashboardCharts = @js($chartData);
  window.synexMapPoints = @js($mapPoints);
  window.synexDeathRateYear = @js($deathRateYear);
</script>

@php
  $journalSection = $dashboardSections->get('dashboard_journal');
@endphp
@if ($journalSection?->is_active)
<div class="card">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h5 class="card-title mb-1">{{ $journalSection->title }}</h5>
      @if ($journalSection->description)<p class="card-subtitle mb-1">{{ $journalSection->description }}</p>@endif
      <p class="card-subtitle mb-0">
        @if ($events->total())
          {{ number_format($events->firstItem(), 0, ',', ' ') }}–{{ number_format($events->lastItem(), 0, ',', ' ') }}
          sur {{ number_format($events->total(), 0, ',', ' ') }} événements
        @else
          Aucun événement ne correspond à ces filtres.
        @endif
      </p>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead>
        <tr>
          <th>Date</th>
          <th>Événement</th>
          <th>Thématique</th>
          <th>Province / localité</th>
          <th>Acteurs</th>
          <th>Impacts</th>
          <th>Source</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($events as $event)
          <tr>
            <td class="text-nowrap">{{ $event->event_date_raw ?: '—' }}</td>
            <td>
              <span class="fw-medium">{{ $event->eventTypeRecord?->name ?: '—' }}</span>
              @if ($event->subEventRecord)
                <small class="d-block text-muted">{{ $event->subEventRecord->name }}</small>
              @endif
              @if ($event->otherEventRecord)
                <small class="d-block text-muted">{{ $event->otherEventRecord->description }}</small>
              @endif
            </td>
            <td>
              <span>{{ $event->themeRecord?->name ?: '—' }}</span>
              @if ($event->subthemeRecord)
                <small class="d-block text-muted">{{ $event->subthemeRecord->name }}</small>
              @endif
            </td>
            <td>
              <span class="fw-medium">{{ $event->province ?: '—' }}</span>
              @if ($event->locality)
                <small class="d-block text-muted">{{ $event->locality }}</small>
              @endif
            </td>
            <td>{{ $event->actors->pluck('name')->join(' ↔ ') ?: '—' }}</td>
            <td class="text-nowrap">
              <span class="text-danger">{{ $event->deaths ?? 0 }} décès</span>
              <small class="d-block text-muted">{{ $event->injuries ?? 0 }} blessés</small>
            </td>
            <td>
              @if ($event->source_url_1 && in_array(parse_url($event->source_url_1, PHP_URL_SCHEME), ['http', 'https'], true))
                <a href="{{ $event->source_url_1 }}" target="_blank" rel="noopener noreferrer">{{ $event->source ?: 'Consulter' }}</a>
              @else
                {{ $event->source ?: '—' }}
              @endif
              @if ($event->event_notes)
                <small class="d-block text-muted">{{ \Illuminate\Support\Str::limit($event->event_notes, 90) }}</small>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-8 text-muted">Aucun événement à afficher.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if ($events->hasPages())
    <div class="card-footer">
      {{ $events->links('pagination::bootstrap-5') }}
    </div>
  @endif
</div>
@endif
@endsection
