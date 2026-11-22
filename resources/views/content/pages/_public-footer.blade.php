@php($footerContent = array_replace_recursive(config('synex-footer'), $siteSettings->footer_content ?? []))

<footer class="synex-public-footer">
  <div class="container-xxl px-4">
    <div class="row g-4 g-xl-5 py-5">
      <div class="col-12 col-lg-4">
        <a href="{{ route('home') }}" class="synex-public-footer-brand">
          @if ($siteSettings->logo_path)
            <img class="synex-public-footer-logo" src="{{ asset('storage/'.$siteSettings->logo_path) }}" alt="">
          @else
            <span class="synex-public-footer-logo" aria-hidden="true">@include('_partials.macros', ['width' => 42, 'variant' => 'dore'])</span>
          @endif
          <span>
            <strong>{{ $siteSettings->name }}</strong>
            <small>Suivi &amp; Analyse des Conflits</small>
          </span>
        </a>
        <p class="synex-public-footer-description">{{ $footerContent['description'] }}</p>
        @if ($footerSocialLinks->isNotEmpty())
          <div class="synex-public-footer-social" aria-label="Réseaux sociaux">
            @foreach ($footerSocialLinks as $link)
              <a href="{{ $link->href }}" aria-label="{{ $link->label }}" title="{{ $link->label }}" @if (str_starts_with($link->href, 'http')) target="_blank" rel="noopener noreferrer" @endif>
                <i class="icon-base bx {{ $link->icon }}" aria-hidden="true"></i>
              </a>
            @endforeach
          </div>
        @endif
      </div>

      @if ($footerUsefulLinks->isNotEmpty())
        <nav class="col-12 col-md-6 col-lg-4 synex-public-footer-links" aria-label="Liens utiles">
          <h2>Liens utiles</h2>
          @foreach ($footerUsefulLinks as $link)
            <a href="{{ $link->href }}">{{ $link->label }}</a>
          @endforeach
        </nav>
      @endif

      <div class="col-12 col-md-6 col-lg-4 synex-public-footer-location">
        <h2>Nous trouver</h2>
        <img src="{{ asset('assets/images/chad-regions.svg') }}" alt="Carte des provinces du Tchad avec des repères GPS">
        <p>{{ $siteSettings->location ?: 'Tchad' }}</p>
        @if ($siteSettings->contact_phone)
          <a class="synex-public-footer-contact" href="tel:{{ $siteSettings->contact_phone }}">{{ $siteSettings->contact_phone }}</a>
        @endif
        @if ($siteSettings->contact_email)
          <a class="synex-public-footer-contact" href="mailto:{{ $siteSettings->contact_email }}">{{ $siteSettings->contact_email }}</a>
        @endif
      </div>
    </div>

    <div class="synex-public-footer-bottom">
      <span>© {{ date('Y') }} {{ $siteSettings->name }}. {{ $footerContent['copyright_text'] }}</span>
      @if ($footerLegalLinks->isNotEmpty())
        <nav aria-label="Liens légaux">
          @foreach ($footerLegalLinks as $link)
            <a href="{{ $link->href }}" @if (str_starts_with($link->href, 'http')) target="_blank" rel="noopener noreferrer" @endif>{{ $link->label }}</a>
          @endforeach
        </nav>
      @endif
    </div>
  </div>
</footer>
