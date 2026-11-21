@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', $news->title.' — Actualités du programme')

@section('page-style')
  @vite('resources/assets/css/program-news.css')
@endsection

@section('content')
@include('content.pages._public-navbar', ['activePage' => 'program-news'])

<main class="container-xxl px-4 py-4 py-lg-5 synex-news-page synex-article">
  <nav aria-label="Fil d’Ariane" class="mb-4">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
      <li class="breadcrumb-item"><a href="{{ route('program-news.index') }}">Actualités du programme</a></li>
      <li class="breadcrumb-item active" aria-current="page">Détail</li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-lg-8">
      <article class="card article-main-card">
        <div class="card-body">
          <div class="d-flex align-items-center flex-wrap gap-3 mb-4">
            @if ($news->category)
              <span class="badge article-category">
                {{ \App\Models\ProgramNews::CATEGORIES[$news->category] ?? $news->category }}
              </span>
            @endif
            <span class="article-date">
              <i class="icon-base bx bx-calendar me-1" aria-hidden="true"></i>
              <time datetime="{{ $news->published_at->toDateString() }}">{{ $news->published_at->format('d/m/Y') }}</time>
            </span>
          </div>

          <h1 class="article-title">{{ $news->title }}</h1>

          @if ($news->summary)
            <p class="article-description">{{ $news->summary }}</p>
          @endif

          @if ($news->image_path)
            <figure class="article-cover">
              <img src="{{ asset('storage/'.$news->image_path) }}" alt="{{ $news->title }}" fetchpriority="high">
            </figure>
          @else
            <div class="article-cover article-cover-placeholder" role="img" aria-label="Aucune image disponible pour cette actualité">
              <i class="icon-base bx bx-news" aria-hidden="true"></i>
            </div>
          @endif

          <section class="article-content mt-4" aria-label="Contenu de l’article">{{ $news->content }}</section>

          <a href="{{ route('program-news.index') }}" class="btn btn-outline-primary mt-4">
            <i class="icon-base bx bx-arrow-back me-1" aria-hidden="true"></i>Retour aux actualités
          </a>
        </div>
      </article>
    </div>

    <div class="col-lg-4">
      <aside class="article-sidebar" aria-label="Informations complémentaires">
        <section class="card article-sidebar-card mb-4">
          <div class="card-header">
            <h2 class="h5 mb-0">
              <i class="icon-base bx bx-info-circle" aria-hidden="true"></i>
              Informations clés
            </h2>
          </div>
          <div class="card-body">
            <div class="article-info-row">
              <i class="icon-base bx bx-map-pin" aria-hidden="true"></i>
              <span>Zone</span>
              <strong>{{ \App\Models\ProgramNews::REGIONS[$news->region] ?? 'Non précisée' }}</strong>
            </div>
            <div class="article-info-row">
              <i class="icon-base bx bx-calendar" aria-hidden="true"></i>
              <span>Publication</span>
              <strong><time datetime="{{ $news->published_at->toDateString() }}">{{ $news->published_at->format('d/m/Y') }}</time></strong>
            </div>
            <div class="article-info-row">
              <i class="icon-base bx bx-layer" aria-hidden="true"></i>
              <span>Type</span>
              <strong>{{ \App\Models\ProgramNews::CATEGORIES[$news->category] ?? 'Non précisé' }}</strong>
            </div>
          </div>
        </section>

        @if ($relatedNews->isNotEmpty())
          <section class="card article-sidebar-card">
            <div class="card-header">
              <h2 class="h5 mb-0">
                <i class="icon-base bx bx-book-open" aria-hidden="true"></i>
                Lire aussi
              </h2>
            </div>
            <div class="card-body">
              @foreach ($relatedNews as $related)
                <a href="{{ route('program-news.show', $related) }}" class="related-article">
                  @if ($related->image_path)
                    <img src="{{ asset('storage/'.$related->image_path) }}" alt="" loading="lazy">
                  @else
                    <span class="related-article-placeholder" aria-hidden="true">
                      <i class="icon-base bx bx-news"></i>
                    </span>
                  @endif
                  <span class="related-article-copy">
                    <span class="related-article-title">{{ $related->title }}</span>
                    <small>
                      <i class="icon-base bx bx-calendar" aria-hidden="true"></i>
                      <time datetime="{{ $related->published_at->toDateString() }}">{{ $related->published_at->format('d/m/Y') }}</time>
                    </small>
                  </span>
                  <i class="icon-base bx bx-chevron-right related-arrow" aria-hidden="true"></i>
                </a>
              @endforeach
            </div>
          </section>
        @endif
      </aside>
    </div>
  </div>
</main>
@endsection
