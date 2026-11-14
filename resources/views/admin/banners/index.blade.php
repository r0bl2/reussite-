@extends('layouts.admin')

@section('title', 'Bannières')
@section('page_title', 'Bannières')

@section('content')
<div class="card">
    <div class="card-header d-flex gap-2 align-items-center">
        <a href="{{ route('banners.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Ordre</th><th>Aperçu</th><th>Titre</th><th>Type</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($banners as $b)
                <tr>
                    <td>{{ $b->order }}</td>
                    <td>
                        @if($b->type === 'image' && $b->full_media_url)
                            <img src="{{ $b->full_media_url }}" height="22" alt="">
                        @else
                            <i class="bi bi-camera-video"></i>
                        @endif
                    </td>
                    <td>{{ $b->title ?? '-' }}</td>
                    <td>{{ $b->type }}</td>
                    <td><span class="badge {{ $b->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $b->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Actions"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('banners.show', $b) }}"><i class="bi bi-eye"></i> Voir détails</a></li>
                                <li><a class="dropdown-item" href="{{ route('banners.edit', $b) }}"><i class="bi bi-pencil"></i> Modifier</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('banners.destroy', $b) }}" onsubmit="return confirm('Supprimer cette bannière ?')">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bi bi-trash"></i> Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Aucune bannière.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $banners->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
