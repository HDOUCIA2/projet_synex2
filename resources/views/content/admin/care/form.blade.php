@extends('layouts/contentNavbarLayout')

@php($editing = $center->exists)

@section('html-lang', 'fr')
@section('title', $editing ? 'Modifier une structure' : 'Ajouter une structure')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1 fw-bold">{{ $editing ? 'Modifier la structure' : 'Ajouter une structure' }}</h4>
    <p class="text-muted mb-0">Ne renseignez que des informations institutionnelles vérifiées et autorisées à la publication.</p>
  </div>
  <a href="{{ route('admin.care.index') }}" class="btn btn-outline-secondary">Retour à l’administration</a>
</div>

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

<form method="POST" action="{{ $editing ? route('admin.care.update', $center) : route('admin.care.store') }}">
  @csrf
  @if ($editing)
    @method('PUT')
  @endif

  <div class="card mb-6">
    <div class="card-header"><h5 class="card-title mb-0 fw-bold">Informations sur la structure</h5></div>
    <div class="card-body row g-4">
      <div class="col-md-8">
        <label for="care-name" class="form-label">Nom officiel *</label>
        <input id="care-name" name="name" class="form-control" maxlength="180" value="{{ old('name', $center->name) }}" required>
      </div>
      <div class="col-md-4">
        <label for="care-type" class="form-label">Type de structure *</label>
        <input id="care-type" name="structure_type" class="form-control" maxlength="80" value="{{ old('structure_type', $center->structure_type) }}" placeholder="Ex. : CISM, relais" required>
      </div>
      <div class="col-12">
        <label for="care-description" class="form-label">Présentation</label>
        <textarea id="care-description" name="description" rows="3" class="form-control" maxlength="3000">{{ old('description', $center->description) }}</textarea>
      </div>
      <div class="col-md-4">
        <label for="care-province" class="form-label">Province</label>
        <input id="care-province" name="province" class="form-control" maxlength="100" value="{{ old('province', $center->province) }}">
      </div>
      <div class="col-md-4">
        <label for="care-department" class="form-label">Département</label>
        <input id="care-department" name="department" class="form-control" maxlength="100" value="{{ old('department', $center->department) }}">
      </div>
      <div class="col-md-4">
        <label for="care-locality" class="form-label">Localité</label>
        <input id="care-locality" name="locality" class="form-control" maxlength="120" value="{{ old('locality', $center->locality) }}">
      </div>
      <div class="col-12">
        <label for="care-address" class="form-label">Adresse publique</label>
        <input id="care-address" name="address" class="form-control" maxlength="255" value="{{ old('address', $center->address) }}">
      </div>
      <div class="col-md-6">
        <label for="care-phone" class="form-label">Téléphone institutionnel</label>
        <input id="care-phone" name="phone" type="tel" class="form-control" maxlength="60" value="{{ old('phone', $center->phone) }}">
      </div>
      <div class="col-md-6">
        <label for="care-email" class="form-label">Courriel public</label>
        <input id="care-email" name="email" type="email" class="form-control" maxlength="180" value="{{ old('email', $center->email) }}">
      </div>
      <div class="col-md-6">
        <label for="care-hours" class="form-label">Horaires confirmés</label>
        <input id="care-hours" name="opening_hours" class="form-control" maxlength="180" value="{{ old('opening_hours', $center->opening_hours) }}">
      </div>
      <div class="col-md-6 d-flex align-items-end">
        <div class="form-check mb-2">
          <input id="care-free" name="is_free" type="checkbox" class="form-check-input" value="1" @checked(old('is_free', $center->is_free))>
          <label for="care-free" class="form-check-label">Gratuité confirmée par la structure</label>
        </div>
      </div>
      <div class="col-12">
        <label for="care-confidentiality" class="form-label">Note publique sur la confidentialité (facultative)</label>
        <textarea id="care-confidentiality" name="confidentiality_note" rows="2" class="form-control" maxlength="1000">{{ old('confidentiality_note', $center->confidentiality_note) }}</textarea>
        <div class="form-text">N’inscrivez aucun nom, témoignage ou renseignement sur une personne prise en charge.</div>
      </div>
    </div>
  </div>

  <div class="card mb-6">
    <div class="card-header"><h5 class="card-title mb-0 fw-bold">Services associés</h5></div>
    <div class="card-body">
      @if ($services->isNotEmpty())
        <div class="row g-3">
          @foreach ($services as $service)
            <div class="col-md-6 col-xl-4">
              <div class="form-check">
                <input
                  id="care-service-{{ $service->id }}"
                  name="service_ids[]"
                  type="checkbox"
                  class="form-check-input"
                  value="{{ $service->id }}"
                  @checked(in_array($service->id, old('service_ids', $center->services->pluck('id')->all())))
                >
                <label for="care-service-{{ $service->id }}" class="form-check-label">
                  {{ $service->name }} @unless ($service->is_active)<span class="text-muted">(inactif)</span>@endunless
                </label>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <p class="text-muted mb-0">Ajoutez d’abord des domaines d’accompagnement dans l’administration de la page.</p>
      @endif
    </div>
  </div>

  <div class="card mb-6">
    <div class="card-header">
      <h5 class="card-title mb-1 fw-bold">Publication et confidentialité</h5>
      <p class="text-muted small mb-0">Les coordonnées ne sont publiques sur la carte qu’avec l’autorisation explicite ci-dessous.</p>
    </div>
    <div class="card-body row g-4">
      <div class="col-md-6">
        <label for="care-latitude" class="form-label">Latitude (entre 7,4 et 23,5)</label>
        <input id="care-latitude" name="latitude" type="number" step="any" min="7.4" max="23.5" class="form-control" value="{{ old('latitude', $center->latitude) }}">
      </div>
      <div class="col-md-6">
        <label for="care-longitude" class="form-label">Longitude (entre 13,4 et 24)</label>
        <input id="care-longitude" name="longitude" type="number" step="any" min="13.4" max="24" class="form-control" value="{{ old('longitude', $center->longitude) }}">
      </div>
      <div class="col-12 d-grid gap-3">
        <div class="form-check">
          <input id="care-show-map" name="show_on_public_map" type="checkbox" class="form-check-input" value="1" @checked(old('show_on_public_map', $center->show_on_public_map))>
          <label for="care-show-map" class="form-check-label">Afficher la localisation publique sur la carte (coordonnées requises)</label>
        </div>
        <div class="form-check">
          <input id="care-show-detail" name="show_public_detail" type="checkbox" class="form-check-input" value="1" @checked(old('show_public_detail', $center->show_public_detail))>
          <label for="care-show-detail" class="form-check-label">Autoriser une fiche publique détaillée</label>
        </div>
        <div class="form-check">
          <input id="care-published" name="is_published" type="checkbox" class="form-check-input" value="1" @checked(old('is_published', $center->is_published))>
          <label for="care-published" class="form-check-label">Publier cette structure sur la page publique</label>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2">
    <a href="{{ route('admin.care.index') }}" class="btn btn-label-secondary">Annuler</a>
    <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>{{ $editing ? 'Enregistrer la structure' : 'Créer la structure' }}</button>
  </div>
</form>
@endsection
