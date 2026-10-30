@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Rechercher (nom, email)">
            <button class="btn btn-sm btn-primary">Filtrer</button>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Nom d'utilisateur</th><th>Email</th><th>Téléphone</th>
                    <th>Type de lycée</th>
                    @if($teacher)<th>Matières</th>@else<th>Niveau / Classe</th>@endif
                    <th>Inscrit le</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->username }}</td>
                    <td>{{ $u->email ?? '-' }}</td>
                    <td>{{ $u->telephone ?? '-' }}</td>
                    <td>{{ $u->type_lycee ?? '-' }}</td>
                    @if($teacher)
                        <td>{{ is_array($u->matieres) ? implode(', ', $u->matieres) : ($u->matieres ?? '-') }}</td>
                    @else
                        <td>{{ $u->level ?? '-' }} / {{ $u->class ?? '-' }}</td>
                    @endif
                    <td>{{ $u->created_at?->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun résultat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $users->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
