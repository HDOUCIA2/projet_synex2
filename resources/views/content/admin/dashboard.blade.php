@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Administration | SYNEX')

@section('page-script')
  <script>
    window.synexAdminDashboardCharts = @js($charts);
    window.synexAdminDashboardMap = @js($mapData);
  </script>
  @vite('resources/assets/js/admin-dashboard.js')
@endsection

@section('content')
<main class="synex-admin-dashboard">
  <header class="synex-admin-hero mb-4">
    <span class="synex-admin-hero-accent" aria-hidden="true"></span>
    <div>
      <h1>Bienvenue dans l’espace d’administration SYNEX</h1>
      <p>Gérez les données de conflits, les contenus du site et suivez les actions de prévention et de résolution.</p>
    </div>
    <a class="btn synex-admin-hero-button" href="{{ route('admin.events.index', ['status' => 'pending']) }}">
      <i class="bx bx-list-check me-1"></i> Examiner les observations
    </a>
  </header>

  @php
    $topMetrics = [
      ['label' => 'Observations', 'value' => $totalEvents, 'detail' => number_format($pendingEvents, 0, ',', ' ').' à valider', 'icon' => 'bx-file'],
      ['label' => 'Campagnes de prévention', 'value' => $initiativeMetrics['campaign'], 'detail' => 'Enregistrements publiés', 'icon' => 'bx-shield-quarter'],
      ['label' => 'Médiations', 'value' => $initiativeMetrics['mediation'], 'detail' => 'Enregistrements publiés', 'icon' => 'bx-group'],
      ['label' => 'Arbitrages', 'value' => $initiativeMetrics['arbitration'], 'detail' => 'Enregistrements publiés', 'icon' => 'bx-briefcase'],
      ['label' => 'Accords de paix', 'value' => $initiativeMetrics['agreement'], 'detail' => 'Enregistrements publiés', 'icon' => 'bx-heart'],
    ];
  @endphp

  <section class="row g-3 mb-4 synex-admin-stats" aria-label="Indicateurs clés">
    @foreach ($topMetrics as $metric)
      <div class="col-12 col-sm-6 col-xl">
        <article class="card synex-admin-stat h-100">
          <div class="card-body">
            <span class="synex-admin-stat-icon"><i class="bx {{ $metric['icon'] }}"></i></span>
            <div class="synex-admin-stat-copy">
              <strong>{{ number_format($metric['value'], 0, ',', ' ') }}</strong>
              <p>{{ $metric['label'] }}</p>
              <small>{{ $metric['detail'] }}</small>
            </div>
          </div>
        </article>
      </div>
    @endforeach
  </section>

  <section class="card synex-admin-panel synex-admin-map-panel mb-4" aria-labelledby="synexAdminMapTitle">
    <div class="card-header">
      <div>
        <h2 id="synexAdminMapTitle">Observations sur la carte du Tchad</h2>
        <p>Les nombres incluent les observations de tous les statuts; les points GPS situés au Tchad sont colorés selon leur type.</p>
      </div>
      <span class="synex-admin-map-total"><i class="bx bx-map-pin me-1"></i>{{ number_format($totalEvents, 0, ',', ' ') }} observations en base</span>
    </div>
    <div class="card-body">
      <div id="synexAdminChadMap" class="synex-admin-map" role="application" aria-label="Carte interactive des observations par province au Tchad"></div>
      <p id="synexAdminMapStatus" class="synex-admin-map-status" role="status">Chargement de la carte…</p>
      <div class="synex-admin-map-legend" aria-label="Légende de la carte">
        <div class="synex-admin-map-legend-row">
          <strong>Nombre d’observations par province</strong>
          <span><i class="synex-admin-map-swatch synex-admin-map-swatch-none"></i>0</span>
          <span><i class="synex-admin-map-swatch synex-admin-map-swatch-low"></i>1–9</span>
          <span><i class="synex-admin-map-swatch synex-admin-map-swatch-medium"></i>10–19</span>
          <span><i class="synex-admin-map-swatch synex-admin-map-swatch-high"></i>20–49</span>
          <span><i class="synex-admin-map-swatch synex-admin-map-swatch-highest"></i>50+</span>
          <span><i class="synex-admin-map-swatch synex-admin-map-swatch-high"></i>50+</span>
        </div>
        <div class="synex-admin-map-legend-row">
          <strong>Types d’événements (observations enregistrées)</strong>
          @forelse ($mapTypeLegend as $type)
            <span><i class="synex-admin-map-swatch" style="--synex-map-swatch: {{ $type['color'] }}"></i>{{ $type['label'] }} ({{ number_format($type['count'], 0, ',', ' ') }})</span>
          @empty
            <span>Aucun événement enregistré</span>
          @endforelse
        </div>
      </div>
      <p class="synex-admin-map-attribution">Limites administratives : GeoBoundaries, licence ODbL 1.0.</p>
    </div>
  </section>

  <section class="row g-3 mb-4" aria-label="Analyses des observations">
    <div class="col-12 col-xl-8">
      <article class="card synex-admin-panel h-100">
        <div class="card-header">
          <div>
            <h2>Évolution des observations</h2>
            <p>Répartition selon l’année renseignée</p>
          </div>
          <span class="synex-admin-panel-icon"><i class="bx bx-trending-up"></i></span>
        </div>
        <div class="card-body"><div id="synexAdminAnnualChart" class="synex-admin-chart" role="img" aria-label="Graphique des observations par année"></div></div>
      </article>
    </div>
    <div class="col-12 col-xl-4">
      <article class="card synex-admin-panel h-100">
        <div class="card-header">
          <div>
            <h2>Thématiques principales</h2>
            <p>Les sept plus représentées</p>
          </div>
          <span class="synex-admin-panel-icon"><i class="bx bx-pie-chart-alt-2"></i></span>
        </div>
        <div class="card-body"><div id="synexAdminThemeChart" class="synex-admin-chart synex-admin-chart-donut" role="img" aria-label="Graphique des observations par thématique"></div></div>
      </article>
    </div>
  </section>

  <section class="row g-3 mb-4" aria-label="Répartition territoriale et qualité des données">
    <div class="col-12 col-xl-7">
      <article class="card synex-admin-panel h-100">
        <div class="card-header">
          <div>
            <h2>Observations par territoire</h2>
            <p>Les sept provinces les plus représentées</p>
          </div>
          <span class="synex-admin-panel-icon"><i class="bx bx-map"></i></span>
        </div>
        <div class="card-body"><div id="synexAdminProvinceChart" class="synex-admin-chart" role="img" aria-label="Graphique des observations par province"></div></div>
      </article>
    </div>
    <div class="col-12 col-xl-5">
      <article class="card synex-admin-quality h-100">
        <div class="card-body">
          <div class="synex-admin-quality-heading">
            <span class="synex-admin-quality-icon"><i class="bx bx-check-shield"></i></span>
            <div><h2>Qualité des données</h2><p>Complétude des champs requis</p></div>
          </div>
          <div class="synex-admin-quality-rate">{{ number_format($completionRate, 1, ',', ' ') }}<span>%</span></div>
          <div class="progress synex-admin-quality-progress" role="progressbar" aria-label="Taux de complétude" aria-valuenow="{{ round($completionRate) }}" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar" style="width: {{ min(100, max(0, $completionRate)) }}%"></div>
          </div>
          <div class="synex-admin-quality-detail">
            <span>Fiches avec année, province, type et thématique</span>
            <strong>{{ number_format($completeEvents, 0, ',', ' ') }} / {{ number_format($totalEvents, 0, ',', ' ') }}</strong>
          </div>
          <div class="synex-admin-quality-detail">
            <span>Fiches avec coordonnées valides</span>
            <strong>{{ number_format($geolocatedEvents, 0, ',', ' ') }} / {{ number_format($totalEvents, 0, ',', ' ') }}</strong>
          </div>
          <div class="synex-admin-quality-detail">
            <span>Observations rejetées</span>
            <strong>{{ number_format($rejectedEvents, 0, ',', ' ') }}</strong>
          </div>
          <a href="{{ route('admin.events.index') }}" class="btn btn-outline-primary w-100 mt-3">Consulter les observations</a>
        </div>
      </article>
    </div>
  </section>

  <section class="row g-3" aria-label="Dernières observations et actions">
    <div class="col-12 col-xl-8">
      <article class="card synex-admin-panel h-100">
        <div class="card-header">
          <div>
            <h2>Dernières observations</h2>
            <p>Les fiches ajoutées le plus récemment</p>
          </div>
          <a href="{{ route('admin.events.index') }}" class="synex-admin-text-link">Tout voir <i class="bx bx-right-arrow-alt"></i></a>
        </div>
        <div class="table-responsive">
          <table class="table synex-admin-events-table">
            <thead>
              <tr><th>Date</th><th>Observation</th><th>Territoire</th><th>Thématique</th><th>Statut</th></tr>
            </thead>
            <tbody>
              @forelse ($recentEvents as $event)
                @php
                  $statusLabel = ['pending' => 'En attente', 'approved' => 'Validée', 'rejected' => 'Rejetée'][$event->status] ?? 'Non défini';
                @endphp
                <tr>
                  <td>{{ $event->event_date_raw ?: ($event->event_year ?: '—') }}</td>
                  <td>
                    <a href="{{ route('admin.events.edit', $event->id) }}" class="synex-admin-event-title">{{ $event->event_type ?: 'Observation' }}</a>
                    <small>{{ $event->locality ?: 'Localité non renseignée' }}</small>
                  </td>
                  <td>{{ $event->province ?: '—' }}</td>
                  <td>{{ $event->theme ?: '—' }}</td>
                  <td><span class="synex-admin-status synex-admin-status-{{ $event->status ?: 'unknown' }}">{{ $statusLabel }}</span></td>
                </tr>
              @empty
                <tr><td colspan="5" class="synex-admin-empty">Aucune observation enregistrée pour le moment.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </article>
    </div>
    <div class="col-12 col-xl-4" id="synex-admin-quick-actions">
      <article class="card synex-admin-panel h-100">
        <div class="card-header">
          <div><h2>Actions rapides</h2><p>Accès direct aux modules</p></div>
          <span class="synex-admin-panel-icon"><i class="bx bx-bolt-circle"></i></span>
        </div>
        <div class="card-body synex-admin-quick-actions">
          <a href="{{ route('admin.events.create') }}"><span><i class="bx bx-plus-circle"></i></span><div><strong>Ajouter une observation</strong><small>Créer une nouvelle fiche</small></div><i class="bx bx-chevron-right"></i></a>
          <a href="{{ route('admin.import.index') }}"><span><i class="bx bx-upload"></i></span><div><strong>Importer des données</strong><small>Charger un fichier CSV</small></div><i class="bx bx-chevron-right"></i></a>
          <a href="{{ route('admin.prevention.initiatives.create') }}"><span><i class="bx bx-shield-plus"></i></span><div><strong>Nouvelle initiative</strong><small>Prévention ou résolution</small></div><i class="bx bx-chevron-right"></i></a>
          <a href="{{ route('admin.contact.index') }}"><span><i class="bx bx-envelope"></i></span><div><strong>Messages de contact</strong><small>Consulter les demandes reçues</small></div><i class="bx bx-chevron-right"></i></a>
        </div>
      </article>
    </div>
  </section>
</main>
@endsection
