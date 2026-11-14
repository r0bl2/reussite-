@extends('layouts.admin')

@section('title', 'Exercices')
@section('page_title', 'Exercices')

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
            @include('admin.partials.level-filter', ['name' => 'niveau'])
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">Tous statuts</option>
                <option value="active" @selected(request('status') === 'active')>Actifs</option>
                <option value="inactive" @selected(request('status') === 'inactive')>En attente</option>
            </select>
            <a href="{{ route('exercises.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Titre</th><th>Matière</th><th>Niveau / Classe</th><th>Auteur</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($exercises as $e)
                <tr>
                    <td>{{ $e->id }}</td>
                    <td>{{ $e->title }}</td>
                    <td>{{ $e->subject->name ?? '-' }}</td>
                    <td>{{ $e->niveau ?? '-' }} / {{ $e->classe ?? '-' }}</td>
                    <td>{{ $e->teacher?->role === 'admin' ? 'Administration' : ($e->teacher->username ?? '-') }}</td>
                    <td><span class="badge {{ $e->active ? 'text-bg-success' : 'text-bg-warning' }}">{{ $e->active ? 'Actif' : 'En attente' }}</span></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('exercises.show', $e) }}"><i class="bi bi-eye"></i> Voir détails</a></li>
                                <li>
                                    <form method="POST" action="{{ route('exercises.toggle', $e) }}">
                                        @csrf @method('PUT')
                                        <button class="dropdown-item"><i class="bi {{ $e->active ? 'bi-toggle-off' : 'bi-toggle-on' }}"></i> {{ $e->active ? 'Désactiver' : 'Activer' }}</button>
                                    </form>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('exercises.edit', $e) }}"><i class="bi bi-pencil"></i> Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('exercises.destroy', $e) }}" onsubmit="return confirm('Supprimer cet exercice ?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Aucun exercice.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $exercises->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
