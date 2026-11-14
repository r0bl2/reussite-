@extends('layouts.admin')

@section('title', 'Demandes de répétition')
@section('page_title', 'Demandes de répétition')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Nom ou prénom">
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Nom</th><th>Téléphone</th><th>Ville</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($demands as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->last_name }} {{ $d->first_name }}</td>
                    <td>{{ $d->phone_number }}</td>
                    <td>{{ $d->city }}</td>
                    <td>{{ $d->created_at?->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('home-tutor-demands.show', $d) }}"><i class="bi bi-eye"></i> Voir détails</a></li>
                                <li>
                                    <form method="POST" action="{{ route('home-tutor-demands.validate', $d) }}" onsubmit="return confirm('Valider cette demande ?')">
                                        @csrf @method('PUT')
                                        <button class="dropdown-item text-success"><i class="bi bi-check-circle"></i> Valider</button>
                                    </form>
                                </li>
                                <li><a class="dropdown-item text-danger" href="{{ route('home-tutor-demands.show', $d) }}#rejet"><i class="bi bi-x-circle"></i> Rejeter</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Aucune demande en attente.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $demands->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
