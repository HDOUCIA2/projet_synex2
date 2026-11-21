@extends('layouts/contentNavbarLayout')

@section('html-lang', 'fr')
@section('title', $config['title'])

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-4 mb-6">
  <div>
    <h4 class="mb-1">Référentiels — {{ $config['title'] }}</h4>
    <p class="text-muted mb-0">Ces valeurs sont utilisées lors de la saisie et de l’import des événements.</p>
  </div>
</div>

<div class="d-flex flex-wrap gap-2 mb-6">
  @foreach ([
    'provinces' => 'Provinces',
    'event-types' => 'Types',
    'sub-events' => 'Sous-événements',
    'other-events' => 'Autres',
    'themes' => 'Thématiques',
    'subthemes' => 'Sous-thématiques',
    'actors' => 'Acteurs',
  ] as $key => $label)
    <a class="btn btn-sm {{ $catalog === $key ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('admin.references.index', $key) }}">{{ $label }}</a>
  @endforeach
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
    <h5 class="card-title mb-0">Ajouter une valeur</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="{{ route('admin.references.store', $catalog) }}" class="row g-4 align-items-end">
      @csrf
      @if (isset($config['parent']))
        <div class="col-md-4">
          <label for="parent_id" class="form-label">{{ $config['parent']['label'] }}</label>
          <select id="parent_id" name="{{ $config['parent']['field'] }}" class="form-select" required>
            <option value="">Choisir…</option>
            @foreach ($parentOptions as $parent)
              <option value="{{ $parent->id }}" @selected(old($config['parent']['field']) == $parent->id)>{{ $parent->name }}</option>
            @endforeach
          </select>
        </div>
      @endif
      <div class="col-md">
        <label for="new_value" class="form-label">{{ $config['label'] }}</label>
        <input id="new_value" name="{{ $config['field'] }}" type="text" class="form-control" value="{{ old($config['field']) }}" maxlength="{{ $catalog === 'other-events' ? 2000 : 255 }}" required>
      </div>
      <div class="col-md-auto">
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Valeurs existantes</h5>
    <span class="badge bg-label-primary">{{ number_format($items->total(), 0, ',', ' ') }}</span>
  </div>
  <div class="card-body">
    @forelse ($items as $item)
      <div class="border rounded p-3 mb-3">
        <div class="mb-3">
          <span class="badge {{ $item->is_active ? 'bg-label-success' : 'bg-label-danger' }}">
            {{ $item->is_active ? 'Actif' : 'Bloqué' }}
          </span>
        </div>
        <div class="d-flex flex-wrap gap-3 align-items-end">
          <form id="update-{{ $item->id }}" method="POST" action="{{ route('admin.references.update', [$catalog, $item->id]) }}" class="row g-3 align-items-end flex-grow-1">
            @csrf
            @method('PUT')
            @if (isset($config['parent']))
              <div class="col-md-4">
                <label for="parent-{{ $item->id }}" class="form-label">{{ $config['parent']['label'] }}</label>
                <select id="parent-{{ $item->id }}" name="{{ $config['parent']['field'] }}" class="form-select" required>
                  @foreach ($parentOptions as $parent)
                    <option value="{{ $parent->id }}" @selected($item->{$config['parent']['field']} == $parent->id)>{{ $parent->name }}</option>
                  @endforeach
                </select>
              </div>
            @endif
            <div class="col">
              <label for="value-{{ $item->id }}" class="form-label">{{ $config['label'] }}</label>
              <input id="value-{{ $item->id }}" name="{{ $config['field'] }}" type="text" class="form-control" value="{{ $item->{$config['field']} }}" maxlength="{{ $catalog === 'other-events' ? 2000 : 255 }}" required>
            </div>
            @if (isset($config['parent']))
              <button type="submit" class="btn btn-outline-primary">Enregistrer</button>
            @else
              <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">Enregistrer</button>
              </div>
            @endif
          </form>
          <form method="POST" action="{{ route('admin.references.destroy', [$catalog, $item->id]) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Supprimer</button>
          </form>
          <form method="POST" action="{{ route('admin.references.status', [$catalog, $item->id]) }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-warning">
              {{ $item->is_active ? 'Bloquer' : 'Réactiver' }}
            </button>
          </form>
        </div>
        @if (isset($config['parent']))
          <small class="text-muted">{{ $item->{$config['parent']['relation']}?->name }}</small>
        @endif
      </div>
    @empty
      <p class="text-muted mb-0">Aucune valeur enregistrée.</p>
    @endforelse
    {{ $items->links() }}
  </div>
</div>
@endsection
