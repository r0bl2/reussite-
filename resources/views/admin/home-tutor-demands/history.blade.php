@extends('layouts.admin')

@section('title', 'Historique des décisions')
@section('page_title', 'Historique des décisions')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">Toutes les décisions</option>
                <option value="approved" @selected(request('status') === 'approved')>Validées</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejetées</option>
            </select>
            <button class="btn btn-sm btn-primary">Filtrer</button>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>#</th><th>Nom</th><th>Téléphone</th><th>Décision</th><th>Commentaire</th><th>Date</th><th></th></tr></thead>
            <tbody>
            @forelse($demands as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->last_name }} {{ $d->first_name }}</td>
                    <td>{{ $d->phone_number }}</td>
                    <td><span class="badge {{ $d->status === 'approved' ? 'text-bg-success' : 'text-bg-danger' }}">{{ $d->status === 'approved' ? 'Validée' : 'Rejetée' }}</span></td>
                    <td>{{ $d->admin_comment ?? '-' }}</td>
                    <td>{{ $d->updated_at?->format('d/m/Y H:i') }}</td>
                    <td class="text-end"><a href="{{ route('home-tutor-demands.show', $d) }}" class="btn btn-sm btn-outline-secondary">Voir</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune décision.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $demands->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
