@extends('layouts.admin')

@php $isEdit = $item->exists; @endphp

@section('title', $isEdit ? 'Modifier une actualité' : 'Nouvelle actualité')
@section('page_title', $isEdit ? 'Modifier l\'actualité' : 'Nouvelle actualité')

@section('content')
<div class="card">
    <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('news.update', $item) : route('news.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Titre *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $item->title) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contenu *</label>
                <textarea name="content" rows="6" class="form-control" required>{{ old('content', $item->content) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Catégorie</label>
                <input type="text" name="category" class="form-control" value="{{ old('category', $item->category) }}">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Image de couverture</label>
                    <input type="file" name="cover_image" accept="image/*" class="form-control">
                    @if($isEdit && $item->cover_image_url)<img src="{{ $item->cover_image_url }}" height="60" class="mt-2" alt="">@endif
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Pièce jointe (pdf, doc, docx)</label>
                    <input type="file" name="attachment" class="form-control">
                    @if($isEdit && $item->attachment)<small class="text-muted">Actuelle : {{ basename($item->attachment) }}</small>@endif
                </div>
            </div>
            <div class="form-check">
                <input type="checkbox" name="is_published" value="1" id="pub" class="form-check-input" @checked(old('is_published', $item->is_published))>
                <label for="pub" class="form-check-label">Publié</label>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
            <a href="{{ route('news.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
