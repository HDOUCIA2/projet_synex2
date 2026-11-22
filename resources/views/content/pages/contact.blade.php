@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', 'Nous contacter | SYNEX')

@section('page-style')
  @vite(['resources/assets/css/public-site.css', 'resources/assets/css/contact.css'])
@endsection

@section('content')
<div class="synex-contact-page">
  @include('content.pages._public-navbar', ['activePage' => 'contact'])

  <main class="container-xxl synex-contact-container px-3 py-5">
    <nav aria-label="Fil d’Ariane" class="mb-4">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
        <li class="breadcrumb-item active" aria-current="page">Nous contacter</li>
      </ol>
    </nav>

    <section class="synex-contact-heading text-center mb-4">
      <span>Contactez-nous</span>
      <h1>Nous sommes à votre écoute</h1>
      <p>Posez-nous vos questions ou partagez-nous une demande concernant SYNEX.</p>
    </section>

    <div class="row g-4 justify-content-center">
      <div class="col-lg-4">
        <aside class="synex-contact-info h-100">
          <h2>Nos coordonnées</h2>
          @if ($siteSettings->address)
            <div class="synex-contact-info-item">
              <i class="icon-base bx bx-map" aria-hidden="true"></i>
              <div><span>Adresse</span><strong>{{ $siteSettings->address }}</strong></div>
            </div>
          @endif
          @if ($siteSettings->contact_email)
            <a class="synex-contact-info-item" href="mailto:{{ $siteSettings->contact_email }}">
              <i class="icon-base bx bx-envelope" aria-hidden="true"></i>
              <div><span>E-mail</span><strong>{{ $siteSettings->contact_email }}</strong></div>
            </a>
          @endif
          @if ($siteSettings->contact_phone)
            <a class="synex-contact-info-item" href="tel:{{ $siteSettings->contact_phone }}">
              <i class="icon-base bx bx-phone" aria-hidden="true"></i>
              <div><span>Téléphone</span><strong>{{ $siteSettings->contact_phone }}</strong></div>
            </a>
          @endif
        </aside>
      </div>

      <div class="col-lg-7">
        <section class="synex-contact-card">
          @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
          @endif
          @if (session('warning'))
            <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
          @endif

          <form method="POST" action="{{ route('contact.store') }}">
            @csrf
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="contact-name">Nom complet <span aria-hidden="true">*</span></label>
                <input id="contact-name" class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" maxlength="120" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="contact-email">Adresse e-mail <span aria-hidden="true">*</span></label>
                <input id="contact-email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" maxlength="255" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="contact-phone">Téléphone</label>
                <input id="contact-phone" class="form-control @error('phone') is-invalid @enderror" type="tel" name="phone" value="{{ old('phone') }}" maxlength="50">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label" for="contact-subject">Sujet <span aria-hidden="true">*</span></label>
                <input id="contact-subject" class="form-control @error('subject') is-invalid @enderror" type="text" name="subject" value="{{ old('subject') }}" maxlength="160" required>
                @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label" for="contact-message">Votre message <span aria-hidden="true">*</span></label>
                <textarea id="contact-message" class="form-control @error('message') is-invalid @enderror" name="message" rows="6" maxlength="5000" required>{{ old('message') }}</textarea>
                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                  <i class="icon-base bx bx-send me-1" aria-hidden="true"></i>
                  Envoyer le message
                </button>
              </div>
            </div>
          </form>
        </section>
      </div>
    </div>
  </main>
</div>
@endsection
