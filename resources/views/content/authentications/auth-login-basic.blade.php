@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', 'Connexion')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <!-- Register -->
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                            <span class="app-brand-text demo text-heading fw-bold">{{ $siteSettings->name }}</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1">Connexion à {{ $siteSettings->name }}</h4>
                    <p class="mb-6">Connectez-vous pour accéder à l’administration.</p>

                    @if ($errors->any())
                    <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form id="formAuthentication" class="mb-6" action="{{ route('auth.authenticate') }}" method="POST">
                        @csrf
                        <div class="mb-6">
                            <label for="identifier" class="form-label">Identifiant</label>
                            <input type="text" class="form-control" id="identifier" name="identifier" value="{{ old('identifier') }}" placeholder="Nom d’utilisateur ou e-mail" autocomplete="username" autofocus required />
                        </div>
                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password">Mot de passe</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" placeholder="Votre mot de passe" autocomplete="current-password" required />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="mb-8">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="remember-me" name="remember" value="1" @checked(old('remember')) />
                                <label class="form-check-label" for="remember-me">Se souvenir de moi</label>
                            </div>
                        </div>
                        <div class="mb-6">
                            <button class="btn btn-primary d-grid w-100" type="submit">Se connecter</button>
                        </div>
                    </form>

                    <p class="text-center mb-0">
                        Pas encore de profil ?
                        <a href="{{ route('auth-register-basic') }}">Créer un compte utilisateur</a>
                    </p>
                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>
</div>
@endsection
