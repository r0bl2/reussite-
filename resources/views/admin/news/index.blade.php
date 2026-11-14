@extends('layouts.admin')

@section('title', 'Actualités')
@section('page_title', 'Actualités')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm w-auto" placeholder="Rechercher un titre">
            <a href="{{ route('news.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>#</th><th>Image</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($news as $n)
                <tr>
                    <td>{{ $n->id }}</td>
                    <td>@if($n->cover_image_url)<img src="{{ $n->cover_image_url }}" height="22" alt="">@endif</td>
                    <td>{{ $n->title }}</td>
                    <td>{{ $n->category ?? '-' }}</td>
                    <td><span class="badge {{ $n->is_published ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $n->is_published ? 'Publié' : 'Brouillon' }}</span></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('news.show', $n) }}"><i class="bi bi-eye"></i> Voir détails</a></li>
                                <li><a class="dropdown-item" href="{{ route('news.edit', $n) }}"><i class="bi bi-pencil"></i> Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('news.destroy', $n) }}" onsubmit="return confirm('Supprimer cette actualité ?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Aucune actualité.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $news->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
