@extends('layouts.admin')

@section('title', 'Bannières')
@section('page_title', 'Bannières')

@section('content')
<div class="card">
    <div class="card-header text-end">
        <a href="{{ route('banners.create') }}" class="btn btn-sm btn-success"><i class="bi bi-plus-lg"></i> Ajouter</a>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead><tr><th>Ordre</th><th>Aperçu</th><th>Titre</th><th>Type</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($banners as $b)
                <tr>
                    <td>{{ $b->order }}</td>
                    <td>
                        @if($b->type === 'image' && $b->full_media_url)
                            <img src="{{ $b->full_media_url }}" height="40" alt="">
                        @else
                            <i class="bi bi-camera-video"></i>
                        @endif
                    </td>
                    <td>{{ $b->title ?? '-' }}</td>
                    <td>{{ $b->type }}</td>
                    <td><span class="badge {{ $b->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $b->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('banners.edit', $b) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('banners.destroy', $b) }}" class="d-inline" onsubmit="return confirm('Supprimer cette bannière ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aucune bannière.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $banners->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
