@extends('layouts.admin')

@section('title', 'Demandes de répétition')
@section('page_title', 'Demandes de répétition')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Nom ou prénom">
            <button class="btn btn-sm btn-primary">Rechercher</button>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>#</th><th>Nom</th><th>Téléphone</th><th>Ville</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($demands as $d)
                <tr>
                    <td>{{ $d->id }}</td>
                    <td>{{ $d->last_name }} {{ $d->first_name }}</td>
                    <td>{{ $d->phone_number }}</td>
                    <td>{{ $d->city }}</td>
                    <td>{{ $d->created_at?->format('d/m/Y') }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('home-tutor-demands.show', $d) }}" class="btn btn-sm btn-outline-primary">Examiner</a>
                        <form method="POST" action="{{ route('home-tutor-demands.validate', $d) }}" class="d-inline" onsubmit="return confirm('Valider cette demande ?')">
                            @csrf @method('PUT')
                            <button class="btn btn-sm btn-success">Valider</button>
                        </form>
                        <a href="{{ route('home-tutor-demands.show', $d) }}#rejet" class="btn btn-sm btn-danger">Rejeter</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucune demande en attente.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $demands->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
