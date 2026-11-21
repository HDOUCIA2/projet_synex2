@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', 'Créer un profil')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <div class="app-brand justify-content-center mb-6">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                            <span class="app-brand-text demo text-heading fw-bold">{{ $siteSettings->name }}</span>
                        </a>
                    </div>

                    <h4 class="mb-1">Créer un profil {{ $siteSettings->name }}</h4>
                    <p class="mb-6">Inscrivez-vous pour accéder au tableau de bord comme utilisateur simple.</p>

                    @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form id="formAuthentication" class="mb-6" action="{{ route('auth.register') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label for="name" class="form-label">Nom complet</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus />
                        </div>
                        <div class="mb-6">
                            <label for="username" class="form-label">Identifiant</label>
                            <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}" autocomplete="username" required />
                        </div>
                        <div class="mb-6">
                            <label for="email" class="form-label">Adresse e-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required />
                        </div>
                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password">Mot de passe</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" autocomplete="new-password" minlength="12" required />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                            <div class="form-text">12 caractères minimum.</div>
                        </div>
                        <div class="mb-6">
                            <label class="form-label" for="password_confirmation">Confirmer le mot de passe</label>
                            <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" autocomplete="new-password" minlength="12" required />
                        </div>
                        <button class="btn btn-primary d-grid w-100" type="submit">Créer mon profil</button>
                    </form>

                    <p class="text-center mb-0">
                        Vous avez déjà un compte ?
                        <a href="{{ route('auth-login-basic') }}">Se connecter</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
