@extends('layouts.admin')

@section('title', 'Épreuves d\'examen')
@section('page_title', 'Épreuves d\'examen')

@php
    $labels = ['pending' => ['En attente', 'text-bg-warning'], 'approved' => ['Validée', 'text-bg-success'], 'rejected' => ['Rejetée', 'text-bg-danger']];
@endphp

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Rechercher un titre">
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">Tous statuts</option>
                @foreach($labels as $k => $l)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $l[0] }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Filtrer</button>
            <a href="{{ route('exam-papers.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Titre</th><th>Matière</th><th>Niveau</th><th>Auteur</th><th>Statut</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($papers as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->title }}</td>
                    <td>{{ $p->subject }}</td>
                    <td>{{ $p->level }}</td>
                    <td>{{ $p->author?->role === 'admin' ? 'Administration' : ($p->author->username ?? '-') }}</td>
                    <td><span class="badge {{ $labels[$p->status][1] ?? 'text-bg-secondary' }}">{{ $labels[$p->status][0] ?? $p->status }}</span></td>
                    <td>{{ $p->created_at?->format('d/m/Y') }}</td>
                    <td class="text-end text-nowrap">
                        @if($p->status !== 'approved')
                            <form method="POST" action="{{ route('exam-papers.approve', $p) }}" class="d-inline">
                                @csrf @method('PUT')
                                <button class="btn btn-sm btn-outline-success" title="Valider"><i class="bi bi-check-circle"></i></button>
                            </form>
                        @endif
                        @if($p->status !== 'rejected')
                            <form method="POST" action="{{ route('exam-papers.reject', $p) }}" class="d-inline" onsubmit="var r = prompt('Motif du rejet :'); if (!r) { return false; } this.rejection_reason.value = r; return true;">
                                @csrf @method('PUT')
                                <input type="hidden" name="rejection_reason">
                                <button class="btn btn-sm btn-outline-danger" title="Rejeter"><i class="bi bi-x-circle"></i></button>
                            </form>
                        @endif
                        <a href="{{ route('exam-papers.show', $p) }}" class="btn btn-sm btn-outline-info" title="Voir détails"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('exam-papers.download', $p) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger"><i class="bi bi-download"></i></a>
                        <form method="POST" action="{{ route('exam-papers.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Supprimer cette épreuve ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Aucune épreuve.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $papers->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
