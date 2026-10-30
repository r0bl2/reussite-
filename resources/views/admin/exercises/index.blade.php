@extends('layouts.admin')

@section('title', 'Exercices')
@section('page_title', 'Exercices & Corrections')

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
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">Tous statuts</option>
                <option value="active" @selected(request('status') === 'active')>Actifs</option>
                <option value="inactive" @selected(request('status') === 'inactive')>En attente / inactifs</option>
            </select>
            <button class="btn btn-sm btn-primary">Filtrer</button>
            <a href="{{ route('exercises.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Titre</th><th>Matière</th><th>Niveau / Classe</th><th>Documents</th><th>Statut</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($exercises as $e)
                <tr>
                    <td>{{ $e->id }}</td>
                    <td>{{ $e->title }}</td>
                    <td>{{ $e->subject->name ?? '-' }}</td>
                    <td>{{ $e->niveau ?? '-' }} / {{ $e->classe ?? '-' }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('exercises.download', [$e, 'file']) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-text"></i> Énoncé</a>
                        @if($e->correction_path)
                            <a href="{{ route('exercises.download', [$e, 'correction']) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-check2-square"></i> Corrigé</a>
                        @else
                            <span class="badge text-bg-light border text-muted">Sans corrigé</span>
                        @endif
                    </td>
                    <td><span class="badge {{ $e->active ? 'text-bg-success' : 'text-bg-warning' }}">{{ $e->active ? 'Actif' : 'En attente' }}</span></td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="{{ route('exercises.toggle', $e) }}" class="d-inline">
                            @csrf @method('PUT')
                            <button class="btn btn-sm {{ $e->active ? 'btn-outline-warning' : 'btn-outline-success' }}">{{ $e->active ? 'Désactiver' : 'Valider' }}</button>
                        </form>
                        <a href="{{ route('exercises.edit', $e) }}" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('exercises.destroy', $e) }}" class="d-inline" onsubmit="return confirm('Supprimer cet exercice ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun exercice.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $exercises->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
