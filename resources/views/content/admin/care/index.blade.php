@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Administration — Prise en charge')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1 fw-bold">Prise en charge et orientation</h4>
    <p class="text-muted mb-0">Gérez le répertoire public, les services proposés et leur publication.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route('public.care.index') }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
      <i class="icon-base bx bx-link-external me-1"></i>Voir la page publique
    </a>
    <a href="{{ route('admin.care.create') }}" class="btn btn-primary">
      <i class="icon-base bx bx-plus me-1"></i>Ajouter une structure
    </a>
  </div>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

<section class="card mb-6" aria-labelledby="care-image-heading">
  <div class="card-header">
    <h5 id="care-image-heading" class="card-title mb-1 fw-bold">Image du guichet unique CISM</h5>
    <p class="text-muted small mb-0">Cette image apparaît sur la page publique « Prise en charge ». Une illustration est affichée par défaut si aucune photo n’est ajoutée.</p>
  </div>
  <div class="card-body row g-4 align-items-center">
    <div class="col-md-4">
      @if ($siteSettings->care_page_image_path)
        <img src="{{ asset('storage/'.$siteSettings->care_page_image_path) }}" alt="Image actuelle du guichet unique CISM" class="img-fluid rounded border" style="max-height: 180px; object-fit: cover">
      @else
        <div class="rounded border bg-light p-4 text-center text-muted">Aucune photo personnalisée</div>
      @endif
    </div>
    <div class="col-md-8">
      <form method="POST" action="{{ route('admin.care.image') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <label for="care-cism-image" class="form-label">Choisir une image (JPG, PNG ou WEBP, 5 Mo maximum)</label>
        <input id="care-cism-image" name="cism_image" type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        @if ($siteSettings->care_page_image_path)
          <div class="form-check mt-3">
            <input id="remove-cism-image" name="remove_cism_image" type="checkbox" class="form-check-input" value="1">
            <label for="remove-cism-image" class="form-check-label">Supprimer la photo et rétablir l’illustration</label>
          </div>
        @endif
        <button type="submit" class="btn btn-primary mt-3">Enregistrer l’image</button>
      </form>
    </div>
  </div>
</section>

<section class="card mb-6" id="care-page-content" aria-labelledby="care-page-content-heading">
  <div class="card-header">
    <h5 id="care-page-content-heading" class="card-title mb-1 fw-bold">Contenu de la page publique</h5>
    <p class="text-muted small mb-0">Modifiez ici les titres, textes, étapes, icônes et numéro affichés sur la page « Prise en charge ».</p>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.care.content') }}">
      @csrf
      @method('PUT')

      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Bandeau et guichet CISM</legend>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="care-hero-title" class="form-label">Titre du bandeau</label>
            <input id="care-hero-title" name="content[hero_title]" class="form-control" maxlength="120" value="{{ old('content.hero_title', $pageContent['hero_title']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-cism-title" class="form-label">Titre du guichet</label>
            <input id="care-cism-title" name="content[cism_title]" class="form-control" maxlength="120" value="{{ old('content.cism_title', $pageContent['cism_title']) }}" required>
          </div>
          <div class="col-12">
            <label for="care-hero-description" class="form-label">Texte du bandeau</label>
            <textarea id="care-hero-description" name="content[hero_description]" rows="2" class="form-control" maxlength="500" required>{{ old('content.hero_description', $pageContent['hero_description']) }}</textarea>
          </div>
          <div class="col-md-8">
            <label for="care-cism-description" class="form-label">Présentation du guichet</label>
            <textarea id="care-cism-description" name="content[cism_description]" rows="2" class="form-control" maxlength="500" required>{{ old('content.cism_description', $pageContent['cism_description']) }}</textarea>
          </div>
          <div class="col-md-4">
            <label for="care-cism-link" class="form-label">Texte du bouton</label>
            <input id="care-cism-link" name="content[cism_link]" class="form-control" maxlength="80" value="{{ old('content.cism_link', $pageContent['cism_link']) }}" required>
          </div>
          @foreach ($pageContent['cism_benefits'] as $index => $benefit)
            <div class="col-md-4">
              <label for="care-cism-benefit-{{ $index }}" class="form-label">Point clé {{ $index + 1 }}</label>
              <input id="care-cism-benefit-{{ $index }}" name="content[cism_benefits][{{ $index }}][text]" class="form-control" maxlength="120" value="{{ old('content.cism_benefits.'.$index.'.text', $benefit['text']) }}" required>
              <label for="care-cism-benefit-icon-{{ $index }}" class="form-label mt-2">Icône</label>
              <select id="care-cism-benefit-icon-{{ $index }}" name="content[cism_benefits][{{ $index }}][icon]" class="form-select" required>
                @foreach ($pageIcons as $icon => $label)
                  <option value="{{ $icon }}" @selected(old('content.cism_benefits.'.$index.'.icon', $benefit['icon']) === $icon)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          @endforeach
        </div>
      </fieldset>

      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Recherche, carte et alerte</legend>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="care-search-title" class="form-label">Titre de la recherche</label>
            <input id="care-search-title" name="content[search_title]" class="form-control" maxlength="120" value="{{ old('content.search_title', $pageContent['search_title']) }}" required>
          </div>
          <div class="col-md-3">
            <label for="care-province-label" class="form-label">Libellé province</label>
            <input id="care-province-label" name="content[province_label]" class="form-control" maxlength="80" value="{{ old('content.province_label', $pageContent['province_label']) }}" required>
          </div>
          <div class="col-md-3">
            <label for="care-department-label" class="form-label">Libellé département</label>
            <input id="care-department-label" name="content[department_label]" class="form-control" maxlength="80" value="{{ old('content.department_label', $pageContent['department_label']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-query-summary" class="form-label">Lien de recherche avancée</label>
            <input id="care-query-summary" name="content[query_summary]" class="form-control" maxlength="100" value="{{ old('content.query_summary', $pageContent['query_summary']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-query-placeholder" class="form-label">Exemple de recherche</label>
            <input id="care-query-placeholder" name="content[query_placeholder]" class="form-control" maxlength="120" value="{{ old('content.query_placeholder', $pageContent['query_placeholder']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-map-title" class="form-label">Légende de la carte</label>
            <input id="care-map-title" name="content[map_title]" class="form-control" maxlength="120" value="{{ old('content.map_title', $pageContent['map_title']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-search-button" class="form-label">Bouton de recherche</label>
            <input id="care-search-button" name="content[search_button]" class="form-control" maxlength="80" value="{{ old('content.search_button', $pageContent['search_button']) }}" required>
          </div>
          <div class="col-12">
            <label for="care-map-attribution" class="form-label">Mention de source de la carte</label>
            <input id="care-map-attribution" name="content[map_attribution]" class="form-control" maxlength="255" value="{{ old('content.map_attribution', $pageContent['map_attribution']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-alert-title" class="form-label">Titre de l’alerte</label>
            <input id="care-alert-title" name="content[alert_title]" class="form-control" maxlength="120" value="{{ old('content.alert_title', $pageContent['alert_title']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-assistance-phone" class="form-label">Numéro d’assistance</label>
            <input id="care-assistance-phone" name="content[assistance_phone]" class="form-control" maxlength="50" value="{{ old('content.assistance_phone', $pageContent['assistance_phone']) }}" required>
          </div>
          <div class="col-12">
            <label for="care-alert-description" class="form-label">Texte de l’alerte</label>
            <textarea id="care-alert-description" name="content[alert_description]" rows="2" class="form-control" maxlength="300" required>{{ old('content.alert_description', $pageContent['alert_description']) }}</textarea>
          </div>
          <div class="col-md-4">
            <label for="care-phone-label" class="form-label">Libellé du numéro</label>
            <input id="care-phone-label" name="content[phone_label]" class="form-control" maxlength="80" value="{{ old('content.phone_label', $pageContent['phone_label']) }}" required>
          </div>
          <div class="col-md-4">
            <label for="care-phone-action" class="form-label">Texte d’appel</label>
            <input id="care-phone-action" name="content[phone_action]" class="form-control" maxlength="100" value="{{ old('content.phone_action', $pageContent['phone_action']) }}" required>
          </div>
          <div class="col-md-4">
            <label for="care-safety-note" class="form-label">Note de confidentialité</label>
            <input id="care-safety-note" name="content[safety_note]" class="form-control" maxlength="200" value="{{ old('content.safety_note', $pageContent['safety_note']) }}" required>
          </div>
        </div>
      </fieldset>

      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Parcours « Comment ça marche ? »</legend>
        <div class="row g-3">
          <div class="col-12">
            <label for="care-process-title" class="form-label">Titre du parcours</label>
            <input id="care-process-title" name="content[process_title]" class="form-control" maxlength="120" value="{{ old('content.process_title', $pageContent['process_title']) }}" required>
          </div>
          @foreach ($pageContent['process_steps'] as $index => $step)
            <div class="col-md-4">
              <div class="border rounded p-3 h-100">
                <h6 class="fw-bold">Étape {{ $index + 1 }}</h6>
                <label for="care-process-title-{{ $index }}" class="form-label">Titre</label>
                <input id="care-process-title-{{ $index }}" name="content[process_steps][{{ $index }}][title]" class="form-control" maxlength="100" value="{{ old('content.process_steps.'.$index.'.title', $step['title']) }}" required>
                <label for="care-process-description-{{ $index }}" class="form-label mt-2">Description</label>
                <textarea id="care-process-description-{{ $index }}" name="content[process_steps][{{ $index }}][description]" rows="2" class="form-control" maxlength="180" required>{{ old('content.process_steps.'.$index.'.description', $step['description']) }}</textarea>
                <label for="care-process-icon-{{ $index }}" class="form-label mt-2">Icône</label>
                <select id="care-process-icon-{{ $index }}" name="content[process_steps][{{ $index }}][icon]" class="form-select" required>
                  @foreach ($pageIcons as $icon => $label)
                    <option value="{{ $icon }}" @selected(old('content.process_steps.'.$index.'.icon', $step['icon']) === $icon)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          @endforeach
        </div>
      </fieldset>

      <fieldset class="mb-5">
        <legend class="h6 fw-bold">Prévention et actualités</legend>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="care-prevention-title" class="form-label">Titre prévention</label>
            <input id="care-prevention-title" name="content[prevention_title]" class="form-control" maxlength="120" value="{{ old('content.prevention_title', $pageContent['prevention_title']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-news-title" class="form-label">Titre actualités</label>
            <input id="care-news-title" name="content[news_title]" class="form-control" maxlength="120" value="{{ old('content.news_title', $pageContent['news_title']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-prevention-description" class="form-label">Texte prévention</label>
            <textarea id="care-prevention-description" name="content[prevention_description]" rows="2" class="form-control" maxlength="300" required>{{ old('content.prevention_description', $pageContent['prevention_description']) }}</textarea>
          </div>
          <div class="col-md-6">
            <label for="care-news-description" class="form-label">Texte actualités</label>
            <textarea id="care-news-description" name="content[news_description]" rows="2" class="form-control" maxlength="300" required>{{ old('content.news_description', $pageContent['news_description']) }}</textarea>
          </div>
          @foreach ($pageContent['prevention_items'] as $index => $item)
            <div class="col-md-6">
              <label for="care-prevention-item-{{ $index }}" class="form-label">Action de prévention {{ $index + 1 }}</label>
              <input id="care-prevention-item-{{ $index }}" name="content[prevention_items][{{ $index }}]" class="form-control" maxlength="160" value="{{ old('content.prevention_items.'.$index, $item) }}" required>
            </div>
          @endforeach
          <div class="col-md-6">
            <label for="care-news-link" class="form-label">Texte du bouton Actualités</label>
            <input id="care-news-link" name="content[news_link]" class="form-control" maxlength="80" value="{{ old('content.news_link', $pageContent['news_link']) }}" required>
          </div>
        </div>
      </fieldset>

      <fieldset class="mb-4">
        <legend class="h6 fw-bold">Répertoire des structures et icônes des sections</legend>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="care-directory-title" class="form-label">Titre du répertoire</label>
            <input id="care-directory-title" name="content[directory_title]" class="form-control" maxlength="120" value="{{ old('content.directory_title', $pageContent['directory_title']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-directory-description" class="form-label">Description du répertoire</label>
            <input id="care-directory-description" name="content[directory_description]" class="form-control" maxlength="300" value="{{ old('content.directory_description', $pageContent['directory_description']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-directory-empty-title" class="form-label">Titre si aucun résultat</label>
            <input id="care-directory-empty-title" name="content[directory_empty_title]" class="form-control" maxlength="120" value="{{ old('content.directory_empty_title', $pageContent['directory_empty_title']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-directory-empty-description" class="form-label">Message si aucun résultat</label>
            <input id="care-directory-empty-description" name="content[directory_empty_description]" class="form-control" maxlength="300" value="{{ old('content.directory_empty_description', $pageContent['directory_empty_description']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-directory-results-label" class="form-label">Libellé des résultats</label>
            <input id="care-directory-results-label" name="content[directory_results_label]" class="form-control" maxlength="50" value="{{ old('content.directory_results_label', $pageContent['directory_results_label']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-center-detail-link" class="form-label">Bouton de fiche structure</label>
            <input id="care-center-detail-link" name="content[center_detail_link]" class="form-control" maxlength="80" value="{{ old('content.center_detail_link', $pageContent['center_detail_link']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-center-call-link" class="form-label">Bouton d’appel structure</label>
            <input id="care-center-call-link" name="content[center_call_link]" class="form-control" maxlength="80" value="{{ old('content.center_call_link', $pageContent['center_call_link']) }}" required>
          </div>
          <div class="col-md-6">
            <label for="care-method-note-title" class="form-label">Titre de la note de bas de page</label>
            <input id="care-method-note-title" name="content[method_note_title]" class="form-control" maxlength="80" value="{{ old('content.method_note_title', $pageContent['method_note_title']) }}" required>
          </div>
          <div class="col-12">
            <label for="care-method-note-description" class="form-label">Note de bas de page</label>
            <textarea id="care-method-note-description" name="content[method_note_description]" rows="2" class="form-control" maxlength="500" required>{{ old('content.method_note_description', $pageContent['method_note_description']) }}</textarea>
          </div>
          @foreach ($pageContent['section_icons'] as $section => $icon)
            <div class="col-md-3">
              <label for="care-section-icon-{{ $section }}" class="form-label">Icône : {{ $section }}</label>
              <select id="care-section-icon-{{ $section }}" name="content[section_icons][{{ $section }}]" class="form-select" required>
                @foreach ($pageIcons as $pageIcon => $label)
                  <option value="{{ $pageIcon }}" @selected(old('content.section_icons.'.$section, $icon) === $pageIcon)>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          @endforeach
        </div>
      </fieldset>

      <button type="submit" class="btn btn-primary">Enregistrer le contenu de la page</button>
    </form>
  </div>
</section>

<section class="card mb-6" aria-labelledby="care-centers-heading">
  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <h5 id="care-centers-heading" class="card-title mb-1 fw-bold">Structures</h5>
      <p class="text-muted small mb-0">Les structures ne sont visibles sur le site qu’après publication.</p>
    </div>
    <span class="badge bg-label-primary">{{ number_format($centers->total(), 0, ',', ' ') }}</span>
  </div>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Structure</th>
          <th>Localisation</th>
          <th>Services</th>
          <th>Publication</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($centers as $center)
          <tr>
            <td>
              <strong class="d-block">{{ $center->name }}</strong>
              <small class="text-muted">{{ $center->structure_type }}</small>
            </td>
            <td>{{ collect([$center->locality, $center->department, $center->province])->filter()->implode(', ') ?: 'Non renseignée' }}</td>
            <td>
              @forelse ($center->services as $service)
                <span class="badge bg-label-secondary mb-1">{{ $service->name }}</span>
              @empty
                <span class="text-muted small">Aucun service associé</span>
              @endforelse
            </td>
            <td>
              <span class="badge {{ $center->is_published ? 'bg-label-success' : 'bg-label-secondary' }}">
                {{ $center->is_published ? 'Publiée' : 'Brouillon' }}
              </span>
              @if ($center->is_published)
                <div class="small text-muted mt-1">
                  {{ $center->show_on_public_map ? 'Carte activée' : 'Carte masquée' }}
                  · {{ $center->show_public_detail ? 'Fiche activée' : 'Fiche masquée' }}
                </div>
              @endif
            </td>
            <td>
              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.care.edit', $center) }}" class="btn btn-sm btn-outline-primary" aria-label="Modifier {{ $center->name }}">
                  <i class="icon-base bx bx-edit-alt" aria-hidden="true"></i>
                </a>
                <form method="POST" action="{{ route('admin.care.publication', $center) }}">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn-sm {{ $center->is_published ? 'btn-outline-warning' : 'btn-outline-success' }}" aria-label="{{ $center->is_published ? 'Dépublier' : 'Publier' }} {{ $center->name }}">
                    <i class="icon-base bx {{ $center->is_published ? 'bx-hide' : 'bx-show' }}" aria-hidden="true"></i>
                  </button>
                </form>
                <form method="POST" action="{{ route('admin.care.destroy', $center) }}" onsubmit="return confirm('Supprimer cette structure et ses associations de services ?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Supprimer {{ $center->name }}">
                    <i class="icon-base bx bx-trash" aria-hidden="true"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="py-5 text-center text-muted">Aucune structure n’a encore été ajoutée.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if ($centers->hasPages())
    <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <span class="text-muted small">Affichage de {{ $centers->firstItem() }} à {{ $centers->lastItem() }} sur {{ $centers->total() }} structures</span>
      {{ $centers->links('pagination::bootstrap-5') }}
    </div>
  @endif
</section>

<section class="card mb-6" id="care-services" aria-labelledby="care-services-heading">
  <div class="card-header">
    <h5 id="care-services-heading" class="card-title mb-1 fw-bold">Domaines d’accompagnement</h5>
    <p class="text-muted small mb-0">Les catégories actives apparaissent sur la page publique. Les catégories associées à des structures peuvent être désactivées, mais pas supprimées.</p>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.care.services.store') }}" class="row g-3 align-items-end border-bottom pb-4 mb-4">
      @csrf
      <div class="col-lg-3">
        <label for="new-service-name" class="form-label">Nom du service</label>
        <input id="new-service-name" name="name" class="form-control" maxlength="120" required>
      </div>
      <div class="col-lg-4">
        <label for="new-service-description" class="form-label">Description</label>
        <input id="new-service-description" name="description" class="form-control" maxlength="1000">
      </div>
      <div class="col-lg-2">
        <label for="new-service-icon" class="form-label">Icône</label>
        <select id="new-service-icon" name="icon" class="form-select" required>
          @foreach ($serviceIcons as $icon => $label)
            <option value="{{ $icon }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-lg-1">
        <label for="new-service-order" class="form-label">Ordre</label>
        <input id="new-service-order" name="sort_order" type="number" min="0" max="999" value="0" class="form-control" required>
      </div>
      <div class="col-lg-2">
        <button type="submit" class="btn btn-primary w-100"><i class="icon-base bx bx-plus me-1"></i>Ajouter</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr><th>Icône</th><th>Nom et description</th><th>Ordre</th><th>Statut</th><th>Structures</th><th class="text-end">Actions</th></tr>
        </thead>
        <tbody>
          @forelse ($services as $service)
            <tr>
              <td><i class="icon-base bx {{ $service->icon }} fs-4 text-primary" aria-hidden="true"></i></td>
              <td>
                <form id="update-care-service-{{ $service->id }}" method="POST" action="{{ route('admin.care.services.update', $service) }}" class="row g-2">
                  @csrf
                  @method('PUT')
                  <div class="col-md-4">
                    <label class="visually-hidden" for="service-name-{{ $service->id }}">Nom</label>
                    <input id="service-name-{{ $service->id }}" name="name" class="form-control form-control-sm" value="{{ $service->name }}" maxlength="120" required>
                  </div>
                  <div class="col-md-5">
                    <label class="visually-hidden" for="service-description-{{ $service->id }}">Description</label>
                    <input id="service-description-{{ $service->id }}" name="description" class="form-control form-control-sm" value="{{ $service->description }}" maxlength="1000">
                  </div>
                  <div class="col-md-3">
                    <label class="visually-hidden" for="service-icon-{{ $service->id }}">Icône</label>
                    <select id="service-icon-{{ $service->id }}" name="icon" class="form-select form-select-sm" required>
                      @foreach ($serviceIcons as $icon => $label)
                        <option value="{{ $icon }}" @selected($service->icon === $icon)>{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                  <input type="hidden" name="sort_order" value="{{ $service->sort_order }}">
                  <input type="hidden" name="is_active" value="0">
                  <div class="form-check ms-2">
                    <input id="service-active-{{ $service->id }}" name="is_active" type="checkbox" class="form-check-input" value="1" @checked($service->is_active)>
                    <label for="service-active-{{ $service->id }}" class="form-check-label small">Actif sur le site</label>
                  </div>
                </form>
              </td>
              <td>
                <label class="visually-hidden" for="service-order-{{ $service->id }}">Ordre d’affichage</label>
                <input id="service-order-{{ $service->id }}" form="update-care-service-{{ $service->id }}" name="sort_order" type="number" min="0" max="999" class="form-control form-control-sm" value="{{ $service->sort_order }}" required>
              </td>
              <td><span class="badge {{ $service->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $service->is_active ? 'Actif' : 'Inactif' }}</span></td>
              <td>{{ $service->centers_count }}</td>
              <td>
                <div class="d-flex justify-content-end gap-2">
                  <button type="submit" form="update-care-service-{{ $service->id }}" class="btn btn-sm btn-outline-primary" aria-label="Enregistrer {{ $service->name }}">
                    <i class="icon-base bx bx-save" aria-hidden="true"></i>
                  </button>
                  @if ($service->centers_count === 0)
                    <form method="POST" action="{{ route('admin.care.services.destroy', $service) }}" onsubmit="return confirm('Supprimer ce service ?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Supprimer {{ $service->name }}">
                        <i class="icon-base bx bx-trash" aria-hidden="true"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="py-4 text-center text-muted">Aucun service n’a été ajouté.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection
