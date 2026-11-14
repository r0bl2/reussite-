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
            @include('admin.partials.level-filter', ['name' => 'level'])
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">Tous statuts</option>
                <option value="active" @selected(request('status') === 'active')>Actifs</option>
                <option value="inactive" @selected(request('status') === 'inactive')>En attente</option>
            </select>
            <a href="{{ route('courses.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Titre</th><th>Matière</th><th>Niveau / Classe</th><th>Auteur</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($courses as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->title }}</td>
                    <td>{{ $c->subject->name ?? '-' }}</td>
                    <td>{{ $c->level ?? '-' }} / {{ $c->class ?? '-' }}</td>
                    <td>{{ $c->teacher?->user?->username ?? 'Administration' }}</td>
                    <td><span class="badge {{ $c->active ? 'text-bg-success' : 'text-bg-warning' }}">{{ $c->active ? 'Actif' : 'En attente' }}</span></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('courses.show', $c) }}"><i class="bi bi-eye"></i> Voir détails</a></li>
                                <li>
                                    <form method="POST" action="{{ route('courses.toggle', $c) }}">
                                        @csrf @method('PUT')
                                        <button class="dropdown-item"><i class="bi {{ $c->active ? 'bi-toggle-off' : 'bi-toggle-on' }}"></i> {{ $c->active ? 'Désactiver' : 'Activer' }}</button>
                                    </form>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('courses.download', $c) }}"><i class="bi bi-download"></i> Télécharger</a></li>
                                <li><a class="dropdown-item" href="{{ route('courses.edit', $c) }}"><i class="bi bi-pencil"></i> Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('courses.destroy', $c) }}" onsubmit="return confirm('Supprimer ce cours ?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">Aucun cours.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $courses->links('pagination::bootstrap-5') }}</div>
</div>
@endsection