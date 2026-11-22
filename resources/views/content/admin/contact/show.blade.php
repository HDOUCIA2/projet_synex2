@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Message de contact')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1">{{ $submission->subject }}</h4>
    <p class="text-muted mb-0">Reçu le {{ $submission->created_at->format('d/m/Y à H:i') }}</p>
  </div>
  <a href="{{ route('admin.contact.index') }}" class="btn btn-outline-secondary">Retour aux messages</a>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

<div class="card mb-4">
  <div class="card-body">
    <dl class="row mb-0">
      <dt class="col-sm-3">Nom</dt>
      <dd class="col-sm-9">{{ $submission->name }}</dd>
      <dt class="col-sm-3">Adresse e-mail</dt>
      <dd class="col-sm-9"><a href="mailto:{{ $submission->email }}">{{ $submission->email }}</a></dd>
      @if ($submission->phone)
        <dt class="col-sm-3">Téléphone</dt>
        <dd class="col-sm-9"><a href="tel:{{ $submission->phone }}">{{ $submission->phone }}</a></dd>
      @endif
      <dt class="col-sm-3">Message</dt>
      <dd class="col-sm-9"><div class="text-break" style="white-space: pre-wrap">{{ $submission->message }}</div></dd>
    </dl>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">Répondre par e-mail</h5>
    @if ($submission->replied_at)
      <small class="text-muted">Dernière réponse envoyée le {{ $submission->replied_at->format('d/m/Y à H:i') }}</small>
    @endif
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.contact.reply', $submission) }}">
      @csrf
      <div class="mb-3">
        <label for="contact-reply" class="form-label">Votre réponse</label>
        <textarea id="contact-reply" name="reply" rows="7" maxlength="10000" required class="form-control @error('reply') is-invalid @enderror">{{ old('reply') }}</textarea>
        @error('reply')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="icon-base bx bx-send me-1" aria-hidden="true"></i>Envoyer la réponse
      </button>
    </form>
  </div>
</div>

<form method="POST" action="{{ route('admin.contact.destroy', $submission) }}" onsubmit="return confirm('Supprimer définitivement ce message ?')">
  @csrf
  @method('DELETE')
  <button type="submit" class="btn btn-outline-danger">Supprimer le message</button>
</form>
@endsection
