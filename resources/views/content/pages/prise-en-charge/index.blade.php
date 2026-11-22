@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', $pageContent['hero_title'])

@section('page-style')
  @vite('resources/assets/css/prise-en-charge.css')
@endsection

@section('page-script')
  <script>
    window.synexCareMapData = {{ \Illuminate\Support\Js::from([
        'centers' => $mapCenters->map(fn ($center) => [
            'name' => $center->name,
            'type' => $center->structure_type,
            'province' => $center->province,
            'department' => $center->department,
            'locality' => $center->locality,
            'latitude' => (float) $center->latitude,
            'longitude' => (float) $center->longitude,
        ])->values(),
        'geoJsonUrl' => asset('assets/geo/chad-adm1.geojson'),
    ]) }};
  </script>
  @vite('resources/assets/js/prise-en-charge.js')
@endsection

@section('content')
<div class="synex-care-page">
  @include('content.pages._public-navbar', ['activePage' => 'care'])

  <header class="care-hero">
    <div class="container-xxl px-4">
      <div class="care-hero-copy">
        <span class="care-hero-mark" aria-hidden="true"></span>
        <div>
          <h1>{{ $pageContent['hero_title'] }}</h1>
          <p>{{ $pageContent['hero_description'] }}</p>
        </div>
      </div>
    </div>
  </header>

  <main class="container-xxl care-content px-2 py-2">
    <nav aria-label="Fil d’Ariane" class="mb-2">
      <ol class="breadcrumb breadcrumb-chevron mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Prise en charge</li>
      </ol>
    </nav>

    <div class="row g-2">
      <div class="col-md-8">
        <section class="card care-intro-card" aria-labelledby="care-intro-title">
          <div class="card-body">
            <div class="care-cism-visual" @if (!$cismImageUrl) aria-hidden="true" @endif>
              @if ($cismImageUrl)
                <img class="care-cism-photo" src="{{ $cismImageUrl }}" alt="Centre intégré de services multisectoriels">
              @else
                <span class="care-cism-building">
                  <span class="care-cism-sign"><strong>CISM</strong><small>Centre intégré de services<br>multisectoriels</small></span>
                  <span class="care-cism-door"></span>
                </span>
                <span class="care-cism-tree care-cism-tree-left"></span>
                <span class="care-cism-tree care-cism-tree-right"></span>
              @endif
            </div>
            <div class="care-intro-copy">
              <div class="care-title-inline">
                <i class="icon-base bx {{ $pageContent['section_icons']['cism'] }}" aria-hidden="true"></i>
                <h2 id="care-intro-title">{{ $pageContent['cism_title'] }}</h2>
              </div>
              <p>{{ $pageContent['cism_description'] }}</p>
              <ul class="care-benefits">
                @foreach ($pageContent['cism_benefits'] as $benefit)
                  <li><i class="icon-base bx {{ $benefit['icon'] }}" aria-hidden="true"></i> {{ $benefit['text'] }}</li>
                @endforeach
              </ul>
              <a href="#care-centers" class="btn btn-outline-primary btn-sm">{{ $pageContent['cism_link'] }} <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i></a>
            </div>
          </div>
        </section>

        <section class="card care-services-card mt-2" aria-labelledby="care-services-title">
          <div class="care-section-heading">
            <i class="icon-base bx {{ $pageContent['section_icons']['services'] }}" aria-hidden="true"></i>
            <h2 id="care-services-title">{{ $pageContent['services_title'] }}</h2>
          </div>
          <div class="care-services-grid">
            @foreach ($serviceTypes as $service)
              <article class="care-service-tile">
                <span class="care-service-icon"><i class="icon-base bx {{ data_get($service, 'icon', 'bx-heart') }}" aria-hidden="true"></i></span>
                <h3>{{ data_get($service, 'name') }}</h3>
                <p>{{ data_get($service, 'description') }}</p>
              </article>
            @endforeach
          </div>
          <p class="care-disclaimer">{{ $pageContent['services_disclaimer'] }}</p>
        </section>
      </div>

      <div class="col-md-4">
        <section class="card care-search-card h-100" aria-labelledby="care-search-title">
          <div class="care-search-heading">
            <i class="icon-base bx {{ $pageContent['section_icons']['search'] }}" aria-hidden="true"></i>
            <h2 id="care-search-title">{{ $pageContent['search_title'] }}</h2>
          </div>
          <form action="{{ route('public.care.index') }}" method="GET" class="care-search-form">
            <div>
              <label for="care-province" class="form-label">{{ $pageContent['province_label'] }}</label>
              <select id="care-province" name="province" class="form-select">
                <option value="">Toutes les provinces</option>
                @foreach ($provinces as $province)
                  <option value="{{ $province }}" @selected(($filters['province'] ?? '') === $province)>{{ $province }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label for="care-department" class="form-label">{{ $pageContent['department_label'] }}</label>
              <select id="care-department" name="department" class="form-select">
                <option value="">Tous les départements</option>
                @foreach ($departments as $department)
                  <option value="{{ $department }}" @selected(($filters['department'] ?? '') === $department)>{{ $department }}</option>
                @endforeach
              </select>
            </div>
            <details class="care-query-filter" @if (!empty($filters['q'])) open @endif>
              <summary>{{ $pageContent['query_summary'] }}</summary>
              <input id="care-query" type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" maxlength="120" placeholder="{{ $pageContent['query_placeholder'] }}">
            </details>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-grow-1"><i class="icon-base bx bx-search me-1" aria-hidden="true"></i>{{ $pageContent['search_button'] }}</button>
              @if (collect($filters)->filter()->isNotEmpty())
                <a href="{{ route('public.care.index') }}" class="btn btn-outline-secondary" aria-label="Effacer les filtres"><i class="icon-base bx bx-reset"></i></a>
              @endif
            </div>
          </form>

          <div class="care-map-heading">
            <strong>{{ $pageContent['map_title'] }}</strong>
            <span>{{ method_exists($centers, 'total') ? $centers->total() : $centers->count() }}</span>
          </div>
          <div id="care-map" class="care-map" role="region" aria-label="Carte du Tchad indiquant les structures de prise en charge autorisées à être affichées">
            <p id="care-map-status" class="care-map-status" role="status" aria-live="polite">Chargement de la carte…</p>
          </div>
          <p class="care-map-attribution">{{ $pageContent['map_attribution'] }}</p>
        </section>
      </div>
    </div>

    <section class="row g-2 mt-0" aria-label="Alerte et orientation">
      <div class="col-md-5">
        <article class="card care-alert-card h-100">
          <div class="care-card-heading">
            <i class="icon-base bx {{ $pageContent['section_icons']['alert'] }}" aria-hidden="true"></i>
            <h2>{{ $pageContent['alert_title'] }}</h2>
          </div>
          <p>{{ $pageContent['alert_description'] }}</p>
          @if ($assistancePhone)
            <a class="care-phone-panel" href="tel:{{ preg_replace('/[^0-9+]/', '', $assistancePhone) }}">
              <i class="icon-base bx bx-phone-call" aria-hidden="true"></i>
              <span><small>{{ $pageContent['phone_label'] }}</small><strong>{{ $assistancePhone }}</strong></span>
              <span class="care-phone-action">{{ $pageContent['phone_action'] }}</span>
            </a>
          @endif
          <p class="care-safety-note"><i class="icon-base bx bx-lock-alt" aria-hidden="true"></i> {{ $pageContent['safety_note'] }}</p>
        </article>
      </div>
      <div class="col-md-7">
        <article class="card care-process-card h-100">
          <div class="care-section-heading">
            <i class="icon-base bx {{ $pageContent['section_icons']['process'] }}" aria-hidden="true"></i>
            <h2>{{ $pageContent['process_title'] }}</h2>
          </div>
          <div class="care-process-steps">
            @foreach ($pageContent['process_steps'] as $step)
              <div class="care-process-step">
                <span class="care-step-icon"><i class="icon-base bx {{ $step['icon'] }}" aria-hidden="true"></i></span>
                <strong>{{ $step['title'] }}</strong>
                <p>{{ $step['description'] }}</p>
              </div>
              @if (!$loop->last)
                <i class="icon-base bx bx-right-arrow-alt care-step-arrow" aria-hidden="true"></i>
              @endif
            @endforeach
          </div>
        </article>
      </div>
    </section>

    <section class="row g-2 mt-0">
      <div class="col-md-6">
        <article class="card care-prevention-card h-100">
          <div class="care-card-heading"><i class="icon-base bx {{ $pageContent['section_icons']['prevention'] }}" aria-hidden="true"></i><h2>{{ $pageContent['prevention_title'] }}</h2></div>
          <p class="care-card-lead">{{ $pageContent['prevention_description'] }}</p>
          <ul class="care-prevention-list">
            @foreach ($pageContent['prevention_items'] as $item)
              <li><i class="icon-base bx bxs-check-circle" aria-hidden="true"></i> {{ $item }}</li>
            @endforeach
          </ul>
        </article>
      </div>
      <div class="col-md-6">
        <article class="card care-news-card h-100">
          <div class="care-card-heading"><i class="icon-base bx {{ $pageContent['section_icons']['news'] }}" aria-hidden="true"></i><h2>{{ $pageContent['news_title'] }}</h2></div>
          <p>{{ $pageContent['news_description'] }}</p>
          <a class="btn btn-outline-primary btn-sm align-self-start" href="{{ route('program-news.index', ['category' => 'paix']) }}">{{ $pageContent['news_link'] }} <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i></a>
        </article>
      </div>
    </section>

    <section class="card care-directory-card mt-3" id="care-centers" aria-labelledby="care-centers-title">
      <div class="care-section-heading care-directory-heading">
        <i class="icon-base bx {{ $pageContent['section_icons']['directory'] }}" aria-hidden="true"></i>
        <div>
          <h2 id="care-centers-title">{{ $pageContent['directory_title'] }}</h2>
          <p>{{ $pageContent['directory_description'] }}</p>
        </div>
        <span class="care-directory-count">{{ method_exists($centers, 'total') ? $centers->total() : $centers->count() }} {{ $pageContent['directory_results_label'] }}</span>
      </div>
      <div class="care-directory-body">
        @if ($centers->isNotEmpty())
          <div class="row g-3">
            @foreach ($centers as $center)
              <div class="col-lg-6">
                <article class="care-center-item h-100">
                  <i class="icon-base bx bx-building-house" aria-hidden="true"></i>
                  <div>
                    <span class="care-center-type">{{ $center->structure_type }}</span>
                    <h3>{{ $center->name }}</h3>
                    <p class="care-center-location"><i class="icon-base bx bx-map-pin" aria-hidden="true"></i>{{ collect([$center->locality, $center->department, $center->province])->filter()->implode(', ') }}</p>
                    @if ($center->description)
                      <p class="care-center-description">{{ \Illuminate\Support\Str::limit($center->description, 160) }}</p>
                    @endif
                    @if ($center->services->isNotEmpty())
                      <div class="care-service-tags">
                        @foreach ($center->services->take(4) as $service)
                          <span>{{ $service->name }}</span>
                        @endforeach
                      </div>
                    @endif
                    <div class="d-flex flex-wrap gap-2 mt-3">
                      @if ($center->show_public_detail)
                        <a href="{{ route('public.care.show', $center->slug) }}" class="btn btn-sm btn-primary">{{ $pageContent['center_detail_link'] }}</a>
                      @endif
                      @if ($center->phone)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $center->phone) }}" class="btn btn-sm btn-outline-secondary"><i class="icon-base bx bx-phone me-1" aria-hidden="true"></i>{{ $pageContent['center_call_link'] }}</a>
                      @endif
                    </div>
                  </div>
                </article>
              </div>
            @endforeach
          </div>
          @if (method_exists($centers, 'links'))
            <div class="mt-3">{{ $centers->links() }}</div>
          @endif
        @else
          <div class="care-empty-state">
            <i class="icon-base bx bx-search-alt" aria-hidden="true"></i>
            <h3>{{ $pageContent['directory_empty_title'] }}</h3>
            <p>{{ $pageContent['directory_empty_description'] }}</p>
          </div>
        @endif
      </div>
    </section>

    <p class="care-method-note"><i class="icon-base bx bx-info-circle" aria-hidden="true"></i><span><strong>{{ $pageContent['method_note_title'] }}</strong> {{ $pageContent['method_note_description'] }}</span></p>
  </main>
</div>
@endsection
