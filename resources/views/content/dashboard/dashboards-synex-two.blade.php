@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', 'Tableau de bord 2 | SYNEX')

@section('page-style')
  @vite('resources/assets/css/dashboard-synex-two.css')
@endsection

@section('page-script')
  <script>
    window.synexDashboardTwoData = @js([
      'annualTrend' => $annualTrend,
      'provinceDistribution' => $provinceDistribution,
      'themeDistribution' => $themeDistribution,
    ]);
  </script>
  @vite('resources/assets/js/dashboard-synex-two.js')
@endsection

@section('content')
<div class="synex-dashboard-two">
  @include('content.pages._public-navbar', ['activePage' => 'analyses'])

  <header class="synex-dashboard-two-hero">
    <div class="container-xxl px-4">
      <h1>Tableau de bord des dynamiques de conflits au Tchad</h1>
      <p>Explorez les données documentées sur les conflits, leurs dynamiques territoriales,<br class="d-none d-md-block"> les acteurs impliqués et les réponses apportées.</p>
    </div>
  </header>

  <main class="container-fluid px-3 px-xl-4 py-3">
    <form method="GET" action="{{ route('dashboard-analytics-two') }}" class="synex-dashboard-two-filters" aria-label="Filtres du tableau de bord">
      <strong class="synex-dashboard-two-filter-title"><i class="bx bx-filter-alt"></i> Filtres</strong>
      <label>
        <span>Période</span>
        <select name="year" class="form-select">
          <option value="">Toutes les périodes</option>
          @foreach ($years as $year)
            <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
          @endforeach
        </select>
      </label>
      <label>
        <span>Province</span>
        <select name="province" class="form-select">
          <option value="">Toutes les provinces</option>
          @foreach ($provinces as $province)
            <option value="{{ $province }}" @selected(($filters['province'] ?? '') === $province)>{{ $province }}</option>
          @endforeach
        </select>
      </label>
      <label>
        <span>Département</span>
        <select name="department" class="form-select">
          <option value="">Tous les départements</option>
          @foreach ($departments as $department)
            <option value="{{ $department }}" @selected(($filters['department'] ?? '') === $department)>{{ $department }}</option>
          @endforeach
        </select>
      </label>
      <label>
        <span>Thématique</span>
        <select name="theme" class="form-select">
          <option value="">Toutes les thématiques</option>
          @foreach ($themes as $theme)
            <option value="{{ $theme }}" @selected(($filters['theme'] ?? '') === $theme)>{{ $theme }}</option>
          @endforeach
        </select>
      </label>
      <label>
        <span>Type d'événement</span>
        <select name="event_type" class="form-select">
          <option value="">Tous les types</option>
          @foreach ($eventTypes as $eventType)
            <option value="{{ $eventType }}" @selected(($filters['event_type'] ?? '') === $eventType)>{{ $eventType }}</option>
          @endforeach
        </select>
      </label>
      <label>
        <span>Acteur</span>
        <select name="actor" class="form-select">
          <option value="">Tous les acteurs</option>
          @foreach ($actors as $actor)
            <option value="{{ $actor }}" @selected(($filters['actor'] ?? '') === $actor)>{{ $actor }}</option>
          @endforeach
        </select>
      </label>
      <div class="synex-dashboard-two-filter-actions">
        <button type="submit" class="btn btn-primary" aria-label="Appliquer les filtres"><i class="bx bx-search-alt-2"></i></button>
        <a href="{{ route('dashboard-analytics-two') }}" class="btn btn-outline-primary" aria-label="Réinitialiser les filtres"><i class="bx bx-reset"></i></a>
      </div>
    </form>

    <section class="synex-dashboard-two-kpis" aria-label="Indicateurs clés">
      <article class="synex-dashboard-two-card synex-dashboard-two-kpi">
        <span class="synex-dashboard-two-kpi-icon"><i class="bx bx-folder-open"></i></span>
        <div><strong>{{ number_format($metrics['observations'], 0, ',', ' ') }}</strong><b>Observations datées</b><small>Fiches principales dans la base</small></div>
      </article>
      <article class="synex-dashboard-two-card synex-dashboard-two-kpi">
        <span class="synex-dashboard-two-kpi-icon"><i class="bx bx-map-pin"></i></span>
        <div><strong>{{ number_format($metrics['provinces_covered'], 0, ',', ' ') }}</strong><b>Provinces couvertes</b><small>{{ collect($provinceDistribution)->take(3)->pluck('province')->join(', ') ?: 'Aucune donnée disponible' }}</small></div>
      </article>
      <article class="synex-dashboard-two-card synex-dashboard-two-kpi">
        <span class="synex-dashboard-two-kpi-icon"><i class="bx bx-map-alt"></i></span>
        <div><strong>{{ number_format($metrics['gps_valid'], 0, ',', ' ') }}</strong><b>Coordonnées GPS valides</b><small>{{ number_format($metrics['gps_rate'], 0, ',', ' ') }} % de couverture</small></div>
      </article>
      <article class="synex-dashboard-two-card synex-dashboard-two-kpi">
        <span class="synex-dashboard-two-kpi-icon"><i class="bx bx-git-merge"></i></span>
        <div><strong>{{ number_format($metrics['conflict_references'], 0, ',', ' ') }}</strong><b>Références de conflit</b><small>Identifiants distincts ({{ number_format($metrics['linked_observations'], 0, ',', ' ') }} fiches liées)</small></div>
      </article>
    </section>

    <section class="synex-dashboard-two-main-grid">
      <article class="synex-dashboard-two-card synex-dashboard-two-panel synex-dashboard-two-map-panel">
        <h2><i class="bx bxs-map-alt"></i> Répartition des observations par province</h2>
        <div id="synexDashboardTwoMap" class="synex-dashboard-two-map" data-geojson-url="{{ asset('assets/geo/chad-adm1.geojson') }}" role="img" aria-label="Carte des observations par province au Tchad"></div>
        <div class="synex-dashboard-two-map-legend"><span><i></i> Province couverte (avec données)</span><span><i></i> Aucune donnée disponible</span></div>
      </article>
      <article class="synex-dashboard-two-card synex-dashboard-two-panel">
        <h2><i class="bx bx-bar-chart-alt-2"></i> Évolution annuelle des observations</h2>
        <div id="synexDashboardTwoAnnualChart" class="synex-dashboard-two-annual-chart" role="img" aria-label="Évolution annuelle des observations"></div>
        @if (now()->year === (int) collect($annualTrend['labels'])->last())
          <p class="synex-dashboard-two-note"><i class="bx bxs-info-circle"></i> {{ now()->year }} : année en cours, données partielles.</p>
        @endif
      </article>
      <article class="synex-dashboard-two-card synex-dashboard-two-panel">
        <h2><i class="bx bx-pie-chart-alt-2"></i> Répartition par thématique</h2>
        <div class="synex-dashboard-two-theme-layout">
          <div id="synexDashboardTwoThemeChart" class="synex-dashboard-two-theme-chart" role="img" aria-label="Répartition des observations par thématique"></div>
          <ul class="synex-dashboard-two-theme-legend">
            @forelse ($themeDistribution as $theme)
              <li><i style="--theme-color: {{ $theme['color'] }}"></i><span>{{ $theme['label'] }}</span><strong>{{ number_format($theme['count'], 0, ',', ' ') }} <small>({{ $metrics['observations'] > 0 ? number_format(($theme['count'] / $metrics['observations']) * 100, 1, ',', ' ') : '0' }} %)</small></strong></li>
            @empty
              <li class="synex-dashboard-two-empty">Aucune thématique disponible.</li>
            @endforelse
          </ul>
        </div>
      </article>
    </section>

    <section class="synex-dashboard-two-lower-grid">
      <article class="synex-dashboard-two-card synex-dashboard-two-panel synex-dashboard-two-associations">
        <h2><i class="bx bx-group"></i> Principales associations d'acteurs</h2>
        <p class="synex-dashboard-two-panel-intro">Paires les plus souvent mentionnées dans les observations.</p>
        @php
          $maxActorPair = max(1, (int) collect($actorPairs)->max('count'));
        @endphp
        @forelse ($actorPairs as $pair)
          <div class="synex-dashboard-two-actor-row">
            <span title="{{ $pair['label'] }}">{{ $pair['label'] }}</span>
            <i><b style="width: {{ min(100, ($pair['count'] / $maxActorPair) * 100) }}%"></b></i>
            <strong>{{ number_format($pair['count'], 0, ',', ' ') }}</strong>
          </div>
        @empty
          <p class="synex-dashboard-two-empty">Aucune association d'acteurs disponible.</p>
        @endforelse
      </article>
      <article class="synex-dashboard-two-card synex-dashboard-two-panel synex-dashboard-two-responses">
        <h2><i class="bx bx-handshake"></i> Mécanismes de prévention et de résolution</h2>
        <p class="synex-dashboard-two-panel-intro">Actions documentées dans la base.</p>
        <div class="synex-dashboard-two-response-grid">
          @forelse ($peaceResponses as $response)
            <div><i class="bx {{ $response['icon'] }}"></i><strong>{{ number_format($response['count'], 0, ',', ' ') }}</strong><span>{{ $response['label'] }}</span></div>
          @empty
            <p class="synex-dashboard-two-empty">Aucune action de prévention ou de résolution documentée.</p>
          @endforelse
        </div>
      </article>
      <article class="synex-dashboard-two-card synex-dashboard-two-panel synex-dashboard-two-observations">
        <div class="synex-dashboard-two-observations-heading">
          <h2><i class="bx bx-list-ul"></i> Dernières observations</h2>
          <a href="{{ route('dashboard-analytics') }}">Voir tout <i class="bx bx-right-arrow-alt"></i></a>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Date</th><th>Événement</th><th>Thématique</th><th>Province</th></tr></thead>
            <tbody>
              @forelse ($recentObservations as $observation)
                <tr><td>{{ $observation['date'] }}</td><td>{{ $observation['event'] }}</td><td>{{ $observation['theme'] }}</td><td>{{ $observation['province'] }}</td></tr>
              @empty
                <tr><td colspan="4" class="text-center">Aucune observation publique disponible.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>
    </section>
  </main>
</div>
@endsection
