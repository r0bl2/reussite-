@extends('layouts.admin')

@section('title', 'Gestion enseignant')
@section('page_title', 'Gestion enseignant')

@php $typeLabels = ['college' => 'Collège', 'lycee_moderne' => 'Lycée moderne', 'lycee_technique' => 'Lycée technique']; @endphp

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Identifiant, email ou téléphone">
            <select name="type_lycee" class="form-select form-select-sm w-auto">
                <option value="">Tous les types</option>
                @foreach($typeLabels as $k => $l)
                    <option value="{{ $k }}" @selected(request('type_lycee') === $k)>{{ $l }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Identifiant</th><th>Email</th><th>Téléphone</th><th>Type de lycée</th>
                    <th>Niveau / Classe</th><th>Matières</th><th>Cours</th><th>Résumés</th><th>Exercices</th><th>Inscrit le</th>
                </tr>
            </thead>
            <tbody>
            @forelse($teachers as $t)
                @php
                    $m = is_array($t->matieres) ? $t->matieres : (json_decode($t->matieres ?? '[]', true) ?: []);
                @endphp
                <tr>
                    <td>{{ $t->id }}</td>
                    <td>{{ $t->username }}</td>
                    <td>{{ $t->email ?: '-' }}</td>
                    <td>{{ $t->telephone ?: ($t->user_phone ?: '-') }}</td>
                    <td>{{ $typeLabels[$t->type_lycee] ?? ($t->type_lycee ?: '-') }}</td>
                    <td>{{ $t->level ?: '-' }} / {{ $t->class ?: '-' }}</td>
                    <td>{{ implode(', ', $m) ?: '-' }}</td>
                    <td>{{ $courses[$t->id] ?? 0 }}</td>
                    <td>{{ $summaries[$t->id] ?? 0 }}</td>
                    <td>{{ $exercises[$t->user_id] ?? 0 }}</td>
                    <td>{{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center text-muted">Aucun enseignant.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $teachers->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
