@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', $siteSettings->name.' — Veille')

@section('page-style')
  @vite('resources/assets/css/veille.css')
@endsection

@section('page-script')
  @vite('resources/assets/js/veille-animations.js')
@endsection

@section('content')
  @php
    $dashboardTarget = auth()->user()?->isAdmin() ? route('admin.dashboard') : route('dashboard-analytics');
    $features = $veilleSections->get('home_features');
    $severity = $veilleSections->get('home_severity');
    $iconColors = ['red', 'green', 'blue', 'purple', 'orange'];
    $levelIcons = ['bx-check-circle', 'bx-error', 'bx-error-circle'];
  @endphp

  <div class="landing-shell">
    @include('content.pages._public-navbar', ['activePage' => 'veille'])

    <header class="veille-hero">
      <div class="container-xxl px-4">
        <div class="veille-hero-copy">
          <h1>Veille Territoriale</h1>
          <p>Suivi des dynamiques de conflits, de la situation sécuritaire et des signaux de tension sur l’ensemble du Tchad.</p>
        </div>
      </div>
    </header>

    <main class="container-xxl veille-page px-3 px-lg-4 py-3 py-lg-4">
      <nav aria-label="Fil d’Ariane" class="mb-3">
        <ol class="breadcrumb breadcrumb-chevron mb-0">
          <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Accueil</a></li>
          <li class="breadcrumb-item active" aria-current="page">Veille</li>
        </ol>
      </nav>

      <nav class="veille-section-nav mb-3" aria-label="Sections de la veille">
        <a class="active" href="#synthese" aria-current="page"><i class="icon-base bx bx-broadcast me-1" aria-hidden="true"></i>Vue d’ensemble</a>
        <a href="#evenements-recents"><i class="icon-base bx bx-bell me-1" aria-hidden="true"></i>Événements récents</a>
        <a href="#tendances"><i class="icon-base bx bx-pulse me-1" aria-hidden="true"></i>Tendances</a>
        <a href="#publications"><i class="icon-base bx bx-file me-1" aria-hidden="true"></i>Bulletins de veille</a>
        <a href="#sources"><i class="icon-base bx bx-layer me-1" aria-hidden="true"></i>Sources</a>
      </nav>

      <section id="synthese" class="card veille-filter-card shadow-sm mb-3" aria-label="Filtres de la veille">
        <form action="{{ route('veille') }}" method="GET" class="card-body">
          <div class="row g-2 align-items-center">
            <div class="col-12 col-md-4 col-xl-3">
              <label for="veille-period" class="visually-hidden">Période</label>
              <select id="veille-period" name="period" class="form-select">
                @foreach ($periodOptions as $value => $label)
                  <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4 col-xl-3">
              <label for="veille-province" class="visually-hidden">Région ou province</label>
              <select id="veille-province" name="province" class="form-select">
                <option value="">Toutes les régions</option>
                @foreach ($provinces as $province)
                  <option value="{{ $province }}" @selected(($filters['province'] ?? null) === $province)>{{ $province }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4 col-xl-4">
              <label for="veille-event-type" class="visually-hidden">Type d’événement</label>
              <select id="veille-event-type" name="event_type" class="form-select">
                <option value="">Tous les types d’événements</option>
                @foreach ($eventTypes as $eventType)
                  <option value="{{ $eventType }}" @selected(($filters['event_type'] ?? null) === $eventType)>{{ $eventType }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-xl-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-grow-1">
                <i class="icon-base bx bx-search me-1" aria-hidden="true"></i>Appliquer les filtres
              </button>
              @if ($filters)
                <a class="btn btn-outline-secondary" href="{{ route('veille') }}" aria-label="Effacer les filtres" title="Effacer les filtres">
                  <i class="icon-base bx bx-reset" aria-hidden="true"></i>
                </a>
              @endif
            </div>
          </div>
          @if ($period !== 'all' && $unparsedDateCount > 0)
            <p class="small text-muted mb-0 mt-2">
              {{ number_format($unparsedDateCount, 0, ',', ' ') }} événement(s) avec une date inexploitable ne sont pas inclus dans cette période.
            </p>
          @endif
        </form>
      </section>

      <section class="row g-3 mb-3" aria-label="Indicateurs clés">
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="card veille-metric-card h-100" aria-label="Nombre d’événements approuvés : {{ $eventCount }}">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="veille-metric-icon"><i class="icon-base bx bx-file-find" aria-hidden="true"></i></span>
              <div><span class="veille-metric-label">Événements suivis</span><strong>{{ number_format($eventCount, 0, ',', ' ') }}</strong><small>{{ $periodLabel }}</small></div>
            </div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="card veille-metric-card h-100" aria-label="Nombre de provinces renseignées : {{ $provinceCount }}">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="veille-metric-icon veille-metric-orange"><i class="icon-base bx bx-map-pin" aria-hidden="true"></i></span>
              <div><span class="veille-metric-label">Zones concernées</span><strong>{{ number_format($provinceCount, 0, ',', ' ') }}</strong><small>Provinces renseignées</small></div>
            </div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="card veille-metric-card h-100" aria-label="Nombre d’événements avec décès signalés : {{ $eventsWithDeaths }}">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="veille-metric-icon veille-metric-warning"><i class="icon-base bx bx-error" aria-hidden="true"></i></span>
              <div><span class="veille-metric-label">Événements avec décès</span><strong>{{ number_format($eventsWithDeaths, 0, ',', ' ') }}</strong><small>{{ number_format($deathsTotal, 0, ',', ' ') }} décès signalés</small></div>
            </div>
          </article>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
          <article class="card veille-metric-card h-100" aria-label="Nombre de bulletins de veille publiés : {{ $bulletinCount }}">
            <div class="card-body d-flex align-items-center gap-3">
              <span class="veille-metric-icon veille-metric-info"><i class="icon-base bx bx-file" aria-hidden="true"></i></span>
              <div><span class="veille-metric-label">Bulletins publiés</span><strong>{{ number_format($bulletinCount, 0, ',', ' ') }}</strong><small>Catégorie Veille</small></div>
            </div>
          </article>
        </div>
      </section>

      <section class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
          <article id="carte" class="card veille-panel h-100">
            <div class="card-header veille-panel-heading">
              <span class="veille-section-icon" aria-hidden="true"><i class="icon-base bx bx-map-alt"></i></span>
              <div>
                <h2 class="card-title h5 mb-1">Situation territoriale</h2>
                <p class="card-subtitle text-muted mb-0">Répartition des événements suivis · {{ $periodLabel }}</p>
              </div>
            </div>
            <div class="card-body">
              <div id="synexVeilleMap" class="veille-map" role="img" aria-label="Carte du Tchad avec répartition des événements par province"></div>
              <div id="synexVeilleMapStatus" class="small text-muted mt-2" role="status" aria-live="polite">Chargement de la carte locale du Tchad…</div>
              <ul class="veille-map-legend mt-3 mb-0" aria-label="Légende du nombre d’événements par province">
                <li><span class="veille-legend-swatch veille-level-high"></span>50 et plus</li>
                <li><span class="veille-legend-swatch veille-level-medium"></span>20–49</li>
                <li><span class="veille-legend-swatch veille-level-low"></span>10–19</li>
                <li><span class="veille-legend-swatch veille-level-minimal"></span>1–9</li>
                <li><span class="veille-legend-swatch veille-level-none"></span>0</li>
              </ul>
              <p class="veille-map-attribution small text-muted mb-0 mt-2">Limites administratives : GeoBoundaries (OpenStreetMap/Wambacher, ODbL 1.0). Les repères sont issus des événements approuvés et géolocalisés.</p>
            </div>
          </article>
        </div>

        <div class="col-12 col-lg-6">
          <article id="evenements-recents" class="card veille-panel h-100">
            <div class="card-header veille-panel-heading">
              <span class="veille-section-icon veille-icon-danger" aria-hidden="true"><i class="icon-base bx bx-bell"></i></span>
              <div>
                <h2 class="card-title h5 mb-1">Derniers événements validés</h2>
                <p class="card-subtitle text-muted mb-0">Signalements correspondant aux filtres sélectionnés</p>
              </div>
            </div>
            <div class="card-body">
              @forelse ($recentEvents as $event)
                <article class="veille-event-item">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div class="min-w-0">
                      <h3 class="h6 mb-1">{{ $event->event_type ?: 'Type non renseigné' }}</h3>
                      <p class="small text-muted mb-2">
                        <i class="icon-base bx bx-map-pin me-1" aria-hidden="true"></i>
                        {{ collect([$event->locality, $event->province])->filter()->join(', ') ?: 'Lieu non renseigné' }}
                      </p>
                    </div>
                    <span class="badge bg-label-primary">{{ $event->event_date_raw ?: ($event->event_year ?: 'Date non renseignée') }}</span>
                  </div>
                  <div class="d-flex flex-wrap gap-2">
                    @if ($event->deaths !== null)<span class="veille-impact"><i class="icon-base bx bx-heart me-1" aria-hidden="true"></i>{{ number_format($event->deaths, 0, ',', ' ') }} décès</span>@endif
                    @if ($event->injuries !== null)<span class="veille-impact"><i class="icon-base bx bx-first-aid me-1" aria-hidden="true"></i>{{ number_format($event->injuries, 0, ',', ' ') }} blessés</span>@endif
                  </div>
                </article>
              @empty
                <div class="veille-empty-state">
                  <i class="icon-base bx bx-info-circle" aria-hidden="true"></i>
                  <p class="mb-0">Aucun événement approuvé ne correspond à ces filtres.</p>
                </div>
              @endforelse
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
              <a class="veille-link" href="{{ $dashboardTarget }}">
                Explorer les événements <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i>
              </a>
            </div>
          </article>
        </div>
      </section>

      <section id="tendances" class="row g-3 mb-4" aria-label="Tendances des événements">
        <div class="col-12 col-xl-7">
          <article class="card veille-panel h-100">
            <div class="card-header veille-panel-heading">
              <span class="veille-section-icon" aria-hidden="true"><i class="icon-base bx bx-line-chart"></i></span>
              <div>
                <h2 class="card-title h5 mb-1">Évolution des événements</h2>
                <p class="card-subtitle text-muted mb-0">Nombre d’événements suivis · {{ $periodLabel }}</p>
              </div>
            </div>
            <div class="card-body">
              @if ($trend->isNotEmpty())
                <div id="synexVeilleTrendChart" class="veille-chart" role="img" aria-label="Courbe des événements sur la période sélectionnée"></div>
              @else
                <div class="veille-empty-state"><i class="icon-base bx bx-line-chart" aria-hidden="true"></i><p class="mb-0">Aucune donnée à afficher pour cette sélection.</p></div>
              @endif
            </div>
          </article>
        </div>
        <div class="col-12 col-xl-5" id="publications">
          <article class="card veille-panel h-100">
            <div class="card-header veille-panel-heading">
              <span class="veille-section-icon" aria-hidden="true"><i class="icon-base bx bx-file"></i></span>
              <div>
                <h2 class="card-title h5 mb-1">Derniers bulletins de veille</h2>
                <p class="card-subtitle text-muted mb-0">Publications SYNEX publiées dans la catégorie Veille</p>
              </div>
            </div>
            <div class="card-body">
              @forelse ($bulletins as $bulletin)
                <article class="veille-bulletin-item d-flex align-items-start gap-3">
                  <span class="veille-bulletin-icon" aria-hidden="true"><i class="icon-base bx bx-file"></i></span>
                  <div class="min-w-0 flex-grow-1">
                    <a class="veille-bulletin-title" href="{{ route('program-news.show', $bulletin) }}">{{ $bulletin->title }}</a>
                    <p class="small text-muted mb-0">{{ $bulletin->summary }}</p>
                    <time class="small text-muted" datetime="{{ $bulletin->published_at->toDateString() }}">{{ $bulletin->published_at->locale('fr')->isoFormat('D MMM YYYY') }}</time>
                  </div>
                </article>
              @empty
                <div class="veille-empty-state">
                  <i class="icon-base bx bx-file" aria-hidden="true"></i>
                  <p class="mb-0">Aucun bulletin de veille publié pour le moment.</p>
                </div>
              @endforelse
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
              <a class="veille-link" href="{{ route('program-news.index', ['category' => 'veille']) }}">Voir les publications de veille <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i></a>
            </div>
          </article>
        </div>
      </section>

      <section id="sources" class="card veille-panel mb-4">
        <div class="card-header veille-panel-heading">
          <span class="veille-section-icon" aria-hidden="true"><i class="icon-base bx bx-layer"></i></span>
          <div>
            <h2 class="card-title h5 mb-1">Sources consultables</h2>
            <p class="card-subtitle text-muted mb-0">Liens référencés dans les événements approuvés de la période sélectionnée</p>
          </div>
        </div>
        <div class="card-body">
          @forelse ($sources as $source)
            <a class="veille-source-link" href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer">
              <i class="icon-base bx bx-link-external me-2" aria-hidden="true"></i>{{ $source['label'] }}
            </a>
          @empty
            <p class="text-muted small mb-0">Aucun lien source n’est renseigné pour les événements de cette période.</p>
          @endforelse
        </div>
      </section>

      @if ($features?->is_active)
        <section id="lectures" class="card shadow-sm mb-4">
          <div class="card-header d-flex align-items-center gap-3 pb-2">
            <span class="veille-section-icon" aria-hidden="true"><i class="icon-base bx bx-layer icon-md"></i></span>
            <div class="veille-section-heading">
              <h2 class="card-title h5 mb-1">{{ $features->title }}</h2>
              @if ($features->description)<p class="card-subtitle text-muted mb-0">{{ $features->description }}</p>@endif
            </div>
          </div>
          <div class="card-body pt-3">
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-5 g-3">
              @foreach ($features->cards as $card)
                @php $iconColor = $iconColors[$loop->index % count($iconColors)]; @endphp
                <div class="col">
                  <article class="card veille-item h-100">
                    <div class="card-body">
                      <span class="veille-icon icon-{{ $iconColor }} mb-3" aria-hidden="true">
                        @if ($card->image_path)
                          <img src="{{ asset('storage/'.$card->image_path) }}" alt="">
                        @else
                          <i class="icon-base bx {{ $card->icon ?: 'bx-book-open' }}"></i>
                        @endif
                      </span>
                      <h3 class="card-title h6">{{ $card->title }}</h3>
                      @if ($card->description)<p class="card-text small mb-0">{{ $card->description }}</p>@endif
                    </div>
                    <div class="card-footer bg-transparent border-0">
                      <a class="veille-link" href="{{ $dashboardTarget }}" aria-label="En savoir plus : {{ $card->title }}">
                        <i class="icon-base bx bx-right-arrow-alt me-1" aria-hidden="true"></i>En savoir plus
                      </a>
                    </div>
                  </article>
                </div>
              @endforeach
            </div>
          </div>
        </section>
      @endif

      <section id="calculateur" class="row g-3 align-items-stretch" aria-labelledby="death-rate-title">
        <div class="col-12 col-lg-8">
          <div class="card shadow-sm h-100">
            <div class="card-header d-flex align-items-center gap-3">
              <span class="veille-section-icon" aria-hidden="true"><i class="icon-base bx bx-bar-chart-alt-2 icon-md"></i></span>
              <div class="veille-section-heading">
                <h2 id="death-rate-title" class="card-title h5 mb-1">Décès pour 100 000 habitants</h2>
                <p class="card-subtitle text-muted mb-0">Indicateur de gravité permettant de comparer les territoires.</p>
              </div>
            </div>
            @if ($severity?->is_active)
              <div class="card-body">
                <div class="row g-3">
                  @foreach ($severity->cards as $card)
                    @php
                      $level = ['low', 'moderate', 'high'][$loop->index] ?? '';
                      $levelIcon = $levelIcons[$loop->index] ?? 'bx-info-circle';
                    @endphp
                    <div class="col-12 col-md-4">
                      <article class="card gravity-card {{ ['gravity-low', 'gravity-medium', 'gravity-high'][$loop->index] ?? '' }}" data-death-level="{{ $level }}">
                        <div class="card-body p-3">
                          @if ($card->value)<div class="gravity-value">{{ $card->value }}</div>@endif
                          <h3 class="card-title h6 d-flex align-items-center gap-2 mb-2">
                            <i class="icon-base bx {{ $levelIcon }} icon-md" aria-hidden="true"></i>{{ $card->title }}
                          </h3>
                          @if ($card->description)<p class="card-text small mb-0">{{ $card->description }}</p>@endif
                        </div>
                      </article>
                    </div>
                  @endforeach
                </div>
                @if ($severity->secondary_text)
                  <div class="alert alert-primary d-flex align-items-start gap-2 mt-3 mb-0" role="note">
                    <i class="icon-base bx bx-info-circle mt-1" aria-hidden="true"></i>
                    <span class="small">{{ $severity->secondary_text }}</span>
                  </div>
                @endif
              </div>
            @endif
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <section class="card calculator-card h-100" aria-labelledby="calculator-title">
            <div class="card-header calculator-header d-flex align-items-center gap-3">
              <i class="icon-base bx bx-calculator icon-lg" aria-hidden="true"></i>
              <h2 id="calculator-title" class="card-title h5 text-white mb-0">Calculer un indice</h2>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label for="recorded-deaths" class="form-label">Décès enregistrés <span class="text-danger">*</span></label>
                <input id="recorded-deaths" class="form-control" type="number" min="0" step="1" inputmode="numeric" placeholder="12" required>
              </div>
              <div class="mb-4">
                <label for="territory-population" class="form-label">Population du territoire <span class="text-danger">*</span></label>
                <input id="territory-population" class="form-control" type="number" min="1" step="1" inputmode="numeric" placeholder="180000" required>
              </div>
              <div id="death-rate-result" class="calculator-result bg-label-primary" aria-live="polite">
                <div class="d-flex flex-wrap align-items-baseline gap-2 mb-2">
                  <strong id="death-rate-value" class="fs-2">—</strong>
                  <span id="death-rate-description">décès / 100 000 habitants</span>
                </div>
                <span id="death-rate-severity" class="badge bg-label-secondary">Saisissez les deux valeurs</span>
              </div>
            </div>
          </section>
        </div>
      </section>
    </main>
  </div>

  <script>
    window.synexVeilleData = {{ \Illuminate\Support\Js::from($veilleData) }};
  </script>
@endsection
