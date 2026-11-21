@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Importer des événements')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mb-6">
  <div>
    <h4 class="mb-1">Importer des événements</h4>
    <p class="text-muted mb-0">Téléversez un CSV, vérifiez le bilan, puis confirmez l’ajout.</p>
  </div>
  <a href="{{ route('admin.import.template') }}" class="btn btn-outline-primary">
    <i class="icon-base bx bx-download me-1"></i> Télécharger le modèle CSV
  </a>
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

<div class="card mb-6">
  <div class="card-header">
    <h5 class="card-title mb-0">Étape 1 — Vérifier un fichier</h5>
  </div>
  <div class="card-body">
    <p>Le fichier doit respecter les colonnes du modèle et utiliser le point-virgule comme séparateur. Les lignes sans année ou sans province seront ignorées et comptées dans le bilan.</p>
    <form method="POST" action="{{ route('admin.import.preview') }}" enctype="multipart/form-data" class="row g-4 align-items-end">
      @csrf
      <div class="col-md-8">
        <label for="csv_file" class="form-label">Fichier CSV (20 Mo maximum)</label>
        <input id="csv_file" name="csv_file" type="file" class="form-control" accept=".csv,text/csv" required>
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary">Vérifier le fichier</button>
      </div>
    </form>
  </div>
</div>

@isset($previewOutput)
  <div class="card border-primary">
    <div class="card-header">
      <h5 class="card-title mb-0">Étape 2 — Confirmer l’import</h5>
    </div>
    <div class="card-body">
      <p class="mb-4">{{ $previewOutput }}</p>
      @if (auth()->user()->isAdmin())
        <p class="text-muted">Les événements seront ajoutés sans remplacer ceux déjà enregistrés. Le même fichier ne peut être importé deux fois.</p>
      @else
        <p class="text-muted">Les événements seront soumis à validation par un administrateur avant publication. Le fichier doit utiliser les valeurs de référentiel actives, déjà enregistrées par l’administration.</p>
      @endif
      <form method="POST" action="{{ route('admin.import.commit') }}" class="d-flex flex-wrap align-items-center gap-3">
        @csrf
        <input type="hidden" name="upload_id" value="{{ $uploadId }}">
        <div class="form-check">
          <input id="confirm_import" name="confirm_import" type="checkbox" class="form-check-input" value="1" required>
          <label for="confirm_import" class="form-check-label">Je confirme l’ajout des événements validés.</label>
        </div>
        <button type="submit" class="btn btn-primary">Importer maintenant</button>
      </form>
    </div>
  </div>
@endisset
@endsection
