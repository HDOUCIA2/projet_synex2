@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Messages de contact')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1">Messages de contact</h4>
    <p class="text-muted mb-0">{{ $unreadCount }} message(s) non lu(s)</p>
  </div>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

<div class="card">
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Expéditeur</th>
          <th>Sujet</th>
          <th>Reçu le</th>
          <th>Statut</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($submissions as $submission)
          <tr>
            <td>
              <strong class="{{ $submission->read_at ? '' : 'text-primary' }}">{{ $submission->name }}</strong>
              <small class="d-block text-muted">{{ $submission->email }}</small>
            </td>
            <td>{{ $submission->subject }}</td>
            <td>{{ $submission->created_at->format('d/m/Y H:i') }}</td>
            <td>
              @if ($submission->replied_at)
                <span class="badge bg-label-success">Répondu</span>
              @elseif ($submission->read_at)
                <span class="badge bg-label-secondary">Lu</span>
              @else
                <span class="badge bg-label-warning">Nouveau</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('admin.contact.show', $submission) }}" class="btn btn-sm btn-outline-primary">
                Lire
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-5">Aucun message de contact pour le moment.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if ($submissions->hasPages())
    <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <span class="text-muted small">
        Affichage de {{ $submissions->firstItem() }} à {{ $submissions->lastItem() }} sur {{ $submissions->total() }} messages
      </span>
      {{ $submissions->links('pagination::bootstrap-5') }}
    </div>
  @endif
</div>
@endsection
