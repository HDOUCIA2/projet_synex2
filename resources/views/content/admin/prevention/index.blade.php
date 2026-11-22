@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Administration — Prévention')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
  <div>
    <h4 class="mb-1 fw-bold">Gestion de la prévention</h4>
    <p class="text-muted mb-0">Pilotez les contenus, les indicateurs, les initiatives publiées et les documents de référence.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route('prevention') }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
      <i class="icon-base bx bx-link-external me-1"></i>Voir la page publique
    </a>
    <a href="{{ route('admin.prevention.initiatives.create') }}" class="btn btn-primary">
      <i class="icon-base bx bx-plus me-1"></i>Ajouter une initiative
    </a>
  </div>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif
@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <p class="fw-semibold mb-1">Vérifiez les informations signalées :</p>
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

<section class="card mb-5" id="prevention-page-content" aria-labelledby="prevention-page-content-title">
  <div class="card-header">
    <h5 id="prevention-page-content-title" class="card-title mb-1 fw-bold">Contenu et indicateurs publics</h5>
    <p class="text-muted small mb-0">Les totaux territoriaux doivent correspondre à la somme des quatre indicateurs d’action.</p>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.prevention.content') }}">
      @csrf
      @method('PUT')
      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Bandeau de la page</legend>
        <div class="row g-3">
          <div class="col-12">
            <label for="prevention-hero-title" class="form-label">Titre principal</label>
            <input id="prevention-hero-title" name="content[hero_title]" class="form-control" maxlength="180" value="{{ old('content.hero_title', $prevention['hero_title']) }}" required>
          </div>
          <div class="col-12">
            <label for="prevention-hero-description" class="form-label">Texte de présentation</label>
            <textarea id="prevention-hero-description" name="content[hero_description]" rows="2" class="form-control" maxlength="600" required>{{ old('content.hero_description', $prevention['hero_description']) }}</textarea>
          </div>
          <div class="col-md-6">
            <label for="prevention-source-from" class="form-label">Début de la période couverte</label>
            <input id="prevention-source-from" type="date" name="content[source][from]" class="form-control" value="{{ old('content.source.from', $prevention['source']['from']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="prevention-source-to" class="form-label">Fin de la période couverte</label>
            <input id="prevention-source-to" type="date" name="content[source][to]" class="form-control" value="{{ old('content.source.to', $prevention['source']['to']) }}" required>
          </div>
          <div class="col-12">
            <label for="prevention-source-note" class="form-label">Note méthodologique</label>
            <textarea id="prevention-source-note" name="content[source][note]" rows="3" class="form-control" maxlength="2000" required>{{ old('content.source.note', $prevention['source']['note']) }}</textarea>
          </div>
        </div>
      </fieldset>

      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Indicateurs de synthèse</legend>
        <p class="text-muted small">Ces chiffres sont des enregistrements issus de la source indiquée, pas nécessairement des initiatives uniques.</p>
        <div class="row g-3">
          @php($metricLabels = ['campaigns' => 'Campagnes de prévention', 'mediations' => 'Médiations', 'arbitrations' => 'Arbitrages', 'peace_agreements' => 'Accords de paix'])
          @foreach ($metricLabels as $key => $label)
            <div class="col-6 col-lg-3">
              <label for="prevention-metric-{{ $key }}" class="form-label">{{ $label }}</label>
              <input id="prevention-metric-{{ $key }}" type="number" min="0" max="1000000" name="content[metrics][{{ $key }}]" class="form-control" value="{{ old('content.metrics.'.$key, $prevention['metrics'][$key]) }}" required>
            </div>
          @endforeach
        </div>
      </fieldset>

      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Répartition par province</legend>
        <div class="row g-3">
          @foreach ($prevention['province_distribution'] as $index => $province)
            <div class="col-md-8">
              <label for="prevention-province-{{ $index }}" class="form-label">Province {{ $index + 1 }}</label>
              <input id="prevention-province-{{ $index }}" name="content[province_distribution][{{ $index }}][province]" class="form-control" maxlength="100" value="{{ old('content.province_distribution.'.$index.'.province', $province['province']) }}" required>
            </div>
            <div class="col-md-4">
              <label for="prevention-province-count-{{ $index }}" class="form-label">Enregistrements</label>
              <input id="prevention-province-count-{{ $index }}" type="number" min="0" max="1000000" name="content[province_distribution][{{ $index }}][count]" class="form-control" value="{{ old('content.province_distribution.'.$index.'.count', $province['count']) }}" required>
            </div>
          @endforeach
        </div>
      </fieldset>

      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Axes de prévention</legend>
        <div class="row g-4">
          @foreach ($prevention['axes'] as $index => $axis)
            <div class="col-lg-6">
              <div class="rounded border p-3 h-100">
                <h6 class="mb-3">Axe {{ $index + 1 }}</h6>
                <label for="prevention-axis-title-{{ $index }}" class="form-label">Titre</label>
                <input id="prevention-axis-title-{{ $index }}" name="content[axes][{{ $index }}][title]" class="form-control mb-3" maxlength="120" value="{{ old('content.axes.'.$index.'.title', $axis['title']) }}" required>
                <label for="prevention-axis-description-{{ $index }}" class="form-label">Description</label>
                <textarea id="prevention-axis-description-{{ $index }}" name="content[axes][{{ $index }}][description]" rows="2" class="form-control mb-3" maxlength="300" required>{{ old('content.axes.'.$index.'.description', $axis['description']) }}</textarea>
                <label for="prevention-axis-tag-{{ $index }}" class="form-label">Thématique</label>
                <input id="prevention-axis-tag-{{ $index }}" name="content[axes][{{ $index }}][tag]" class="form-control" maxlength="80" value="{{ old('content.axes.'.$index.'.tag', $axis['tag']) }}" required>
              </div>
            </div>
          @endforeach
        </div>
      </fieldset>

      <fieldset class="mb-4">
        <legend class="h6 fw-bold">Notre approche</legend>
        <label for="prevention-approach-title" class="form-label">Titre de la section</label>
        <input id="prevention-approach-title" name="content[approach_title]" class="form-control mb-3" maxlength="120" value="{{ old('content.approach_title', $prevention['approach_title']) }}" required>
        <div class="row g-3">
          @foreach ($prevention['approach_points'] as $index => $point)
            <div class="col-md-6">
              <label for="prevention-approach-point-{{ $index }}" class="form-label">Engagement {{ $index + 1 }}</label>
              <input id="prevention-approach-point-{{ $index }}" name="content[approach_points][{{ $index }}]" class="form-control" maxlength="180" value="{{ old('content.approach_points.'.$index, $point) }}" required>
            </div>
          @endforeach
        </div>
      </fieldset>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-save me-1"></i>Enregistrer le contenu</button>
      </div>
    </form>
  </div>
</section>

<section class="card mb-5" id="prevention-initiatives" aria-labelledby="prevention-initiatives-admin-title">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h5 id="prevention-initiatives-admin-title" class="card-title mb-1 fw-bold">Initiatives documentées</h5>
      <p class="text-muted small mb-0">Seules les initiatives publiées apparaissent sur la page publique.</p>
    </div>
    <a href="{{ route('admin.prevention.initiatives.create') }}" class="btn btn-outline-primary btn-sm"><i class="icon-base bx bx-plus me-1"></i>Ajouter une initiative</a>
  </div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>Initiative</th><th>Type</th><th>Localisation</th><th>Date</th><th>État</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      @forelse ($initiatives as $initiative)
        <tr>
          <td><strong>{{ $initiative->title }}</strong><div class="small text-muted">{{ $initiative->records }} enregistrement(s) · {{ $initiative->source ?: 'Source non renseignée' }}</div></td>
          <td>{{ $types[$initiative->type] }}</td>
          <td>{{ collect([$initiative->location, $initiative->province])->filter()->join(', ') }}</td>
          <td>{{ $initiative->initiative_date?->format('d/m/Y') ?: '—' }}</td>
          <td><span class="badge {{ $initiative->is_published ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $initiative->is_published ? 'Publiée' : 'Brouillon' }}</span></td>
          <td class="text-end text-nowrap">
            <a href="{{ route('admin.prevention.initiatives.edit', $initiative) }}" class="btn btn-sm btn-icon btn-text-primary" aria-label="Modifier {{ $initiative->title }}"><i class="icon-base bx bx-edit-alt"></i></a>
            <form method="POST" action="{{ route('admin.prevention.initiatives.destroy', $initiative) }}" class="d-inline" onsubmit="return confirm('Supprimer cette initiative ?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-icon btn-text-danger" aria-label="Supprimer {{ $initiative->title }}"><i class="icon-base bx bx-trash"></i></button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Aucune initiative enregistrée.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if ($initiatives->hasPages())
    <div class="card-footer">{{ $initiatives->links() }}</div>
  @endif
</section>

<section class="card" id="prevention-resources" aria-labelledby="prevention-resources-admin-title">
  <div class="card-header">
    <h5 id="prevention-resources-admin-title" class="card-title mb-1 fw-bold">Ressources documentaires</h5>
    <p class="text-muted small mb-0">Seuls les fichiers PDF validés et publiés sont téléchargeables publiquement.</p>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.prevention.resources.store') }}" enctype="multipart/form-data" class="row g-3 align-items-end mb-4">
      @csrf
      <div class="col-lg-4">
        <label for="prevention-resource-title" class="form-label">Titre du document</label>
        <input id="prevention-resource-title" name="title" class="form-control" maxlength="180" required>
      </div>
      <div class="col-lg-4">
        <label for="prevention-resource-description" class="form-label">Description (facultative)</label>
        <input id="prevention-resource-description" name="description" class="form-control" maxlength="500">
      </div>
      <div class="col-lg-4">
        <label for="prevention-resource-file" class="form-label">Fichier PDF (10 Mo maximum)</label>
        <input id="prevention-resource-file" name="file" type="file" accept=".pdf,application/pdf" class="form-control" required>
      </div>
      <div class="col-md-6 d-flex align-items-center">
        <div class="form-check">
          <input id="prevention-resource-published" name="is_published" type="checkbox" class="form-check-input" value="1" @checked(old('_token') ? old('is_published') : true)>
          <label for="prevention-resource-published" class="form-check-label">Publier immédiatement après validation</label>
        </div>
      </div>
      <div class="col-md-6 text-md-end">
        <button type="submit" class="btn btn-primary"><i class="icon-base bx bx-upload me-1"></i>Ajouter le document</button>
      </div>
    </form>

    @if ($resources->isNotEmpty())
      <div class="d-grid gap-3">
        @foreach ($resources as $resource)
          <div class="rounded border p-3">
            <form method="POST" action="{{ route('admin.prevention.resources.update', $resource) }}" enctype="multipart/form-data" class="row g-3 align-items-end">
              @csrf
              @method('PUT')
              <div class="col-lg-4">
                <label for="prevention-resource-title-{{ $resource->id }}" class="form-label">Titre</label>
                <input id="prevention-resource-title-{{ $resource->id }}" name="title" class="form-control" maxlength="180" value="{{ $resource->title }}" required>
              </div>
              <div class="col-lg-4">
                <label for="prevention-resource-description-{{ $resource->id }}" class="form-label">Description</label>
                <input id="prevention-resource-description-{{ $resource->id }}" name="description" class="form-control" maxlength="500" value="{{ $resource->description }}">
              </div>
              <div class="col-lg-4">
                <label for="prevention-resource-file-{{ $resource->id }}" class="form-label">Remplacer le PDF (facultatif)</label>
                <input id="prevention-resource-file-{{ $resource->id }}" name="file" type="file" accept=".pdf,application/pdf" class="form-control">
              </div>
              <div class="col-md-6 d-flex flex-wrap align-items-center gap-3">
                <div class="form-check">
                  <input id="prevention-resource-published-{{ $resource->id }}" name="is_published" type="checkbox" class="form-check-input" value="1" @checked(old('_token') ? old('is_published') : $resource->is_published)>
                  <label for="prevention-resource-published-{{ $resource->id }}" class="form-check-label">{{ $resource->is_published ? 'Publiée' : 'Brouillon' }}</label>
                </div>
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($resource->file_path) }}" target="_blank" rel="noopener noreferrer" class="small">Ouvrir le PDF</a>
              </div>
              <div class="col-md-6 d-flex justify-content-md-end gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="icon-base bx bx-save me-1"></i>Enregistrer</button>
              </div>
            </form>
            <form method="POST" action="{{ route('admin.prevention.resources.destroy', $resource) }}" class="mt-2" onsubmit="return confirm('Supprimer ce document ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
            </form>
          </div>
        @endforeach
      </div>
    @else
      <div class="alert alert-light border mb-0">Aucune ressource documentaire n’est encore enregistrée.</div>
    @endif
  </div>
</section>
@endsection
