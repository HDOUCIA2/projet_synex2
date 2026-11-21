@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Mon profil')

@section('content')
<div class="mb-6">
  <h4 class="mb-1">Mon profil</h4>
  <p class="text-muted mb-0">Modifiez vos informations de connexion et votre mot de passe.</p>
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
    <div class="mb-5">
      <span class="badge bg-label-primary">{{ auth()->user()->isAdmin() ? 'Administrateur' : 'Utilisateur' }}</span>
    </div>
    <form method="POST" action="{{ route('pages-account-settings-account.update') }}" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <div class="d-flex flex-wrap align-items-center gap-4 mb-5">
        <img
          src="{{ auth()->user()->avatar_path ? asset('storage/'.auth()->user()->avatar_path) : asset('assets/img/avatars/1.png') }}"
          alt="Photo de profil"
          class="rounded-circle"
          width="88"
          height="88"
          style="object-fit: cover">
        <div>
          <label for="avatar" class="form-label">Photo de profil (facultative)</label>
          <input id="avatar" name="avatar" type="file" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          <small class="text-muted">JPG, PNG ou WEBP — 2 Mo maximum. Sans photo, l’avatar par défaut est affiché.</small>
          @if (auth()->user()->avatar_path)
            <div class="form-check mt-2">
              <input id="remove_avatar" name="remove_avatar" type="checkbox" class="form-check-input" value="1">
              <label for="remove_avatar" class="form-check-label">Retirer ma photo</label>
            </div>
          @endif
        </div>
      </div>
      <div class="row g-4">
        <div class="col-md-6">
          <label for="name" class="form-label">Nom complet</label>
          <input id="name" name="name" type="text" class="form-control" value="{{ old('name', auth()->user()->name) }}" maxlength="255" required>
        </div>
        <div class="col-md-6">
          <label for="username" class="form-label">Identifiant</label>
          <input id="username" name="username" type="text" class="form-control" value="{{ old('username', auth()->user()->username) }}" minlength="3" maxlength="30" autocomplete="username" required>
        </div>
        <div class="col-12">
          <label for="email" class="form-label">Adresse e-mail</label>
          <input id="email" name="email" type="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" maxlength="255" autocomplete="email" required>
        </div>
      </div>

      <hr class="my-6">
      <h5 class="mb-1">Changer le mot de passe</h5>
      <p class="text-muted">Laissez ces champs vides pour conserver le mot de passe actuel.</p>
      <div class="row g-4">
        <div class="col-md-4">
          <label for="current_password" class="form-label">Mot de passe actuel</label>
          <input id="current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
        </div>
        <div class="col-md-4">
          <label for="password" class="form-label">Nouveau mot de passe</label>
          <input id="password" name="password" type="password" class="form-control" minlength="12" autocomplete="new-password">
        </div>
        <div class="col-md-4">
          <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
          <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" minlength="12" autocomplete="new-password">
        </div>
      </div>

      <div class="mt-5">
        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
      </div>
    </form>
  </div>
</div>
@endsection
