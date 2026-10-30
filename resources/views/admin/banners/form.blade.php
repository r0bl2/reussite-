@extends('layouts.admin')

@php $isEdit = $banner->exists; @endphp

@section('title', $isEdit ? 'Modifier une bannière' : 'Nouvelle bannière')
@section('page_title', $isEdit ? 'Modifier la bannière' : 'Nouvelle bannière')

@section('content')
<div class="card">
    <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('banners.update', $banner) : route('banners.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Titre</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-select" required>
                        <option value="image" @selected(old('type', $banner->type) === 'image')>Image</option>
                        <option value="video" @selected(old('type', $banner->type) === 'video')>Vidéo</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="order" class="form-control" value="{{ old('order', $banner->order ?? 0) }}">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Fichier (jpg, png, gif, mp4, 10 Mo max)</label>
                <input type="file" name="media" class="form-control">
                @if($isEdit && $banner->full_media_url)
                    @if($banner->type === 'image')
                        <img src="{{ $banner->full_media_url }}" height="80" class="mt-2 border rounded" alt="Aperçu">
                    @else
                        <small class="text-muted d-block mt-2">Vidéo actuelle : {{ $banner->media_url }}</small>
                    @endif
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label">ou lien direct du média</label>
                <input type="text" name="media_url" class="form-control" value="{{ old('media_url', $banner->media_url) }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Lien de destination</label>
                <input type="text" name="target_url" class="form-control" value="{{ old('target_url', $banner->target_url) }}">
            </div>
            <div class="form-check">
                <input type="checkbox" name="is_active" value="1" id="act" class="form-check-input" @checked(old('is_active', $isEdit ? $banner->is_active : true))>
                <label for="act" class="form-check-label">Active</label>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
            <a href="{{ route('banners.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
