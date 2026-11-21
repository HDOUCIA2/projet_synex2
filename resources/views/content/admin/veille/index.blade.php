@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Gestion de la veille')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1">Gestion de la veille</h4>
    <p class="text-muted mb-0">Gérez les titres, descriptions, icônes ou images des lectures affichées sur la page Veille.</p>
  </div>
  <a href="{{ route('veille') }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
    <i class="icon-base bx bx-link-external me-1"></i>Voir la page Veille
  </a>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

<section class="card mb-6">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <span class="badge bg-label-primary mb-2">Page Veille</span>
      <h5 class="card-title mb-0">{{ $section->title }}</h5>
    </div>
    <span class="badge {{ $section->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
      {{ $section->is_active ? 'Publiée' : 'Dépubliée' }}
    </span>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.veille.sections.update', $section) }}" class="row g-4">
      @csrf
      @method('PUT')
      <div class="col-12">
        <label class="form-label" for="section-title">Titre</label>
        <input id="section-title" name="title" class="form-control" maxlength="180" value="{{ $section->title }}" required>
      </div>
      <div class="col-12">
        <label class="form-label" for="section-description">Description</label>
        <textarea id="section-description" name="description" rows="3" class="form-control" maxlength="10000">{{ $section->description }}</textarea>
      </div>
      <div class="col-md-6 d-flex align-items-center">
        <div class="form-check">
          <input id="section-active" name="is_active" type="checkbox" class="form-check-input" value="1" @checked($section->is_active)>
          <label for="section-active" class="form-check-label">Publier cette section</label>
        </div>
      </div>
      <div class="col-md-6 text-md-end">
        <button type="submit" class="btn btn-primary">
          <i class="icon-base bx bx-save me-1"></i>Enregistrer les textes
        </button>
      </div>
    </form>
  </div>
</section>

<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h5 class="mb-1">Lectures du terrain</h5>
    <p class="text-muted mb-0">Modifiez le texte, publiez ou dépubliez une lecture, ou supprimez-la.</p>
  </div>
  <span class="badge bg-label-secondary">{{ $section->cards->count() }} contenu(s)</span>
</div>

@forelse ($section->cards as $card)
  <section class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h6 class="card-title mb-0">{{ $card->title }}</h6>
      <span class="badge {{ $card->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
        {{ $card->is_active ? 'Publiée' : 'Dépubliée' }}
      </span>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('admin.veille.cards.update', $card) }}" class="row g-3" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="col-md-4">
          <label for="card-title-{{ $card->id }}" class="form-label">Titre</label>
          <input id="card-title-{{ $card->id }}" name="title" class="form-control" maxlength="180" value="{{ $card->title }}" required>
        </div>
        <div class="col-md-8">
          <label for="card-description-{{ $card->id }}" class="form-label">Description</label>
          <textarea id="card-description-{{ $card->id }}" name="description" rows="2" class="form-control" maxlength="10000">{{ $card->description }}</textarea>
        </div>
        <div class="col-md-4">
          <label for="card-icon-{{ $card->id }}" class="form-label">Icône</label>
          <select id="card-icon-{{ $card->id }}" name="icon" class="form-select" required>
            @foreach ($icons as $icon => $label)
              <option value="{{ $icon }}" @selected($card->icon === $icon)>{{ $label }}</option>
            @endforeach
          </select>
          <span class="form-text">L’icône est utilisée lorsqu’aucune image n’est choisie.</span>
        </div>
        <div class="col-md-8">
          <label for="card-image-{{ $card->id }}" class="form-label">Image (facultative)</label>
          <input id="card-image-{{ $card->id }}" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control">
          <span class="form-text">JPG, PNG ou WEBP, 4 Mo maximum.</span>
          @if ($card->image_path)
            <div class="d-flex align-items-center gap-3 mt-3">
              <img src="{{ asset('storage/'.$card->image_path) }}" alt="Image actuelle de {{ $card->title }}" class="img-thumbnail" width="72" height="72" style="object-fit: cover">
              <div class="form-check">
                <input id="card-remove-image-{{ $card->id }}" name="remove_image" type="checkbox" class="form-check-input" value="1">
                <label for="card-remove-image-{{ $card->id }}" class="form-check-label">Retirer l’image et utiliser l’icône</label>
              </div>
            </div>
          @endif
        </div>
        <div class="col-md-6 d-flex align-items-center">
          <div class="form-check">
            <input id="card-active-{{ $card->id }}" name="is_active" type="checkbox" class="form-check-input" value="1" @checked($card->is_active)>
            <label for="card-active-{{ $card->id }}" class="form-check-label">Publier cette lecture</label>
          </div>
        </div>
        <div class="col-md-6 d-flex justify-content-md-end gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="icon-base bx bx-save me-1"></i>Enregistrer
          </button>
          <button type="submit" form="delete-card-{{ $card->id }}" class="btn btn-outline-danger">
            <i class="icon-base bx bx-trash me-1"></i>Supprimer
          </button>
        </div>
      </form>
      <form id="delete-card-{{ $card->id }}" method="POST" action="{{ route('admin.veille.cards.destroy', $card) }}">
        @csrf
        @method('DELETE')
      </form>
    </div>
  </section>
@empty
  <div class="alert alert-info">Aucune lecture n’est encore créée.</div>
@endforelse

<section class="card">
  <div class="card-header">
    <h5 class="card-title mb-0">Ajouter une lecture</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.veille.cards.store', $section) }}" class="row g-3" enctype="multipart/form-data">
      @csrf
      <div class="col-md-4">
        <label for="new-card-title" class="form-label">Titre</label>
        <input id="new-card-title" name="title" class="form-control" maxlength="180" required>
      </div>
      <div class="col-md-8">
        <label for="new-card-description" class="form-label">Description</label>
        <textarea id="new-card-description" name="description" rows="2" class="form-control" maxlength="10000"></textarea>
      </div>
      <div class="col-md-4">
        <label for="new-card-icon" class="form-label">Icône</label>
        <select id="new-card-icon" name="icon" class="form-select" required>
          @foreach ($icons as $icon => $label)
            <option value="{{ $icon }}" @selected($icon === 'bx-book-open')>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-8">
        <label for="new-card-image" class="form-label">Image (facultative)</label>
        <input id="new-card-image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="form-control">
        <span class="form-text">JPG, PNG ou WEBP, 4 Mo maximum. L’image remplace l’icône sur la page publique.</span>
      </div>
      <div class="col-md-6 d-flex align-items-center">
        <div class="form-check">
          <input id="new-card-active" name="is_active" type="checkbox" class="form-check-input" value="1" checked>
          <label for="new-card-active" class="form-check-label">Publier cette lecture immédiatement</label>
        </div>
      </div>
      <div class="col-md-6 text-md-end">
        <button type="submit" class="btn btn-outline-primary">
          <i class="icon-base bx bx-plus me-1"></i>Ajouter la lecture
        </button>
      </div>
    </form>
  </div>
</section>
@endsection
