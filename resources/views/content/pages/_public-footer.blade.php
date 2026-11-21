<footer class="synex-public-footer">
  <div class="container-xxl px-4">
    <div class="row g-4 py-4 py-lg-5">
      <div class="col-lg-5">
        <a href="{{ route('home') }}" class="synex-public-footer-brand">
          <span class="synex-public-footer-logo" aria-hidden="true">@include('_partials.macros', ['width' => 38, 'variant' => 'dore'])</span>
          <span>
            <strong>{{ $siteSettings->name }}</strong>
            <small>Suivi &amp; Analyse des Conflits</small>
          </span>
        </a>
        <p class="synex-public-footer-description">
          Des données et des analyses pour mieux comprendre les dynamiques territoriales au Tchad.
        </p>
      </div>

      <nav class="col-6 col-lg-3" aria-label="Liens de pied de page">
        <h2>Explorer</h2>
        <a href="{{ route('home') }}">Accueil</a>
        <a href="{{ route('dashboard-analytics') }}">Tableau de bord</a>
        <a href="{{ route('veille') }}">Veille</a>
        <a href="{{ route('program-news.index') }}">Actualités du programme</a>
      </nav>

      <div class="col-6 col-lg-4">
        <h2>Contact</h2>
        @if ($siteSettings->address)<p>{{ $siteSettings->address }}</p>@endif
        @if ($siteSettings->location)<p>{{ $siteSettings->location }}</p>@endif
        @if ($siteSettings->contact_phone)<a href="tel:{{ $siteSettings->contact_phone }}">{{ $siteSettings->contact_phone }}</a>@endif
        @if ($siteSettings->contact_email)<a href="mailto:{{ $siteSettings->contact_email }}">{{ $siteSettings->contact_email }}</a>@endif
      </div>
    </div>

    <div class="synex-public-footer-bottom">
      <span>© {{ date('Y') }} {{ $siteSettings->name }}. Tous droits réservés.</span>
      <span>Suivi &amp; Analyse des Conflits · Tchad</span>
    </div>
  </div>
</footer>
