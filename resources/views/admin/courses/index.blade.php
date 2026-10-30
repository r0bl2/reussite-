@extends('layouts.admin')

@section('title', 'Cours')
@section('page_title', 'Cours')

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
            <a href="{{ route('courses.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>

    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Titre</th><th>Matière</th><th>Niveau / Classe</th><th>Auteur</th><th>Statut</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($courses as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->title }}</td>
                    <td>{{ $c->subject->name ?? '-' }}</td>
                    <td>{{ $c->level ?? '-' }} / {{ $c->class ?? '-' }}</td>
                    <td>{{ $c->teacher?->user?->username ?? 'Administration' }}</td>
                    <td><span class="badge {{ $c->active ? 'text-bg-success' : 'text-bg-warning' }}">{{ $c->active ? 'Actif' : 'En attente' }}</span></td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="{{ route('courses.toggle', $c) }}" class="d-inline">
                            @csrf @method('PUT')
                            <button class="btn btn-sm {{ $c->active ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $c->active ? 'Désactiver' : 'Valider' }}</button>
                        </form>
                        <a href="{{ route('courses.download', $c) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger"><i class="bi bi-download"></i></a>
                        <a href="{{ route('courses.edit', $c) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('courses.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Supprimer ce cours ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun cours.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $courses->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
