@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', $initiative->exists ? 'Modifier une initiative — Prévention' : 'Ajouter une initiative — Prévention')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
  <div>
    <h4 class="mb-1 fw-bold">{{ $initiative->exists ? 'Modifier une initiative' : 'Ajouter une initiative' }}</h4>
    <p class="text-muted mb-0">Renseignez uniquement des éléments vérifiés et autorisés à la publication.</p>
  </div>
  <a href="{{ route('admin.prevention.index') }}#prevention-initiatives" class="btn btn-outline-secondary"><i class="icon-base bx bx-arrow-back me-1"></i>Retour à la prévention</a>
</div>
@if ($errors->any())
  <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<section class="card">
  <div class="card-body">
    <form method="POST" action="{{ $initiative->exists ? route('admin.prevention.initiatives.update', $initiative) : route('admin.prevention.initiatives.store') }}" class="row g-3">
      @csrf
      @if ($initiative->exists) @method('PUT') @endif
      <div class="col-12">
        <label for="prevention-initiative-title" class="form-label">Titre</label>
        <input id="prevention-initiative-title" name="title" class="form-control" maxlength="180" value="{{ old('title', $initiative->title) }}" required>
      </div>
      <div class="col-md-6">
        <label for="prevention-initiative-type" class="form-label">Type d’action</label>
        <select id="prevention-initiative-type" name="type" class="form-select" required>
          @foreach ($types as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $initiative->type) === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-6">
        <label for="prevention-initiative-date" class="form-label">Date documentée</label>
        <input id="prevention-initiative-date" type="date" name="initiative_date" class="form-control" value="{{ old('initiative_date', $initiative->initiative_date?->toDateString()) }}">
      </div>
      <div class="col-md-6">
        <label for="prevention-initiative-province" class="form-label">Province</label>
        <input id="prevention-initiative-province" name="province" class="form-control" maxlength="100" value="{{ old('province', $initiative->province) }}" required>
      </div>
      <div class="col-md-6">
        <label for="prevention-initiative-location" class="form-label">Localité ou département</label>
        <input id="prevention-initiative-location" name="location" class="form-control" maxlength="180" value="{{ old('location', $initiative->location) }}">
      </div>
      <div class="col-md-6">
        <label for="prevention-initiative-source" class="form-label">Source</label>
        <input id="prevention-initiative-source" name="source" class="form-control" maxlength="180" value="{{ old('source', $initiative->source) }}">
      </div>
      <div class="col-md-6">
        <label for="prevention-initiative-records" class="form-label">Nombre d’enregistrements regroupés</label>
        <input id="prevention-initiative-records" type="number" min="1" max="10000" name="records" class="form-control" value="{{ old('records', $initiative->records ?? 1) }}" required>
      </div>
      <div class="col-12">
        <label for="prevention-initiative-note" class="form-label">Note de contexte (facultative)</label>
        <textarea id="prevention-initiative-note" name="note" rows="3" class="form-control" maxlength="2000">{{ old('note', $initiative->note) }}</textarea>
      </div>
      <div class="col-12">
        <div class="form-check">
          <input id="prevention-initiative-published" name="is_published" type="checkbox" class="form-check-input" value="1" @checked(old('_token') ? old('is_published') : $initiative->is_published)>
          <label for="prevention-initiative-published" class="form-check-label">Publier cette initiative sur la page Prévention</label>
        </div>
      </div>
      <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('admin.prevention.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>{{ $initiative->exists ? 'Enregistrer les modifications' : 'Créer l’initiative' }}</button>
      </div>
    </form>
  </div>
</section>
@endsection
