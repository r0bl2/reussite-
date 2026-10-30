@extends('layouts.admin')

@section('title', 'Résumés')
@section('page_title', 'Résumés')

@php $typeLabels = ['college' => 'Collège', 'lycee_moderne' => 'Lycée moderne', 'lycee_technique' => 'Lycée technique']; @endphp

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Rechercher un titre">
            <select name="subject_id" class="form-select form-select-sm w-auto">
                <option value="">Toutes les matières</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}" @selected(request('subject_id') == $s->id)>{{ $s->name }} ({{ $typeLabels[$s->type_lycee] ?? '—' }})</option>
                @endforeach
            </select>
            <input type="text" name="level" value="{{ request('level') }}" class="form-control form-control-sm w-auto" placeholder="Niveau">
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">Tous statuts</option>
                <option value="active" @selected(request('status') === 'active')>Actifs</option>
                <option value="inactive" @selected(request('status') === 'inactive')>En attente / inactifs</option>
            </select>
            <button class="btn btn-sm btn-primary">Filtrer</button>
            <a href="{{ route('summaries.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>

    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Titre</th><th>Matière</th><th>Niveau / Classe</th><th>Auteur</th><th>Statut</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($summaries as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->title }}</td>
                    <td>{{ $s->subject->name ?? '-' }}</td>
                    <td>{{ $s->level ?? '-' }} / {{ $s->class ?? '-' }}</td>
                    <td>{{ $s->teacher?->user?->username ?? 'Administration' }}</td>
                    <td><span class="badge {{ $s->active ? 'text-bg-success' : 'text-bg-warning' }}">{{ $s->active ? 'Actif' : 'En attente' }}</span></td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="{{ route('summaries.toggle', $s) }}" class="d-inline">
                            @csrf @method('PUT')
                            <button class="btn btn-sm {{ $s->active ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $s->active ? 'Désactiver' : 'Valider' }}</button>
                        </form>
                        <a href="{{ route('summaries.download', $s) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger"><i class="bi bi-download"></i></a>
                        <a href="{{ route('summaries.edit', $s) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('summaries.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Supprimer ce résumé ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun résumé.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $summaries->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
