@php($activePage = $activePage ?? '')

<nav class="navbar navbar-expand-xl navbar-light bg-white shadow-sm sticky-top synex-navbar" aria-label="Navigation principale">
  <div class="container-fluid px-4">
    <a class="navbar-brand d-flex align-items-center gap-2 me-4" href="{{ route('home') }}">
      <span class="d-inline-flex" aria-hidden="true">@include('_partials.macros', ['width' => 35, 'variant' => 'dore'])</span>
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
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ $activePage === 'analyses' ? 'active' : '' }}" href="{{ route('dashboard-analytics') }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">Analyses</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('dashboard-analytics') }}">Analyses des conflits</a></li>
            <li><a class="dropdown-item" href="{{ route('dashboard-analytics-two') }}">Nouveau tableau de bord</a></li>
            <li><a class="dropdown-item" href="{{ route('dashboard-analytics') }}#synexEventsMap">Analyses territoriales</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'veille' ? 'active' : '' }}" href="{{ route('veille') }}" @if ($activePage === 'veille') aria-current="page" @endif>Veille</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ $activePage === 'program-news' ? 'active' : '' }}" href="{{ route('program-news.index') }}" @if ($activePage === 'program-news') aria-current="page" @endif>Actualités du programme</a>
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
          <a href="{{ route('pages-account-settings-account') }}" class="btn btn-outline-primary d-flex align-items-center gap-2 px-3">
            <i class="icon-base bx bx-user" aria-hidden="true"></i>
            <span>Mon profil</span>
          </a>
        @endguest
      </div>
    </div>
  </div>
</nav>
