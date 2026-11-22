@php($activePage = $activePage ?? '')

<nav class="navbar navbar-expand-xl navbar-light bg-white shadow-sm sticky-top synex-navbar" aria-label="Navigation principale">
  <div class="container-fluid px-4">
    <a class="navbar-brand d-flex align-items-center gap-2 me-4" href="{{ route('home') }}">
      @if ($siteSettings->logo_path)
        <img src="{{ asset('storage/'.$siteSettings->logo_path) }}" alt="" style="width: 35px; height: 35px; object-fit: contain">
      @else
        <span class="d-inline-flex" aria-hidden="true">@include('_partials.macros', ['width' => 35, 'variant' => 'dore'])</span>
      @endif
      <span class="fw-bold text-dark fs-4">{{ $siteSettings->name }}</span>
      <span class="text-muted small border-start ps-3 ms-2 d-none d-xxl-inline">Suivi &amp; Analyse des Conflits</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#synexNavbar" aria-controls="synexNavbar" aria-expanded="false" aria-label="Ouvrir le menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="synexNavbar">
      <ul class="navbar-nav mx-auto align-items-xl-center gap-xl-2">
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'home' ? 'active' : '' }}" href="{{ route('home') }}" @if ($activePage === 'home') aria-current="page" @endif>Accueil</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'veille' ? 'active' : '' }}" href="{{ route('veille') }}" @if ($activePage === 'veille') aria-current="page" @endif>Veille</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'program-news' ? 'active' : '' }}" href="{{ route('program-news.index') }}" @if ($activePage === 'program-news') aria-current="page" @endif>Actualités du programme</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'prevention' ? 'active' : '' }}" href="{{ route('prevention') }}" @if ($activePage === 'prevention') aria-current="page" @endif>Prévention</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'care' ? 'active' : '' }}" href="{{ route('public.care.index') }}" @if ($activePage === 'care') aria-current="page" @endif>Prise en charge</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'contact' ? 'active' : '' }}" href="{{ route('contact') }}" @if ($activePage === 'contact') aria-current="page" @endif>Nous contacter</a>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-2 mt-3 mt-xl-0">
        <a href="{{ route('dashboard-analytics-two') }}" class="btn btn-primary d-flex align-items-center gap-2 px-3">
          <i class="icon-base bx bx-grid-alt" aria-hidden="true"></i>
          <span>Tableau de bord</span>
        </a>
        @guest
          <a href="{{ route('auth-login-basic') }}" class="btn btn-outline-primary d-flex align-items-center gap-2 px-3">
            <i class="icon-base bx bx-user" aria-hidden="true"></i>
            <span>Connexion</span>
          </a>
        @else
          <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('pages-account-settings-account') }}" class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center p-2" aria-label="Mon profil" title="Mon profil">
            <i class="icon-base bx bx-user" aria-hidden="true"></i>
          </a>
        @endguest
      </div>
    </div>
  </div>
</nav>
