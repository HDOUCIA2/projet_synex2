@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Créer un compte')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mb-6">
  <div>
    <h4 class="mb-1">Créer un compte</h4>
    <p class="text-muted mb-0">Ajoutez un utilisateur à l’espace d’administration Synex.</p>
  </div>
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
    <form method="POST" action="{{ route('admin.users.store') }}">
      @csrf
      <div class="row g-4">
        <div class="col-md-6">
          <label for="name" class="form-label">Nom complet</label>
          <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" autocomplete="name" required>
        </div>
        <div class="col-md-6">
          <label for="username" class="form-label">Identifiant</label>
          <input id="username" name="username" type="text" class="form-control" value="{{ old('username') }}" autocomplete="username" required>
          <div class="form-text">3 à 30 caractères : lettres, chiffres, tirets ou soulignés.</div>
        </div>
        <div class="col-md-6">
          <label for="email" class="form-label">Adresse e-mail</label>
          <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" autocomplete="email" required>
        </div>
        <div class="col-md-6">
          <label for="role" class="form-label">Type de compte</label>
          <select id="role" name="role" class="form-select" required>
            <option value="user" @selected(old('role', 'user') === 'user')>Utilisateur simple</option>
            <option value="admin" @selected(old('role') === 'admin')>Administrateur</option>
          </select>
        </div>
        <div class="col-md-6">
          <label for="password" class="form-label">Mot de passe</label>
          <input id="password" name="password" type="password" class="form-control" autocomplete="new-password" minlength="12" required>
          <div class="form-text">12 caractères minimum.</div>
        </div>
        <div class="col-md-6">
          <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
          <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" minlength="12" required>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Créer le compte</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
