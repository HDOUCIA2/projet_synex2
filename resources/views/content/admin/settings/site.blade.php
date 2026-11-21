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

<div class="card">
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
          <label for="location" class="form-label">Localisation</label>
          <input id="location" name="location" type="text" class="form-control" value="{{ old('location', $settings->location) }}" maxlength="255" placeholder="Ville, province ou pays">
        </div>
      </div>
      <div class="mt-5">
        <button type="submit" class="btn btn-primary">Enregistrer les paramètres</button>
      </div>
    </form>
  </div>
</div>
@endsection
