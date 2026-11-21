<div class="d-flex justify-content-between align-items-center px-6 py-4">
  <span class="badge bg-label-primary">{{ number_format($events->total(), 0, ',', ' ') }} événement(s)</span>
</div>
<div class="table-responsive">
  <table class="table table-sm table-hover align-middle mb-0">
    <thead>
      <tr>
        <th>Année</th>
        <th>Type</th>
        <th>Province / lieu</th>
        <th>Saisi par</th>
        <th>Statut</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($events as $event)
        @php($isOwner = (int) $event->created_by_user_id === (int) auth()->id())
        <tr>
          <td>{{ $event->event_year }}</td>
          <td>
            <div class="fw-semibold">{{ $event->eventTypeRecord?->name ?? $event->event_type ?? '—' }}</div>
            <small class="text-muted">{{ $event->subEventRecord?->name ?? $event->sub_event ?? '' }}</small>
          </td>
          <td>
            <div>{{ $event->province ?? '—' }}</div>
            <small class="text-muted">{{ $event->locality ?? $event->commune ?? '' }}</small>
          </td>
          <td>
            @if (auth()->user()->isAdmin())
              {{ $event->creator?->name ?? 'Import CSV' }}
            @else
              {{ $isOwner ? ($event->creator?->name ?? auth()->user()->name) : 'Événement publié' }}
            @endif
          </td>
          <td>
            <span class="badge {{ $event->status === 'approved' ? 'bg-label-success' : ($event->status === 'rejected' ? 'bg-label-danger' : 'bg-label-warning') }}">
              {{ ['pending' => 'En attente', 'approved' => 'Publié', 'rejected' => 'Refusé'][$event->status] ?? $event->status }}
            </span>
            @if ($event->review_notes)
              <small class="d-block text-muted mt-1">{{ $event->review_notes }}</small>
            @endif
          </td>
          <td>
            <div class="events-actions">
              @if (auth()->user()->isAdmin())
                @if ($event->status !== 'approved')
                  <form method="POST" action="{{ route('admin.events.review', $event) }}" data-event-action>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-sm btn-outline-success" title="Publier" aria-label="Publier l’événement">
                      <i class="icon-base bx bx-show" aria-hidden="true"></i>
                      Publier
                    </button>
                  </form>
                @else
                  <form method="POST" action="{{ route('admin.events.review', $event) }}" data-event-action>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="pending">
                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Dépublier" aria-label="Dépublier l’événement">
                      <i class="icon-base bx bx-hide" aria-hidden="true"></i>
                      Dépublier
                    </button>
                  </form>
                @endif
              @endif
              @if (auth()->user()->isAdmin() || ($isOwner && $event->status !== 'approved'))
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-outline-primary" title="Modifier" aria-label="Modifier l’événement">
                  <i class="icon-base bx bx-edit-alt" aria-hidden="true"></i>
                  Modifier
                </a>
                <form method="POST" action="{{ route('admin.events.destroy', $event) }}" data-event-action data-confirm="Voulez-vous vraiment supprimer cet événement ?">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" aria-label="Supprimer l’événement">
                    <i class="icon-base bx bx-trash" aria-hidden="true"></i>
                    Supprimer
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-6">Aucun événement trouvé.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@if ($events->hasPages())
  <div class="card-footer events-pagination" aria-label="Pagination des événements">
    {{ $events->links('pagination::bootstrap-5') }}
  </div>
@endif
