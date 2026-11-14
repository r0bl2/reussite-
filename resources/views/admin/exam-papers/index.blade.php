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
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('exam-papers.show', $p) }}"><i class="bi bi-eye"></i> Voir détails</a></li>
                                @if($p->status !== 'approved')
                                    <li>
                                        <form method="POST" action="{{ route('exam-papers.approve', $p) }}">
                                            @csrf @method('PUT')
                                            <button class="dropdown-item text-success"><i class="bi bi-check-circle"></i> Valider</button>
                                        </form>
                                    </li>
                                @endif
                                @if($p->status !== 'rejected')
                                    <li>
                                        <form method="POST" action="{{ route('exam-papers.reject', $p) }}" onsubmit="var r = prompt('Motif du rejet :'); if (!r) { return false; } this.rejection_reason.value = r; return true;">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="rejection_reason">
                                            <button class="dropdown-item text-danger"><i class="bi bi-x-circle"></i> Rejeter</button>
                                        </form>
                                    </li>
                                @endif
                                <li><a class="dropdown-item" href="{{ route('exam-papers.download', $p) }}"><i class="bi bi-download"></i> Télécharger</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('exam-papers.destroy', $p) }}" onsubmit="return confirm('Supprimer cette épreuve ?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
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
