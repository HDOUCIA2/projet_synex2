@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', 'Gestion des actualités')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1">Actualités du programme</h4>
    <p class="text-muted mb-0">Créez, modifiez et gérez la publication des actualités.</p>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route('program-news.index') }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
      <i class="icon-base bx bx-link-external me-1"></i>Voir la page publique
    </a>
    <a href="{{ route('admin.program-news.create') }}" class="btn btn-primary">
      <i class="icon-base bx bx-plus me-1"></i>Nouvelle actualité
    </a>
  </div>
</div>

@if (session('status'))
  <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Actualité</th>
          <th>Classement</th>
          <th>Date de publication</th>
          <th>Statut</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse ($newsItems as $news)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('storage/'.$news->image_path) }}" alt="" class="rounded object-fit-cover" width="64" height="48">
                <div>
                  <strong class="d-block">{{ $news->title }}</strong>
                  <small class="text-muted">{{ \Illuminate\Support\Str::limit($news->summary, 90) }}</small>
                </div>
              </div>
            </td>
            <td>
              <span class="d-block">{{ $news->category ? \App\Models\ProgramNews::CATEGORIES[$news->category] ?? $news->category : 'Non classée' }}</span>
              <small class="text-muted">{{ $news->region ? \App\Models\ProgramNews::REGIONS[$news->region] ?? $news->region : 'Région non renseignée' }}</small>
            </td>
            <td>{{ $news->published_at->format('d/m/Y H:i') }}</td>
            <td>
              <span class="badge {{ $news->is_published ? 'bg-label-success' : 'bg-label-secondary' }}">
                {{ $news->is_published ? 'Publiée' : 'Dépubliée' }}
              </span>
            </td>
            <td>
              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.program-news.edit', $news) }}" class="btn btn-sm btn-outline-primary">
                  <i class="icon-base bx bx-edit-alt" aria-hidden="true"></i><span class="visually-hidden">Modifier {{ $news->title }}</span>
                </a>
                <form method="POST" action="{{ route('admin.program-news.publication', $news) }}">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn-sm {{ $news->is_published ? 'btn-outline-warning' : 'btn-outline-success' }}">
                    <i class="icon-base bx {{ $news->is_published ? 'bx-hide' : 'bx-show' }}" aria-hidden="true"></i>
                    <span class="visually-hidden">{{ $news->is_published ? 'Dépublier' : 'Publier' }} {{ $news->title }}</span>
                  </button>
                </form>
                <form method="POST" action="{{ route('admin.program-news.destroy', $news) }}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer cette actualité ?')">
                    <i class="icon-base bx bx-trash" aria-hidden="true"></i><span class="visually-hidden">Supprimer {{ $news->title }}</span>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-5">Aucune actualité n’a été créée.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if ($newsItems->hasPages())
    <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <span class="text-muted small">
        Affichage de {{ $newsItems->firstItem() }} à {{ $newsItems->lastItem() }} sur {{ $newsItems->total() }} actualités
      </span>
      {{ $newsItems->links('pagination::bootstrap-5') }}
    </div>
  @endif
</div>
@endsection
