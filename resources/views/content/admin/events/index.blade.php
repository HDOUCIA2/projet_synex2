@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Gestion des événements')

@section('page-style')
  <style>
    .events-filter-form {
      display: grid;
      grid-template-columns: minmax(16rem, 1fr) minmax(12rem, 15rem) auto;
      gap: 1rem;
      align-items: center;
    }

    .events-results {
      position: relative;
    }

    .events-results table {
      font-size: .875rem;
    }

    .events-results.is-loading {
      opacity: .55;
      pointer-events: none;
    }

    .events-results.is-loading::after {
      content: 'Chargement…';
      position: absolute;
      top: 50%;
      left: 50%;
      padding: .75rem 1rem;
      border-radius: .5rem;
      background: var(--bs-body-bg);
      box-shadow: 0 .25rem 1rem rgb(0 0 0 / 12%);
      transform: translate(-50%, -50%);
    }

    .events-actions {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      flex-wrap: wrap;
      gap: .375rem;
      min-width: 10rem;
    }

    .events-actions form {
      display: inline-flex;
      align-items: center;
      gap: .375rem;
      margin: 0;
    }

    .events-actions .btn {
      padding: .25rem .5rem;
      font-size: .75rem;
    }

    .events-pagination nav {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .75rem;
    }

    .events-pagination .pagination {
      justify-content: center;
      flex-wrap: wrap;
      gap: .25rem;
      margin-bottom: 0;
    }

    @media (max-width: 767.98px) {
      .events-filter-form {
        grid-template-columns: 1fr;
      }

      .events-filter-form .btn {
        width: 100%;
      }

      .events-actions {
        min-width: 7rem;
      }
    }
  </style>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mb-6">
  <div>
    <h4 class="mb-1">Gestion des événements</h4>
    <p class="text-muted mb-0">
      @if (auth()->user()->isAdmin())
        Vérifiez les contributions avant leur publication sur le tableau public.
      @else
        Consultez les événements publiés et suivez vos propres contributions.
      @endif
    </p>
    <span class="badge bg-label-{{ auth()->user()->isAdmin() ? 'primary' : 'secondary' }} mt-2">
      {{ auth()->user()->isAdmin() ? 'Compte administrateur' : 'Compte utilisateur' }}
    </span>
  </div>
  <div class="d-flex flex-wrap gap-2">
    @if (auth()->user()->isAdmin())
      <a href="{{ route('admin.references.index', 'provinces') }}" class="btn btn-outline-primary">
        <i class="icon-base bx bx-list-ul me-1"></i> Gérer les référentiels
      </a>
    @endif
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
      <i class="icon-base bx bx-plus me-1"></i> Saisir un événement
    </a>
  </div>
</div>

@if (!auth()->user()->isAdmin())
  <div class="alert alert-info" role="note">
    Avec un compte utilisateur, vous pouvez modifier ou supprimer vos contributions en attente ou refusées.
    La publication, la dépublication et la gestion des événements déjà publiés sont réservées à un administrateur.
  </div>
@endif

  <div id="events-pending-alert" class="alert alert-warning @if ($pendingCount < 1) d-none @endif" role="status" @if ($pendingCount < 1) hidden @endif>
    {{ $pendingCount }} événement(s) en attente de validation.
  </div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

<div id="events-feedback" class="alert d-none" role="status" aria-live="polite"></div>

@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif

<div class="card">
  <div class="card-header">
    <form method="GET" action="{{ route('admin.events.index') }}" class="events-filter-form" data-events-filter>
      <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Rechercher un événement, un lieu ou une province" aria-label="Rechercher">
      <select name="status" class="form-select" aria-label="Filtrer par statut">
        <option value="">Tous les statuts</option>
        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
        <option value="approved" @selected(request('status') === 'approved')>Publiés</option>
        <option value="rejected" @selected(request('status') === 'rejected')>Refusés</option>
      </select>
      <button class="btn btn-outline-primary" type="submit"><i class="icon-base bx bx-search me-1" aria-hidden="true"></i>Rechercher</button>
    </form>
  </div>
  <div id="events-results" class="events-results" aria-live="polite">
    @include('content.admin.events._results', ['events' => $events])
  </div>
</div>
@endsection

@section('page-script')
  @vite('resources/assets/js/admin-events.js')
@endsection
