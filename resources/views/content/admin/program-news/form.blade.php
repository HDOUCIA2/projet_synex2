@extends('layouts/contentNavbarLayout')

@php($editing = $news->exists)

@section('html-lang', 'fr')
@section('title', $editing ? 'Modifier une actualité' : 'Nouvelle actualité')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1">{{ $editing ? 'Modifier une actualité' : 'Nouvelle actualité' }}</h4>
    <p class="text-muted mb-0">Renseignez le contenu et l’image qui seront présentés dans les cartes Sneat.</p>
  </div>
  <a href="{{ route('admin.program-news.index') }}" class="btn btn-outline-secondary">Retour à la liste</a>
</div>

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ $editing ? route('admin.program-news.update', $news) : route('admin.program-news.store') }}" enctype="multipart/form-data">
  @csrf
  @if ($editing)
    @method('PUT')
  @endif
  <div class="card mb-6">
    <div class="card-header"><h5 class="card-title mb-0">Contenu de l’actualité</h5></div>
    <div class="card-body row g-4">
      <div class="col-12">
        <label for="news-title" class="form-label">Titre *</label>
        <input id="news-title" name="title" class="form-control" maxlength="180" value="{{ old('title', $news->title) }}" required>
      </div>
      <div class="col-12">
        <label for="news-summary" class="form-label">Courte description *</label>
        <textarea id="news-summary" name="summary" rows="2" class="form-control" maxlength="500" required>{{ old('summary', $news->summary) }}</textarea>
        <div class="form-text">500 caractères maximum.</div>
      </div>
      <div class="col-md-6">
        <label for="news-category" class="form-label">Catégorie *</label>
        <select id="news-category" name="category" class="form-select" required>
          <option value="">Choisir une catégorie</option>
          @foreach ($categories as $value => $label)
            <option value="{{ $value }}" @selected(old('category', $news->category) === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="news-region" class="form-label">Région *</label>
        <select id="news-region" name="region" class="form-select" required>
          <option value="">Choisir une région</option>
          @foreach ($regions as $value => $label)
            <option value="{{ $value }}" @selected(old('region', $news->region) === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12">
        <label for="news-content" class="form-label">Article complet *</label>
        <textarea id="news-content" name="content" rows="10" class="form-control" maxlength="50000" required>{{ old('content', $news->content) }}</textarea>
      </div>
      <div class="col-md-6">
        <label for="news-image" class="form-label">Image {{ $editing ? '(facultative)' : '*' }}</label>
        <input id="news-image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control" @required(!$editing)>
        <div class="form-text">JPG, PNG ou WEBP, 5 Mo maximum.</div>
        @if ($editing && $news->image_path)
          <img src="{{ asset('storage/'.$news->image_path) }}" alt="Image actuelle de {{ $news->title }}" class="img-thumbnail mt-3" style="max-height: 180px">
        @endif
      </div>
      <div class="col-md-6">
        <label for="published-at" class="form-label">Date de publication *</label>
        <input id="published-at" name="published_at" type="datetime-local" class="form-control" value="{{ old('published_at', $news->published_at?->format('Y-m-d\TH:i')) }}" required>
        <div class="form-text">Une actualité programmée apparaîtra à cette date si elle est publiée.</div>
      </div>
      <div class="col-12">
        <div class="form-check">
          <input id="news-published" name="is_published" type="checkbox" class="form-check-input" value="1" @checked(old('is_published', $news->is_published))>
          <label for="news-published" class="form-check-label">Publier cette actualité</label>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.program-news.index') }}" class="btn btn-label-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary">
      <i class="icon-base bx bx-save me-1"></i>{{ $editing ? 'Enregistrer les modifications' : 'Créer l’actualité' }}
    </button>
  </div>
</form>
@endsection
