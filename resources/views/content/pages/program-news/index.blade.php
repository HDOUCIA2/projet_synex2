@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', 'Actualités du programme')

@section('page-style')
  @vite('resources/assets/css/program-news.css')
@endsection

@section('content')
@include('content.pages._public-navbar', ['activePage' => 'program-news'])

<main class="container-xxl px-4 py-5 synex-news-page">
  <h1 class="visually-hidden">Actualités du programme</h1>
  <form method="GET" action="{{ route('program-news.index') }}" class="card synex-news-filters mb-4">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-lg-4">
          <label for="newsSearch" class="form-label">Rechercher</label>
          <div class="input-group">
            <span class="input-group-text bg-white" aria-hidden="true">
              <i class="icon-base bx bx-search"></i>
            </span>
            <input
              type="search"
              class="form-control"
              id="newsSearch"
              name="q"
              value="{{ $filters['q'] ?? '' }}"
              maxlength="120"
              placeholder="Rechercher une actualité..."
            >
          </div>
        </div>

        <div class="col-lg-2 col-md-4">
          <label for="newsCategory" class="form-label">Catégorie</label>
          <select class="form-select" id="newsCategory" name="category">
            <option value="">Toutes les catégories</option>
            @foreach ($categories as $value => $label)
              <option value="{{ $value }}" @selected(($filters['category'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 col-md-4">
          <label for="newsRegion" class="form-label">Région</label>
          <select class="form-select" id="newsRegion" name="region">
            <option value="">Toutes les régions</option>
            @foreach ($regions as $value => $label)
              <option value="{{ $value }}" @selected(($filters['region'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2 col-md-4">
          <label for="newsDate" class="form-label">Année</label>
          <select class="form-select" id="newsDate" name="year">
            <option value="">Toutes les dates</option>
            @foreach ($years as $year)
              <option value="{{ $year }}" @selected(($filters['year'] ?? null) == $year)>{{ $year }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2">
          <button class="btn btn-primary w-100" type="submit" id="newsSearchButton">
            <i class="icon-base bx bx-search me-1" aria-hidden="true"></i>
            Rechercher
          </button>
        </div>
      </div>
    </div>
  </form>

  @if ($newsItems->isNotEmpty())
    <div class="row g-4" id="newsList">
      @foreach ($newsItems as $news)
        <div class="col-md-6 col-xl-4">
          <article class="card synex-news-card h-100">
            <a class="synex-news-image" href="{{ route('program-news.show', $news) }}" aria-label="Lire l’actualité : {{ $news->title }}">
              @if ($news->image_path)
                <img
                  src="{{ asset('storage/'.$news->image_path) }}"
                  class="card-img-top"
                  alt="{{ $news->title }}"
                  loading="lazy"
                >
              @else
                <span class="synex-news-placeholder" aria-hidden="true">
                  <i class="icon-base bx bx-news"></i>
                </span>
              @endif
              @if ($news->category && isset($categories[$news->category]))
                <span class="badge synex-news-category">{{ $categories[$news->category] }}</span>
              @endif
            </a>

            <div class="card-body d-flex flex-column">
              <div class="synex-news-meta">
                <span>
                  <i class="icon-base bx bx-calendar me-1" aria-hidden="true"></i>
                  <time datetime="{{ $news->published_at->toDateString() }}">{{ $news->published_at->format('d/m/Y') }}</time>
                </span>
                @if ($news->region && isset($regions[$news->region]))
                  <span class="synex-news-region">{{ $regions[$news->region] }}</span>
                @endif
              </div>

              <h2 class="card-title">{{ $news->title }}</h2>
              <p class="card-text synex-news-summary">{{ $news->summary }}</p>
              <a href="{{ route('program-news.show', $news) }}" class="synex-news-link mt-auto">
                Lire l’actualité
                <i class="icon-base bx bx-right-arrow-alt ms-1" aria-hidden="true"></i>
              </a>
            </div>
          </article>
        </div>
      @endforeach
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-5">
      <p class="text-muted small mb-0">
        Affichage de {{ $newsItems->firstItem() }} à {{ $newsItems->lastItem() }} sur {{ $newsItems->total() }} actualités
      </p>
      {{ $newsItems->links('pagination::bootstrap-5') }}
    </div>
  @else
    <div id="newsEmpty" class="alert alert-light text-center py-5" role="status">
      <i class="icon-base bx bx-info-circle icon-xl mb-2" aria-hidden="true"></i>
      <h2 class="h5">Aucune actualité ne correspond à votre recherche.</h2>
      <p class="text-muted mb-3">Essayez de modifier ou de supprimer un filtre.</p>
      <a href="{{ route('program-news.index') }}" class="btn btn-outline-primary">Afficher toutes les actualités</a>
    </div>
  @endif
</main>
@endsection
