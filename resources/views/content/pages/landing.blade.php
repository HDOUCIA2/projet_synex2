@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', $siteSettings->name.' — Le conflit, lisible avant la crise')

@section('page-script')
  @vite('resources/assets/js/veille-animations.js')
@endsection

@section('page-style')
@vite('resources/assets/css/veille.css')
<style>
  body {
    background: var(--synex-ivory);
    color: var(--synex-muted);
    -webkit-font-smoothing: antialiased;
  }

  .landing-shell {
    min-height: 100vh;
    overflow: hidden;
  }

  .hero-section {
    position: relative;
    background:
      radial-gradient(circle at 82% 18%, rgba(var(--bs-primary-rgb), .12), transparent 26rem),
      linear-gradient(180deg, #FFFFFF 0%, var(--synex-ivory) 100%);
  }

  .hero-copy {
    max-width: 650px;
  }

  .hero-title {
    max-width: 760px;
    letter-spacing: -0.045em;
    line-height: 1.08;
  }

  .hero-title span {
    color: var(--synex-primary);
  }

  .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.09em;
    text-transform: uppercase;
  }

  .eyebrow-dot {
    width: 8px;
    height: 8px;
    flex: 0 0 auto;
    border-radius: 50%;
    background: var(--synex-primary-light);
    box-shadow: 0 0 0 5px rgba(var(--bs-primary-rgb), .13);
  }

  .btn-primary {
    box-shadow: 0 10px 24px rgba(var(--bs-primary-rgb), .22);
  }

  .btn-primary:hover {
    background: var(--synex-primary-dark);
  }

  .landing-shell a:focus-visible,
  .landing-shell button:focus-visible {
    outline: 3px solid rgba(var(--bs-primary-rgb), .55);
    outline-offset: 3px;
  }

  .product-preview {
    position: relative;
    max-width: 580px;
    margin-left: auto;
  }

  .product-preview::before {
    content: "";
    position: absolute;
    inset: 8% -7% -7% 18%;
    z-index: 0;
    border-radius: 2rem;
    background: rgba(var(--bs-primary-rgb), .09);
    transform: rotate(3deg);
  }

  .preview-card {
    position: relative;
    z-index: 1;
    overflow: hidden;
    border: 1px solid rgba(var(--bs-primary-rgb), .12) !important;
    border-radius: 1.25rem;
    box-shadow: 0 1.5rem 4rem rgba(44, 40, 31, .14);
  }

  .preview-toolbar {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .preview-toolbar span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--synex-border);
  }

  .preview-kpi {
    border: 1px solid var(--synex-border);
    border-radius: 0.875rem;
  }

  .logo-strip {
    border-top: 1px solid var(--synex-border);
    border-bottom: 1px solid var(--synex-border);
  }

  .logo-strip-item {
    color: var(--synex-muted);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .metric-card,
  .feature-card,
  .process-card {
    border: 1px solid var(--synex-border);
    border-radius: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  }

  .metric-card:hover,
  .feature-card:hover,
  .process-card:hover {
    transform: translateY(-3px);
    border-color: rgba(var(--bs-primary-rgb), .28);
    box-shadow: 0 1rem 2.5rem rgba(44, 40, 31, .08);
  }

  .feature-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: grid;
    place-items: center;
  }

  .process-number {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    background: var(--synex-primary);
    color: #fff;
    font-size: 0.825rem;
    font-weight: 700;
  }

  .cta-panel {
    position: relative;
    overflow: hidden;
    border-radius: 1.5rem;
    background: var(--synex-ink);
  }

  .cta-panel::after {
    content: "";
    position: absolute;
    width: 320px;
    height: 320px;
    right: -180px;
    bottom: -240px;
    border: 60px solid rgba(var(--bs-primary-rgb), .18);
    border-radius: 50%;
  }

  .section-anchor {
    scroll-margin-top: 96px;
  }

  @media (prefers-reduced-motion: reduce) {
    .metric-card,
    .feature-card,
    .process-card {
      transition: none;
    }

    .metric-card:hover,
    .feature-card:hover,
    .process-card:hover {
      transform: none;
    }
  }

  @media (max-width: 991.98px) {
    .hero-title {
      letter-spacing: -0.035em;
    }

    .product-preview {
      max-width: 620px;
      margin: 0 auto;
    }
  }

  @media (max-width: 575.98px) {
    .hero-title {
      font-size: 2.25rem;
    }

  }
</style>
@endsection

@section('content')
  @php
    $dashboardTarget = auth()->user()?->isAdmin() ? route('admin.dashboard') : route('dashboard-analytics');
  @endphp
<div class="landing-shell">
  @include('content.pages._public-navbar', ['activePage' => 'home'])

  <main>
    <section id="aperçu" class="hero-section section-anchor">
      <div class="container-xxl px-4 py-5 py-lg-6">
        <div class="row align-items-center g-5 py-lg-4">
          @php
            $hero = $homeSections->get('home_hero');
          @endphp
          @if ($hero?->is_active)
          <div class="col-lg-6">
          <div class="hero-copy">
            @if ($hero->eyebrow)
            <div class="eyebrow text-primary mb-4" data-veille-animation="fade-up">
              <span class="eyebrow-dot"></span>
              {{ $hero->eyebrow }}
            </div>
            @endif
            <h1 class="hero-title display-4 fw-bold mb-4" data-veille-animation="fade-up">
              {{ $hero->title }} <span>{{ $hero->accent }}</span>
            </h1>
            @if ($hero->description)<p class="lead text-muted mb-4" data-veille-animation="fade-up">{{ $hero->description }}</p>@endif
            <div class="d-flex flex-wrap gap-3 mb-4">
              @if ($hero->button_text)
                <a href="{{ $dashboardTarget }}" class="btn btn-primary btn-lg px-4">{{ $hero->button_text }} <i class="icon-base bx bx-right-arrow-alt ms-1"></i></a>
              @endif
              @if ($hero->secondary_button_text)
                <a href="{{ route('veille') }}" class="btn btn-outline-secondary btn-lg px-4">{{ $hero->secondary_button_text }}</a>
              @endif
            </div>
          </div>
          </div>
          @endif

          @php
            $preview = $homeSections->get('home_preview');
          @endphp
          @if ($preview?->is_active)
          <div class="col-lg-6">
            <div class="product-preview">
          <div class="card preview-card shadow-sm">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 border-bottom">
              <div class="preview-toolbar" aria-hidden="true">
                <span></span><span></span><span></span>
              </div>
              <span class="badge bg-label-warning"><i class="icon-base bx bx-show me-1"></i>Illustration</span>
            </div>
            <div class="card-body p-4">
              <div class="d-flex align-items-center justify-content-between mb-4">
                <div><h5 class="mb-1">{{ $preview->title }}</h5>@if ($preview->description)<small class="text-muted">{{ $preview->description }}</small>@endif</div>
              </div>

              <div class="veille-card-grid">
                @foreach ($preview->cards as $card)
                  <article class="preview-kpi p-3" data-veille-animation="{{ $card->animation }}">
                    @if ($card->icon)<i class="icon-base bx {{ $card->icon }} text-{{ $card->color }} me-1" aria-hidden="true"></i>@endif
                    <small class="text-muted d-block mb-2">{{ $card->title }}</small>
                    @if ($card->value)<div class="fw-bold fs-4 text-{{ $card->color }}">{{ $card->value }}</div>@endif
                    @if ($card->description)<p class="small mb-0">{{ $card->description }}</p>@endif
                  </article>
                @endforeach
              </div>
              @if ($preview->secondary_text)<p class="text-muted small mt-3 mb-0">{{ $preview->secondary_text }}</p>@endif
            </div>
          </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </section>

    @php
      $highlights = $homeSections->get('home_highlights');
    @endphp
    @if ($highlights?->is_active)
    <section class="logo-strip">
      <div class="container-xxl px-4 py-4">
        <div class="row g-3 align-items-center text-center">
          @foreach ($highlights->cards as $card)
            <div class="col-6 col-md-3 logo-strip-item" data-veille-animation="{{ $card->animation }}">{{ $card->title }}</div>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    @php
      $coverage = $homeSections->get('home_coverage');
    @endphp
    @if ($coverage?->is_active)
    <section id="veille" class="container-xxl px-4 py-5 py-lg-6 section-anchor">
      <div class="row align-items-end mb-4 g-3">
        <div class="col-lg-7">
          @if ($coverage->eyebrow)<span class="eyebrow text-primary" data-veille-animation="fade-up">{{ $coverage->eyebrow }}</span>@endif
          <h2 class="display-6 fw-bold mt-3 mb-0" data-veille-animation="fade-up">{{ $coverage->title }}</h2>
        </div>
        <div class="col-lg-5">
          @if ($coverage->secondary_text)<p class="text-muted mb-0" data-veille-animation="fade-up">{{ $coverage->secondary_text }}</p>@endif
        </div>
      </div>

      <div class="veille-card-grid">
        @foreach ($coverage->cards as $card)
          <article class="card veille-content-card metric-card" data-veille-animation="{{ $card->animation }}">
            <div class="card-body p-4">
              @if ($card->value)<span class="badge bg-label-{{ $card->color }} mb-4">{{ $card->value }}</span>@endif
              <h3 class="h5">{{ $card->title }}</h3>
              @if ($card->description)<p class="text-muted mb-0">{{ $card->description }}</p>@endif
            </div>
          </article>
        @endforeach
      </div>
    </section>
    @endif

    @php
      $features = $homeSections->get('home_features');
    @endphp
    @if ($features?->is_active)
    <section class="bg-lighter py-5 py-lg-6">
      <div class="container-xxl px-4">
        <div class="text-center mx-auto mb-5" style="max-width: 720px;">
          @if ($features->eyebrow)<span class="eyebrow text-primary" data-veille-animation="fade-up">{{ $features->eyebrow }}</span>@endif
          <h2 class="display-6 fw-bold mt-3 mb-3" data-veille-animation="fade-up">{{ $features->title }}</h2>
          @if ($features->description)<p class="text-muted mb-0" data-veille-animation="fade-up">{{ $features->description }}</p>@endif
        </div>
        <div class="veille-card-grid">
          @foreach ($features->cards as $card)
            <article class="card veille-content-card feature-card" data-veille-animation="{{ $card->animation }}">
              <div class="card-body p-4 p-xl-5">
                @if ($card->icon)<span class="feature-icon bg-label-{{ $card->color }} mb-4"><i class="icon-base bx {{ $card->icon }} icon-lg"></i></span>@endif
                <h3 class="h5 mb-3">{{ $card->title }}</h3>
                @if ($card->description)<p class="text-muted mb-0">{{ $card->description }}</p>@endif
              </div>
            </article>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    @php
      $severity = $homeSections->get('home_severity');
    @endphp
    @if ($severity?->is_active)
    <section id="methode" class="container-xxl px-4 py-5 py-lg-6 section-anchor">
      <div class="row align-items-center g-5">
        <div class="col-lg-5">
          @if ($severity->eyebrow)<span class="eyebrow text-primary" data-veille-animation="fade-up">{{ $severity->eyebrow }}</span>@endif
          <h2 class="display-6 fw-bold mt-3 mb-4" data-veille-animation="fade-up">{{ $severity->title }}</h2>
          @if ($severity->description)<p class="text-muted mb-4" data-veille-animation="fade-up">{{ $severity->description }}</p>@endif
          @if ($severity->button_text)<a href="{{ $dashboardTarget }}" class="btn btn-outline-primary" data-veille-animation="fade-up">{{ $severity->button_text }} <i class="icon-base bx bx-right-arrow-alt ms-1"></i></a>@endif
        </div>
        <div class="col-lg-7">
          <div class="veille-card-grid">
            @foreach ($severity->cards as $card)
              <article class="card veille-content-card process-card" data-veille-animation="{{ $card->animation }}">
                <div class="card-body p-4">
                  <span class="process-number mb-4">{{ $card->value }}</span>
                  <h3 class="h5">{{ $card->title }}</h3>
                  @if ($card->description)<p class="text-muted mb-0 small">{{ $card->description }}</p>@endif
                </div>
              </article>
            @endforeach
          </div>
        </div>
      </div>
      @if ($severity->secondary_text)<p class="text-muted small mt-4 mb-0">{{ $severity->secondary_text }}</p>@endif

      <div class="card mt-5" data-veille-animation="fade-up">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
          <div>
            <h3 class="h5 mb-1">Taux annuel par province</h3>
            <p class="card-subtitle mb-0">Décès rapportés pour 100 000 habitants, calculés à partir des données de population administrées.</p>
          </div>
          @if ($populationYears->isNotEmpty())
            <form method="GET" action="{{ route('home') }}">
              <label for="population-year" class="visually-hidden">Année du calcul</label>
              <select id="population-year" name="population_year" class="form-select" onchange="this.form.submit()">
                @foreach ($populationYears as $year)
                  <option value="{{ $year }}" @selected($rateYear == $year)>{{ $year }}</option>
                @endforeach
              </select>
            </form>
          @endif
        </div>
        <div class="card-body">
          @if ($deathRates->isNotEmpty())
            @php
              $maxRate = max(1, (float) $deathRates->max('rate'));
            @endphp
            <div class="veille-card-grid">
              @foreach ($deathRates as $rate)
                @php
                  $rateWidth = min(100, ($rate['rate'] / $maxRate) * 100);
                @endphp
                <article class="border rounded p-3" data-veille-animation="fade-up">
                  <div class="d-flex justify-content-between align-items-start gap-2">
                    <div><h4 class="h6 mb-1">{{ $rate['province'] }}</h4><small class="text-muted">{{ $rate['year'] }} · {{ number_format($rate['deaths'], 0, ',', ' ') }} décès / {{ number_format($rate['population'], 0, ',', ' ') }} habitants</small></div>
                    <strong class="text-primary text-nowrap">{{ number_format($rate['rate'], 2, ',', ' ') }}</strong>
                  </div>
                  <div class="veille-rate-bar mt-3" role="img" aria-label="{{ number_format($rate['rate'], 2, ',', ' ') }} décès pour 100 000 habitants">
                    <span style="--rate-width: {{ $rateWidth }}%"></span>
                  </div>
                </article>
              @endforeach
            </div>
          @else
            <p class="text-muted mb-0">Les taux apparaîtront après la saisie des populations par province et par année dans l’administration.</p>
          @endif
        </div>
      </div>
    </section>
    @endif

    @php
      $cta = $homeSections->get('home_cta');
    @endphp
    @if ($cta?->is_active)
    <section class="container-xxl px-4 pb-5 pb-lg-6">
      <div class="cta-panel text-white p-4 p-md-5">
        <div class="row align-items-center position-relative" style="z-index: 1;">
          <div class="col-lg-8">
            @if ($cta->eyebrow)<span class="text-white-50 small text-uppercase fw-semibold" data-veille-animation="fade-up">{{ $cta->eyebrow }}</span>@endif
            <h2 class="h1 fw-bold mt-2 mb-2" data-veille-animation="fade-up">{{ $cta->title }}</h2>
            @if ($cta->description)<p class="text-white-50 mb-0" data-veille-animation="fade-up">{{ $cta->description }}</p>@endif
          </div>
          <div class="col-lg-4 mt-4 mt-lg-0 text-lg-end">
            @if ($cta->button_text)<a href="{{ $dashboardTarget }}" class="btn btn-light btn-lg px-4" data-veille-animation="fade-left">{{ $cta->button_text }} <i class="icon-base bx bx-right-arrow-alt ms-1"></i></a>@endif
          </div>
        </div>
      </div>
    </section>
    @endif
  </main>

</div>
@endsection
