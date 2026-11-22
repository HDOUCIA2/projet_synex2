@extends('layouts/blankLayout')

@section('html-lang', 'fr')
@section('title', $careCenter->name.' — Prise en charge')

@section('page-style')
  @vite('resources/assets/css/prise-en-charge.css')
@endsection

@section('content')
<div class="synex-care-page">
  @include('content.pages._public-navbar', ['activePage' => 'care'])
  <main class="container-xxl care-content px-3 px-lg-4 py-4">
    <nav aria-label="Fil d’Ariane" class="mb-3">
      <ol class="breadcrumb breadcrumb-chevron mb-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('public.care.index') }}">Prise en charge</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $careCenter->name }}</li>
      </ol>
    </nav>
    <article class="card care-center-detail">
      <div class="care-section-heading">
        <i class="icon-base bx bx-building-house"></i>
        <div><span class="care-center-type">{{ $careCenter->structure_type }}</span><h1>{{ $careCenter->name }}</h1></div>
      </div>
      <div class="care-directory-body">
        @if ($careCenter->description)<p>{{ $careCenter->description }}</p>@endif
        <dl class="care-center-details">
          @if ($careCenter->address || $careCenter->locality || $careCenter->department || $careCenter->province)
            <dt>Adresse</dt><dd>{{ collect([$careCenter->address, $careCenter->locality, $careCenter->department, $careCenter->province])->filter()->implode(', ') }}</dd>
          @endif
          @if ($careCenter->phone)<dt>Téléphone</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $careCenter->phone) }}">{{ $careCenter->phone }}</a></dd>@endif
          @if ($careCenter->email)<dt>Courriel</dt><dd><a href="mailto:{{ $careCenter->email }}">{{ $careCenter->email }}</a></dd>@endif
          @if ($careCenter->opening_hours)<dt>Horaires</dt><dd>{{ $careCenter->opening_hours }}</dd>@endif
          @if ($careCenter->is_free)<dt>Coût</dt><dd>Services indiqués comme gratuits par la structure</dd>@endif
        </dl>
        @if ($careCenter->services->isNotEmpty())
          <h2>Services référencés</h2>
          <ul>@foreach ($careCenter->services as $service)<li>{{ $service->name }}</li>@endforeach</ul>
        @endif
        @if ($careCenter->confidentiality_note)<p class="care-disclaimer">{{ $careCenter->confidentiality_note }}</p>@endif
        <a href="{{ route('public.care.index') }}" class="btn btn-outline-primary btn-sm mt-3"><i class="icon-base bx bx-left-arrow-alt me-1"></i>Retour à la page Prise en charge</a>
      </div>
    </article>
  </main>
</div>
@endsection
