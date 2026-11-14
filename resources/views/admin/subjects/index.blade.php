@extends('layouts.admin')

@section('title', 'Matières')
@section('page_title', 'Matières')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Rechercher">
            <select name="type_lycee" class="form-select form-select-sm w-auto">
                <option value="">Tous les types</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected(request('type_lycee') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ route('subjects.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>

    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>#</th><th>Nom</th><th>Type d'établissement</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($subjects as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->name }}</td>
                    <td>{{ $types[$s->type_lycee] ?? '—' }}</td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('subjects.edit', $s) }}"><i class="bi bi-pencil"></i> Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('subjects.destroy', $s) }}" onsubmit="return confirm('Supprimer cette matière ?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Aucune matière.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $subjects->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
