@extends('layouts.admin')

@section('title', 'Actualités')
@section('page_title', 'Actualités')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Rechercher un titre">
            <button class="btn btn-sm btn-primary">Filtrer</button>
        </form>
        <a href="{{ route('news.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>#</th><th>Image</th><th>Titre</th><th>Catégorie</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($news as $n)
                <tr>
                    <td>{{ $n->id }}</td>
                    <td>@if($n->cover_image_url)<img src="{{ $n->cover_image_url }}" height="40" alt="">@endif</td>
                    <td>{{ $n->title }}</td>
                    <td>{{ $n->category ?? '-' }}</td>
                    <td><span class="badge {{ $n->is_published ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $n->is_published ? 'Publié' : 'Brouillon' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('news.edit', $n) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('news.destroy', $n) }}" class="d-inline" onsubmit="return confirm('Supprimer cette actualité ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucune actualité.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $news->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
