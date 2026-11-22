@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', 'Prévention | SYNEX')

@section('page-style')
  @vite('resources/assets/css/prevention.css')
@endsection

@section('content')
@php
  $chartStops = [];
  $chartPosition = 0;
  foreach ($actionsByType as $action) {
    $nextPosition = $chartPosition + ($actionsTotal > 0 ? ($action['count'] / $actionsTotal) * 100 : 0);
    $chartStops[] = $action['color'].' '.$chartPosition.'% '.$nextPosition.'%';
    $chartPosition = $nextPosition;
  }
  $chartBackground = $actionsTotal > 0 ? 'conic-gradient('.implode(', ', $chartStops).')' : '#f1eee7';
@endphp
<div class="synex-prevention-page">
  @include('content.pages._public-navbar', ['activePage' => 'prevention'])

  <header class="prevention-hero">
    <div class="container-xxl px-4">
      <div class="prevention-hero-copy">
        <span class="prevention-hero-mark" aria-hidden="true"></span>
        <div>
          <span class="prevention-eyebrow"><i class="icon-base bx bx-shield-quarter" aria-hidden="true"></i> Prévention</span>
          <h1>{{ $prevention['hero_title'] }}</h1>
          <p>{{ $prevention['hero_description'] }}</p>
        </div>
        <div class="prevention-hero-values" aria-label="Valeurs de SYNEX">
          <span><i class="icon-base bx bx-group" aria-hidden="true"></i> Dialogue</span>
          <span><i class="icon-base bx bx-conversation" aria-hidden="true"></i> Cohésion</span>
          <span><i class="icon-base bx bxs-heart" aria-hidden="true"></i> Paix</span>
        </div>
      </div>
    </div>
  </header>

  <main class="container-xxl prevention-content px-3 py-3">
    <nav aria-label="Fil d’Ariane" class="mb-3">
      <ol class="breadcrumb prevention-breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="icon-base bx bx-home-alt-2 me-1" aria-hidden="true"></i>Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Prévention</li>
      </ol>
    </nav>

    <section class="row g-3 mb-4" aria-label="Indicateurs des actions documentées">
      @php
        $kpis = [
          ['key' => 'campaigns', 'title' => 'Campagnes de prévention', 'description' => 'Actions de sensibilisation et d’information', 'icon' => 'bxs-megaphone'],
          ['key' => 'mediations', 'title' => 'Médiations', 'description' => 'Démarches de dialogue documentées', 'icon' => 'bx-group'],
          ['key' => 'arbitrations', 'title' => 'Arbitrages', 'description' => 'Règlements de différends recensés', 'icon' => 'bx-briefcase'],
          ['key' => 'peace_agreements', 'title' => 'Accords de paix', 'description' => 'Accords locaux enregistrés', 'icon' => 'bxs-heart'],
        ];
      @endphp
      @foreach ($kpis as $kpi)
        <div class="col-6 col-xl-3">
          <article class="card prevention-kpi h-100">
            <div class="card-body">
              <span class="prevention-kpi-icon"><i class="icon-base bx {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
              <div class="prevention-kpi-value">{{ number_format((int) $metrics[$kpi['key']], 0, ',', ' ') }}</div>
              <h2>{{ $kpi['title'] }}</h2>
              <p>{{ $kpi['description'] }}</p>
            </div>
          </article>
        </div>
      @endforeach
    </section>

    <section class="row g-3 mb-3">
      <div class="col-xl-7">
        <section class="card prevention-panel h-100" aria-labelledby="prevention-axes-title">
          <div class="prevention-panel-heading">
            <span class="prevention-heading-icon"><i class="icon-base bx bx-target-lock" aria-hidden="true"></i></span>
            <div>
              <h2 id="prevention-axes-title">Nos axes de prévention</h2>
              <p>Des leviers concrets pour agir en amont des tensions</p>
            </div>
          </div>
          <div class="prevention-axis-grid">
            @foreach ($prevention['axes'] as $axis)
              <article class="prevention-axis-card">
                <div class="prevention-axis-top">
                  <span class="prevention-axis-icon"><i class="icon-base bx {{ $axis['icon'] }}" aria-hidden="true"></i></span>
                  <span class="prevention-axis-number">{{ $axis['number'] }}</span>
                </div>
                <h3>{{ $axis['title'] }}</h3>
                <p>{{ $axis['description'] }}</p>
                <span class="prevention-axis-tag">{{ $axis['tag'] }}</span>
              </article>
            @endforeach
          </div>
        </section>
      </div>

      <div class="col-xl-5">
        <section class="card prevention-panel h-100" aria-labelledby="prevention-chart-title">
          <div class="prevention-panel-heading">
            <span class="prevention-heading-icon"><i class="icon-base bx bx-pie-chart-alt-2" aria-hidden="true"></i></span>
            <div>
              <h2 id="prevention-chart-title">Répartition des actions</h2>
              <p>{{ number_format($actionsTotal, 0, ',', ' ') }} enregistrements documentés</p>
            </div>
          </div>
          <div class="prevention-chart-layout">
            <div class="prevention-chart-wrap">
              <div id="prevention-actions-chart" style="background: {{ $chartBackground }}" role="img" aria-label="Graphique de répartition des actions documentées"></div>
              <div class="prevention-chart-total" aria-hidden="true">
                <strong>{{ number_format($actionsTotal, 0, ',', ' ') }}</strong>
                <span>enregistrements</span>
              </div>
            </div>
            <div class="prevention-chart-legend">
              @foreach ($actionsByType as $action)
                <div class="prevention-legend-row">
                  <span class="prevention-legend-name"><i style="--legend-color: {{ $action['color'] }}"></i>{{ $action['label'] }}</span>
                  <strong>{{ number_format($action['count'], 0, ',', ' ') }}</strong>
                  <small>{{ $actionsTotal > 0 ? number_format(($action['count'] / $actionsTotal) * 100, 1, ',', ' ') : '0,0' }} %</small>
                </div>
              @endforeach
            </div>
          </div>
          <p class="prevention-data-note"><i class="icon-base bx bx-info-circle" aria-hidden="true"></i>
            Les chiffres couvrent les quatre sous-types présentés. Ils ne constituent pas un décompte d’initiatives uniques.
          </p>
        </section>
      </div>
    </section>

    <section class="row g-3 mb-3">
      <div class="col-xl-5">
        <section class="card prevention-panel prevention-territory-panel" aria-labelledby="prevention-territory-title">
          <div class="prevention-panel-heading">
            <span class="prevention-heading-icon"><i class="icon-base bx bx-map-alt" aria-hidden="true"></i></span>
            <div>
              <h2 id="prevention-territory-title">Couverture territoriale</h2>
              <p>Enregistrements documentés par province</p>
            </div>
          </div>
          <div class="prevention-territory-summary">
            <i class="icon-base bx bx-map-pin" aria-hidden="true"></i>
            <strong>{{ $provinceDistribution->where('count', '>', 0)->count() }}</strong>
            <span>provinces renseignées</span>
          </div>
          <div class="prevention-province-list">
            @foreach ($provinceDistribution as $province)
              <div class="prevention-province-row">
                <span>{{ $province['province'] }}</span>
                <div class="prevention-province-bar" role="img" aria-label="{{ $province['province'] }} : {{ $province['count'] }} enregistrements">
                  <i style="width: {{ $actionsTotal > 0 ? ($province['count'] / $actionsTotal) * 100 : 0 }}%"></i>
                </div>
                <strong>{{ number_format($province['count'], 0, ',', ' ') }}</strong>
                <small>{{ $actionsTotal > 0 ? number_format(($province['count'] / $actionsTotal) * 100, 1, ',', ' ') : '0,0' }} %</small>
              </div>
            @endforeach
          </div>
          <p class="prevention-data-note"><i class="icon-base bx bx-info-circle" aria-hidden="true"></i>
            Les provinces absentes de l’extraction ne sont pas assimilées à des territoires sans action.
          </p>
        </section>
      </div>

      <div class="col-xl-7">
        <section class="card prevention-panel h-100" aria-labelledby="prevention-initiatives-title">
          <div class="prevention-panel-heading prevention-panel-heading-between">
            <div class="d-flex align-items-center gap-3">
              <span class="prevention-heading-icon"><i class="icon-base bx bx-calendar-check" aria-hidden="true"></i></span>
              <div>
                <h2 id="prevention-initiatives-title">Initiatives récentes</h2>
                <p>Actions documentées et autorisées à la publication</p>
              </div>
            </div>
            <span class="prevention-period">{{ \Illuminate\Support\Carbon::parse($prevention['source']['from'])->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($prevention['source']['to'])->format('d/m/Y') }}</span>
          </div>
          <div class="prevention-initiative-list">
            @forelse ($initiatives as $initiative)
              @php
                $initiativeIcons = ['campaign' => 'bxs-megaphone', 'mediation' => 'bx-group', 'arbitration' => 'bx-briefcase', 'agreement' => 'bxs-heart'];
              @endphp
              <article class="prevention-initiative-row">
                <span class="prevention-initiative-icon"><i class="icon-base bx {{ $initiativeIcons[$initiative->type] }}" aria-hidden="true"></i></span>
                <div class="prevention-initiative-content">
                  <div class="prevention-initiative-meta">
                    <span class="prevention-type-pill">{{ \App\Models\PreventionInitiative::TYPES[$initiative->type] }}</span>
                    @if ($initiative->initiative_date)
                      <time datetime="{{ $initiative->initiative_date->toDateString() }}">{{ $initiative->initiative_date->locale('fr')->isoFormat('D MMM YYYY') }}</time>
                    @endif
                  </div>
                  <h3>{{ $initiative->title }}</h3>
                  @if ($initiative->location || $initiative->province)
                    <p><i class="icon-base bx bx-map-pin" aria-hidden="true"></i> {{ collect([$initiative->location, $initiative->province])->filter()->join(', ') }}</p>
                  @endif
                  @if ($initiative->source)
                    <small class="prevention-source">Source : {{ $initiative->source }}@if ($initiative->records > 1) · {{ $initiative->records }} enregistrements regroupés @endif</small>
                  @endif
                  @if ($initiative->note)
                    <small class="prevention-initiative-note">{{ $initiative->note }}</small>
                  @endif
                </div>
              </article>
            @empty
              <div class="prevention-empty-state">
                <i class="icon-base bx bx-calendar-x" aria-hidden="true"></i>
                <p>Aucune initiative n’est publiée pour le moment.</p>
              </div>
            @endforelse
          </div>
        </section>
      </div>
    </section>

    <section class="card prevention-panel mb-3" aria-labelledby="prevention-resources-title">
      <div class="prevention-panel-heading">
        <span class="prevention-heading-icon"><i class="icon-base bx bx-folder-open" aria-hidden="true"></i></span>
        <div>
          <h2 id="prevention-resources-title">Ressources et outils</h2>
          <p>Documents de sensibilisation validés pour diffusion</p>
        </div>
      </div>
      <div class="prevention-resource-grid">
        @forelse ($resources as $resource)
          <article class="prevention-resource-card">
            <span class="prevention-resource-icon"><i class="icon-base bx bx-file" aria-hidden="true"></i></span>
            <h3>{{ $resource['title'] }}</h3>
            <p>{{ $resource['description'] }}</p>
            <a href="{{ $resource['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
              <i class="icon-base bx bx-download me-1" aria-hidden="true"></i>Télécharger
            </a>
          </article>
        @empty
          <div class="prevention-resources-empty">
            <i class="icon-base bx bx-file-blank" aria-hidden="true"></i>
            <div><strong>Ressources en cours de préparation</strong>
              <p class="mb-0">Les documents apparaîtront ici après validation et autorisation de diffusion.</p>
            </div>
          </div>
        @endforelse
      </div>
    </section>

    <section class="prevention-approach-banner mb-3" aria-labelledby="prevention-approach-title">
      <span class="prevention-approach-icon"><i class="icon-base bx bx-conversation" aria-hidden="true"></i></span>
      <div>
        <h2 id="prevention-approach-title">{{ $prevention['approach_title'] }}</h2>
        <ul>
          @foreach ($prevention['approach_points'] as $point)
            <li><i class="icon-base bx bx-check-circle" aria-hidden="true"></i>{{ $point }}</li>
          @endforeach
        </ul>
      </div>
    </section>

    <aside class="prevention-methodology">
      <i class="icon-base bx bx-info-circle" aria-hidden="true"></i>
      <div>
        <strong>Couverture et limites des données</strong>
        <p>{{ $prevention['source']['note'] }}</p>
        <p class="mb-0">Période couverte : du {{ \Illuminate\Support\Carbon::parse($prevention['source']['from'])->format('d/m/Y') }} au {{ \Illuminate\Support\Carbon::parse($prevention['source']['to'])->format('d/m/Y') }}. Les totaux comptent des enregistrements et non nécessairement des projets uniques ou des bénéficiaires.</p>
      </div>
    </aside>
  </main>
</div>
@endsection
