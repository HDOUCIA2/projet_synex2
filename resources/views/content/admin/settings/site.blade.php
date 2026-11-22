@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Paramètres du site')

@section('content')
<div class="mb-6">
  <h4 class="mb-1">Paramètres du site</h4>
  <p class="text-muted mb-0">Ces informations sont utilisées sur le site public et dans son identité visuelle.</p>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@php($footerContent = old('footer_content', $footerContent))

<div class="card mb-6">
  <div class="card-body">
    <form method="POST" action="{{ route('admin.settings.site.update') }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="row g-4">
        <div class="col-md-6">
          <label for="name" class="form-label">Nom du site</label>
          <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $settings->name) }}" maxlength="120" required>
        </div>
        <div class="col-md-6">
          <label for="logo" class="form-label">Logo (JPG, PNG ou WEBP, 2 Mo maximum)</label>
          <input id="logo" name="logo" type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          @if ($settings->logo_path)
            <img class="mt-3 rounded border p-2" src="{{ asset('storage/'.$settings->logo_path) }}" alt="Logo actuel" style="max-width: 180px; max-height: 80px; object-fit: contain">
          @endif
        </div>
        <div class="col-12">
          <label for="address" class="form-label">Adresse</label>
          <input id="address" name="address" type="text" class="form-control" value="{{ old('address', $settings->address) }}" maxlength="500">
        </div>
        <div class="col-md-6">
          <label for="contact_email" class="form-label">E-mail de contact</label>
          <input id="contact_email" name="contact_email" type="email" class="form-control" value="{{ old('contact_email', $settings->contact_email) }}" maxlength="255">
        </div>
        <div class="col-md-6">
          <label for="contact_phone" class="form-label">Téléphone de contact</label>
          <input id="contact_phone" name="contact_phone" type="text" class="form-control" value="{{ old('contact_phone', $settings->contact_phone) }}" maxlength="50">
        </div>
        <div class="col-12">
          <label for="location" class="form-label">Localisation affichée sous la carte du Tchad</label>
          <input id="location" name="location" type="text" class="form-control" value="{{ old('location', $settings->location) }}" maxlength="255" placeholder="Ville, province ou pays">
        </div>
      </div>

      <hr class="my-5">
      <div class="mb-4">
        <h5 class="mb-1">Pied de page</h5>
        <p class="text-muted mb-0">La carte du Tchad est une image issue de la carte administrative déjà utilisée sur le site. Les liens du pied de page sont gérés ci-dessous.</p>
      </div>
      <div class="row g-4">
        <div class="col-12">
          <label for="footer-description" class="form-label">Présentation</label>
          <textarea id="footer-description" name="footer_content[description]" class="form-control" rows="3" maxlength="500" required>{{ $footerContent['description'] }}</textarea>
        </div>
        <div class="col-md-6">
          <label for="footer-copyright" class="form-label">Texte du copyright</label>
          <input id="footer-copyright" name="footer_content[copyright_text]" class="form-control" value="{{ $footerContent['copyright_text'] }}" maxlength="160" required>
        </div>
      </div>
      <div class="mt-5">
        <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
      </div>
    </form>
  </div>
</div>

<div class="card mb-6">
  <div class="card-header">
    <h5 class="mb-1">Liens utiles</h5>
    <p class="text-muted mb-0">Ces liens correspondent aux pages existantes du site. Vous pouvez les afficher ou les masquer.</p>
  </div>
  <div class="list-group list-group-flush">
    @foreach ($footerPages as $page)
      <form method="POST" action="{{ route('admin.settings.site.footer-pages.visibility', $page) }}" class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-3">
        @csrf
        @method('PATCH')
        <input type="hidden" name="is_visible" value="0">
        <div>
          <strong>{{ $page->label }}</strong>
          <div class="small text-muted">{{ route($page->route_name) }}</div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <label class="form-check-label d-flex align-items-center gap-2">
            <input class="form-check-input m-0" type="checkbox" name="is_visible" value="1" @checked($page->is_visible)>
            Afficher
          </label>
          <button type="submit" class="btn btn-sm btn-outline-primary">Enregistrer</button>
        </div>
      </form>
    @endforeach
  </div>
</div>

<div class="card mb-6">
  <div class="card-header">
    <h5 class="mb-1">Liens légaux</h5>
    <p class="text-muted mb-0">Ajoutez, modifiez ou supprimez les liens légaux enregistrés en base de données.</p>
  </div>
  <div class="card-body">
    @forelse ($footerLegalLinks as $link)
      <div class="row g-3 align-items-end mb-4">
        <form method="POST" action="{{ route('admin.settings.site.footer-links.update', $link) }}" class="col-md-10 row g-3">
          @csrf
          @method('PUT')
          <div class="col-md-5">
            <label for="legal-label-{{ $link->id }}" class="form-label">Libellé</label>
            <input id="legal-label-{{ $link->id }}" name="label" class="form-control" value="{{ $link->label }}" maxlength="100" required>
          </div>
          <div class="col-md-7">
            <label for="legal-url-{{ $link->id }}" class="form-label">Adresse de la page</label>
            <input id="legal-url-{{ $link->id }}" name="url" class="form-control" value="{{ $link->url }}" maxlength="500" placeholder="/page ou https://..." required>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-sm btn-outline-primary">Enregistrer</button>
          </div>
        </form>
        <form method="POST" action="{{ route('admin.settings.site.footer-links.destroy', $link) }}" class="col-md-2">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
        </form>
      </div>
      @if (!$loop->last)<hr>@endif
    @empty
      <p class="text-muted">Aucun lien légal n’est configuré.</p>
    @endforelse

    <hr class="my-4">
    <h6>Ajouter un lien légal</h6>
    <form method="POST" action="{{ route('admin.settings.site.footer-links.store') }}" class="row g-3 align-items-end">
      @csrf
      <input type="hidden" name="type" value="legal">
      <div class="col-md-5">
        <label for="new-legal-label" class="form-label">Libellé</label>
        <input id="new-legal-label" name="label" class="form-control" value="{{ old('label') }}" maxlength="100" required>
      </div>
      <div class="col-md-5">
        <label for="new-legal-url" class="form-label">Adresse de la page</label>
        <input id="new-legal-url" name="url" class="form-control" value="{{ old('url') }}" maxlength="500" placeholder="/page ou https://..." required>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-1">Réseaux sociaux</h5>
    <p class="text-muted mb-0">Seuls les réseaux que vous configurez ici apparaissent dans le pied de page.</p>
  </div>
  <div class="card-body">
    @forelse ($footerSocialLinks as $link)
      <div class="row g-3 align-items-end mb-4">
        <form method="POST" action="{{ route('admin.settings.site.footer-links.update', $link) }}" class="col-md-10 row g-3">
          @csrf
          @method('PUT')
          <div class="col-md-4">
            <label for="social-label-{{ $link->id }}" class="form-label">Réseau social</label>
            <input id="social-label-{{ $link->id }}" name="label" class="form-control" value="{{ $link->label }}" maxlength="100" required>
          </div>
          <div class="col-md-5">
            <label for="social-url-{{ $link->id }}" class="form-label">Adresse du profil</label>
            <input id="social-url-{{ $link->id }}" name="url" class="form-control" value="{{ $link->url }}" maxlength="500" required>
          </div>
          <div class="col-md-3">
            <label for="social-icon-{{ $link->id }}" class="form-label">Icône</label>
            <select id="social-icon-{{ $link->id }}" name="icon" class="form-select" required>
              @foreach (config('synex-footer.allowed_social_icons') as $icon)
                <option value="{{ $icon }}" @selected($link->icon === $icon)>{{ $icon }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-sm btn-outline-primary">Enregistrer</button>
          </div>
        </form>
        <form method="POST" action="{{ route('admin.settings.site.footer-links.destroy', $link) }}" class="col-md-2">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
        </form>
      </div>
      @if (!$loop->last)<hr>@endif
    @empty
      <p class="text-muted">Aucun réseau social n’est configuré.</p>
    @endforelse

    <hr class="my-4">
    <h6>Ajouter un réseau social</h6>
    <form method="POST" action="{{ route('admin.settings.site.footer-links.store') }}" class="row g-3 align-items-end">
      @csrf
      <input type="hidden" name="type" value="social">
      <div class="col-md-4">
        <label for="new-social-label" class="form-label">Réseau social</label>
        <input id="new-social-label" name="label" class="form-control" maxlength="100" required>
      </div>
      <div class="col-md-5">
        <label for="new-social-url" class="form-label">Adresse du profil</label>
        <input id="new-social-url" name="url" class="form-control" maxlength="500" placeholder="https://..." required>
      </div>
      <div class="col-md-2">
        <label for="new-social-icon" class="form-label">Icône</label>
        <select id="new-social-icon" name="icon" class="form-select" required>
          @foreach (config('synex-footer.allowed_social_icons') as $icon)
            <option value="{{ $icon }}">{{ $icon }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-1">
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>
@endsection
