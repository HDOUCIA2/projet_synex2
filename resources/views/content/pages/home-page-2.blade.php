@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', 'Home page 2 — Suivi & Analyse des Conflits')

@section('page-style')
  @vite('resources/assets/css/home-page-2.css')
@endsection

@section('page-script')
  @vite('resources/assets/js/home-page-2.js')
@endsection

@section('content')
<div class="synex-home2">
  @php
    $trendChart = ['left' => 64, 'right' => 620, 'top' => 20, 'bottom' => 190];
    $trendMaximum = max(1, (int) $eventsByYear->max('total'));
    $trendPoints = [];
    $trendCount = $eventsByYear->count();

    foreach ($eventsByYear as $index => $year) {
        $x = $trendCount === 1
            ? ($trendChart['left'] + $trendChart['right']) / 2
            : $trendChart['left'] + ($index / ($trendCount - 1)) * ($trendChart['right'] - $trendChart['left']);
        $y = $trendChart['bottom'] - (((int) $year->total / $trendMaximum) * ($trendChart['bottom'] - $trendChart['top']));
        $trendPoints[] = [
            'x' => round($x, 2),
            'y' => round($y, 2),
            'year' => (int) $year->event_year,
            'total' => (int) $year->total,
        ];
    }

    $trendLinePath = '';
    foreach ($trendPoints as $index => $point) {
        if ($index === 0) {
            $trendLinePath = 'M '.$point['x'].' '.$point['y'];
            continue;
        }

        $previous = $trendPoints[$index - 1];
        $controlOffset = ($point['x'] - $previous['x']) * .45;
        $trendLinePath .= ' C '.round($previous['x'] + $controlOffset, 2).' '.$previous['y']
            .' '.round($point['x'] - $controlOffset, 2).' '.$point['y']
            .' '.$point['x'].' '.$point['y'];
    }

    $trendAreaPath = $trendLinePath;
    if (count($trendPoints) > 1) {
        $lastTrendPoint = $trendPoints[array_key_last($trendPoints)];
        $firstTrendPoint = $trendPoints[0];
        $trendAreaPath .= ' L '.$lastTrendPoint['x'].' '.$trendChart['bottom']
            .' L '.$firstTrendPoint['x'].' '.$trendChart['bottom'].' Z';
    }

    $sparkPoints = [];
    foreach ($trendPoints as $index => $point) {
        $sparkPoints[] = [
            'x' => round($trendCount === 1 ? 160 : 10 + ($index / ($trendCount - 1)) * 300, 2),
            'y' => round(64 - ($point['total'] / $trendMaximum) * 48, 2),
        ];
    }

    $sparkLinePath = '';
    foreach ($sparkPoints as $index => $point) {
        if ($index === 0) {
            $sparkLinePath = 'M '.$point['x'].' '.$point['y'];
            continue;
        }

        $previous = $sparkPoints[$index - 1];
        $controlOffset = ($point['x'] - $previous['x']) * .45;
        $sparkLinePath .= ' C '.round($previous['x'] + $controlOffset, 2).' '.$previous['y']
            .' '.round($point['x'] - $controlOffset, 2).' '.$point['y']
            .' '.$point['x'].' '.$point['y'];
    }

    $sparkAreaPath = $sparkLinePath;
    if (count($sparkPoints) > 1) {
        $lastSparkPoint = $sparkPoints[array_key_last($sparkPoints)];
        $firstSparkPoint = $sparkPoints[0];
        $sparkAreaPath .= ' L '.$lastSparkPoint['x'].' 72 L '.$firstSparkPoint['x'].' 72 Z';
    }
  @endphp

  @include('content.pages._public-navbar', ['activePage' => 'home'])

  <main>
    <section class="synex-home2-hero">
      <div class="container-xxl px-4">
        <div class="row align-items-center g-4">
          <div class="col-xl-6">
            <span class="synex-home2-eyebrow" aria-hidden="true"><span></span></span>
            <h1>Une plateforme de suivi, d’analyse et de veille des conflits</h1>
            <p class="synex-home2-lead">
              Collectez, analysez et visualisez les incidents, les acteurs et les dynamiques territoriales
              pour une meilleure anticipation des risques et un appui à la prise de décision.
            </p>
            <div class="d-flex flex-wrap gap-3 mt-4">
              <a href="{{ route('dashboard-analytics') }}" class="btn btn-primary btn-lg">
                <i class="icon-base bx bx-bar-chart-alt-2 me-1" aria-hidden="true"></i>
                Accéder au tableau de bord
              </a>
              <a href="#synex-home2-features" class="btn synex-home2-btn-ghost btn-lg">
                <i class="icon-base bx bx-play-circle me-1" aria-hidden="true"></i>
                Découvrir SYNEX
              </a>
            </div>
          </div>

          <div class="col-sm-5 col-xl-2">
            <div class="synex-home2-hero-map-wrap">
              <div id="synexHome2HeroMap" class="synex-home2-hero-map" aria-hidden="true"></div>
              <span class="synex-home2-hero-map-label">{{ number_format(count($mapPoints), 0, ',', ' ') }} points GPS</span>
            </div>
          </div>

          <div class="col-sm-7 col-xl-4">
            <aside class="synex-home2-overview" aria-label="Résumé des données publiées">
              <div class="synex-home2-overview-heading">
                <div class="synex-home2-overview-country">
                  <span class="synex-home2-flag" role="img" aria-label="Drapeau du Tchad"></span>
                  <h2>Tchad</h2>
                </div>
                <span class="synex-home2-live"><span></span>Données validées</span>
              </div>
              <div class="synex-home2-overview-total">
                <strong>{{ number_format($eventCount, 0, ',', ' ') }}</strong>
                <span>{{ $eventCount === 1 ? 'incident approuvé' : 'incidents approuvés' }}</span>
              </div>
              @if (count($sparkPoints) > 0)
                <svg class="synex-home2-overview-sparkline" viewBox="0 0 320 80" role="img" aria-label="Tendance annuelle des incidents approuvés">
                  <title>Tendance annuelle des incidents approuvés</title>
                  <defs>
                    <linearGradient id="synexHome2OverviewArea" x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0%" stop-color="#D2AD36" stop-opacity=".38"></stop>
                      <stop offset="100%" stop-color="#D2AD36" stop-opacity=".04"></stop>
                    </linearGradient>
                  </defs>
                  @if (count($sparkPoints) > 1)
                    <path class="synex-home2-overview-spark-area" d="{{ $sparkAreaPath }}"></path>
                  @endif
                  <path class="synex-home2-overview-spark-line" d="{{ $sparkLinePath }}"></path>
                </svg>
              @else
                <p class="synex-home2-overview-empty">La tendance apparaîtra dès que des années seront renseignées.</p>
              @endif
              <div class="synex-home2-overview-divider"></div>
              <p class="synex-home2-overview-types-title">Incidents par type</p>
              @forelse ($heroEventTypes as $index => $eventType)
                <div class="synex-home2-overview-row">
                  <span class="synex-home2-overview-type-label" title="{{ $eventType['label'] }}">
                    <i class="synex-home2-dot" style="--synex-home2-type-color: {{ $eventTypeColors[$eventType['label']] ?? '#AF8807' }}"></i>
                    {{ $eventType['label'] }}
                  </span>
                  <strong>{{ number_format($eventType['total'], 0, ',', ' ') }}</strong>
                </div>
              @empty
                <p class="synex-home2-overview-empty">Aucun type d’incident renseigné.</p>
              @endforelse
              <p class="synex-home2-overview-note mb-0">Données issues des événements approuvés dans SYNEX.</p>
            </aside>
          </div>
        </div>
      </div>
    </section>

    <section id="synex-home2-features" class="container-xxl px-4 synex-home2-feature-wrap">
      <div class="row g-0 synex-home2-feature-bar">
        <div class="col-md-4">
          <a class="synex-home2-feature" href="{{ route('dashboard-analytics') }}">
            <span class="synex-home2-feature-icon"><i class="icon-base bx bx-bar-chart-alt-2" aria-hidden="true"></i></span>
            <span><strong>Analyses des conflits</strong><small>Explorez les événements et les tendances.</small></span>
            <i class="icon-base bx bx-right-arrow-alt synex-home2-feature-arrow" aria-hidden="true"></i>
          </a>
        </div>
        <div class="col-md-4">
          <a class="synex-home2-feature" href="{{ route('veille') }}">
            <span class="synex-home2-feature-icon"><i class="icon-base bx bx-radar" aria-hidden="true"></i></span>
            <span><strong>Veille territoriale</strong><small>Consultez les lectures et repères du terrain.</small></span>
            <i class="icon-base bx bx-right-arrow-alt synex-home2-feature-arrow" aria-hidden="true"></i>
          </a>
        </div>
        <div class="col-md-4">
          <a class="synex-home2-feature" href="{{ route('program-news.index') }}">
            <span class="synex-home2-feature-icon"><i class="icon-base bx bx-news" aria-hidden="true"></i></span>
            <span><strong>Actualités du programme</strong><small>Retrouvez les publications récentes.</small></span>
            <i class="icon-base bx bx-right-arrow-alt synex-home2-feature-arrow" aria-hidden="true"></i>
          </a>
        </div>
      </div>
    </section>

    <section class="container-xxl px-4 synex-home2-section">
      <div class="synex-home2-section-heading">
        <span></span>
        <div>
          <p class="synex-home2-kicker mb-1">
            <i class="icon-base bx bx-bar-chart-alt-2" aria-hidden="true"></i>
            REPÈRES CLÉS
          </p>
          <h2>Les données disponibles en un regard</h2>
        </div>
      </div>
      <div class="row row-cols-2 row-cols-xl-4 g-3">
        <div class="col">
          <article class="synex-home2-stat">
            <span class="synex-home2-stat-icon"><i class="icon-base bx bx-calendar-event" aria-hidden="true"></i></span>
            <strong aria-label="Nombre d’événements validés : {{ number_format($eventCount, 0, ',', ' ') }}">{{ number_format($eventCount, 0, ',', ' ') }}</strong>
            <span>Événements validés</span>
          </article>
        </div>
        <div class="col">
          <article class="synex-home2-stat">
            <span class="synex-home2-stat-icon"><i class="icon-base bx bx-map-alt" aria-hidden="true"></i></span>
            <strong aria-label="Nombre de provinces renseignées : {{ number_format($provinceCount, 0, ',', ' ') }}">{{ number_format($provinceCount, 0, ',', ' ') }}</strong>
            <span>Provinces renseignées</span>
          </article>
        </div>
        <div class="col">
          <article class="synex-home2-stat">
            <span class="synex-home2-stat-icon synex-home2-stat-danger"><i class="icon-base bx bx-heart" aria-hidden="true"></i></span>
            <strong aria-label="Nombre de décès signalés : {{ number_format($deathCount, 0, ',', ' ') }}">{{ number_format($deathCount, 0, ',', ' ') }}</strong>
            <span>Décès signalés</span>
          </article>
        </div>
        <div class="col">
          <article class="synex-home2-stat">
            <span class="synex-home2-stat-icon"><i class="icon-base bx bx-news" aria-hidden="true"></i></span>
            <strong aria-label="Nombre d’actualités publiées : {{ number_format($newsCount, 0, ',', ' ') }}">{{ number_format($newsCount, 0, ',', ' ') }}</strong>
            <span>Actualités publiées</span>
          </article>
        </div>
      </div>
    </section>

    <section class="container-xxl px-4 synex-home2-section">
      <div class="row g-4">
        <div class="col-lg-7">
          <article class="synex-home2-panel synex-home2-map-panel h-100">
            <div class="synex-home2-panel-heading synex-home2-map-heading">
              <span class="synex-home2-panel-icon"><i class="icon-base bx bx-map" aria-hidden="true"></i></span>
              <div>
                <h2>Carte des incidents au Tchad</h2>
                <p>Zones administratives et événements selon les coordonnées enregistrées.</p>
              </div>
              <span id="home2-map-point-count" class="badge bg-label-primary ms-auto">{{ number_format(count($mapPoints), 0, ',', ' ') }} points GPS</span>
            </div>
            <div id="synexHome2Map" class="synex-home2-map" role="region" aria-label="Carte interactive des provinces du Tchad"></div>
            <p id="synexHome2MapStatus" class="synex-home2-map-status" role="status">Chargement de la carte du Tchad…</p>
            <div class="synex-home2-map-legend" aria-label="Légende de la carte">
              <strong>Événements approuvés par province</strong>
              <span><i class="synex-home2-legend-chip synex-home2-legend-50"></i>50 et plus</span>
              <span><i class="synex-home2-legend-chip synex-home2-legend-20"></i>20–49</span>
              <span><i class="synex-home2-legend-chip synex-home2-legend-10"></i>10–19</span>
              <span><i class="synex-home2-legend-chip synex-home2-legend-1"></i>1–9</span>
              <span><i class="synex-home2-legend-chip synex-home2-legend-0"></i>Aucun</span>
              @if ($heroEventTypes->isNotEmpty())
                <strong>Points GPS par type d’incident</strong>
                @foreach ($heroEventTypes as $eventType)
                  <span>
                    <i class="synex-home2-type-chip" style="--synex-home2-type-color: {{ $eventTypeColors[$eventType['label']] ?? '#AF8807' }}"></i>
                    {{ $eventType['label'] }}
                  </span>
                @endforeach
              @endif
            </div>
            <small class="synex-home2-map-source">Limites administratives : GeoBoundaries, source OSM/Wambacher (ODbL 1.0).</small>
          </article>
        </div>
        <div class="col-lg-5">
          <article class="synex-home2-panel h-100">
            <div class="synex-home2-panel-heading">
              <span class="synex-home2-panel-icon"><i class="icon-base bx bx-line-chart" aria-hidden="true"></i></span>
              <div>
                <h2>Tendances récentes</h2>
                <p>Événements approuvés, par année</p>
              </div>
            </div>
            @if ($eventsByYear->isNotEmpty())
              <svg class="synex-home2-trend-chart" viewBox="0 0 640 240" role="img" aria-label="Évolution du nombre d’événements approuvés par année">
                <title>Événements approuvés par année</title>
                <desc>Courbe annuelle basée sur les événements SYNEX approuvés.</desc>
                <defs>
                  <linearGradient id="synexHome2TrendArea" x1="0" x2="0" y1="0" y2="1">
                    <stop offset="0%" stop-color="#D2AD36" stop-opacity=".28"></stop>
                    <stop offset="100%" stop-color="#D2AD36" stop-opacity=".02"></stop>
                  </linearGradient>
                </defs>
                @for ($gridLine = 0; $gridLine <= 3; $gridLine++)
                  @php
                    $gridY = $trendChart['top'] + ($gridLine / 3) * ($trendChart['bottom'] - $trendChart['top']);
                    $gridValue = (int) round($trendMaximum * (3 - $gridLine) / 3);
                  @endphp
                  <line class="synex-home2-trend-grid" x1="{{ $trendChart['left'] }}" x2="{{ $trendChart['right'] }}" y1="{{ $gridY }}" y2="{{ $gridY }}"></line>
                  <text class="synex-home2-trend-axis-label" x="54" y="{{ $gridY + 4 }}" text-anchor="end">{{ number_format($gridValue, 0, ',', ' ') }}</text>
                @endfor
                @if (count($trendPoints) > 1)
                  <path class="synex-home2-trend-area" d="{{ $trendAreaPath }}"></path>
                @endif
                <path class="synex-home2-trend-line" d="{{ $trendLinePath }}"></path>
                @foreach ($trendPoints as $point)
                  <circle class="synex-home2-trend-point" cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4.5">
                    <title>{{ number_format($point['total'], 0, ',', ' ') }} événements en {{ $point['year'] }}</title>
                  </circle>
                  <text class="synex-home2-trend-year" x="{{ $point['x'] }}" y="220" text-anchor="middle">{{ $point['year'] }}</text>
                @endforeach
              </svg>
            @else
              <p class="text-muted mb-0 py-4">Les tendances apparaîtront dès que des événements approuvés auront été enregistrés.</p>
            @endif
            <a class="synex-home2-text-link" href="{{ route('dashboard-analytics') }}">
              Explorer les analyses
              <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i>
            </a>
          </article>
        </div>
      </div>
    </section>
    <script>
      window.synexHome2MapData = @js([
        'points' => $mapPoints,
        'provinceEventCounts' => $provinceEventCounts,
        'provinceEventTypeCounts' => $provinceEventTypeCounts,
        'eventTypeColors' => $eventTypeColors,
        'geoJsonUrl' => asset('assets/geo/chad-adm1.geojson'),
      ]);
    </script>

    <section class="container-xxl px-4 synex-home2-section synex-home2-news-section">
      <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div class="synex-home2-section-heading mb-0">
          <span></span>
          <div>
            <p class="synex-home2-kicker mb-1">À LA UNE</p>
            <h2>Actualités du programme</h2>
            <p class="text-muted mb-0">Les dernières publications partagées sur SYNEX.</p>
          </div>
        </div>
        <a href="{{ route('program-news.index') }}" class="synex-home2-text-link">
          Toutes les actualités
          <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i>
        </a>
      </div>

      @if ($newsItems->isNotEmpty())
        <div class="synex-home2-carousel" data-home2-carousel>
          <button class="synex-home2-carousel-button" type="button" aria-label="Actualités précédentes" data-home2-prev>
            <i class="icon-base bx bx-chevron-left" aria-hidden="true"></i>
          </button>
          <div class="synex-home2-carousel-viewport">
            <div class="synex-home2-carousel-track" data-home2-track>
              @foreach ($newsItems as $news)
                <article class="synex-home2-news-card">
                  <a class="synex-home2-news-image" href="{{ route('program-news.show', $news) }}">
                    @if ($news->image_path)
                      <img src="{{ asset('storage/'.$news->image_path) }}" alt="" loading="lazy">
                    @else
                      <span class="synex-home2-news-placeholder" aria-hidden="true"><i class="icon-base bx bx-news"></i></span>
                    @endif
                    <span class="synex-home2-news-label">Actualité</span>
                  </a>
                  <div class="synex-home2-news-body">
                    <p class="synex-home2-news-date">
                      <i class="icon-base bx bx-calendar me-1" aria-hidden="true"></i>
                      <time datetime="{{ $news->published_at->toDateString() }}">{{ $news->published_at->format('d/m/Y') }}</time>
                    </p>
                    <h3>{{ $news->title }}</h3>
                    <p class="synex-home2-news-summary">{{ $news->summary }}</p>
                    <a href="{{ route('program-news.show', $news) }}" class="synex-home2-text-link">Lire l’actualité <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i></a>
                  </div>
                </article>
              @endforeach
            </div>
          </div>
          <button class="synex-home2-carousel-button" type="button" aria-label="Actualités suivantes" data-home2-next>
            <i class="icon-base bx bx-chevron-right" aria-hidden="true"></i>
          </button>
          <div class="synex-home2-carousel-dots" data-home2-dots aria-label="Pagination des actualités"></div>
        </div>
      @else
        <div class="synex-home2-empty-news">
          <i class="icon-base bx bx-news" aria-hidden="true"></i>
          <p class="mb-0">Les actualités publiées apparaîtront ici.</p>
        </div>
      @endif
    </section>
  </main>

</div>
@endsection
