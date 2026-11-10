@extends('layouts.admin')

@section('title', 'Détails de l\'actualité')
@section('page_title', 'Détails de l\'actualité')

@section('content')
<div class="card">
    <div class="card-body small">
        <div class="row mb-2">
            <div class="col-md-4"><strong>Titre :</strong> {{ $item->title }}</div>
            <div class="col-md-4"><strong>Catégorie :</strong> {{ $item->category ?? '-' }}</div>
            <div class="col-md-4"><strong>Statut :</strong> {{ $item->is_published ? 'Publié' : 'Brouillon' }}</div>
        </div>
        @if($item->cover_image_url)
            <img src="{{ $item->cover_image_url }}" class="img-fluid rounded mb-2" style="max-height:260px" alt="Couverture">
        @endif
        <div style="white-space:pre-line">{{ $item->content }}</div>
        @if($item->attachment_url)
            <div class="mt-2">
                <a href="{{ $item->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-paperclip"></i> Pièce jointe</a>
            </div>
        @endif
    </div>
</div>
<a href="{{ route('news.index') }}" class="btn btn-secondary btn-sm">Retour</a>
@endsection
