@extends('layouts.admin')

@section('title', 'Détails de la bannière')
@section('page_title', 'Détails de la bannière')

@section('content')
<div class="card">
    <div class="card-body small">
        <div class="row mb-2">
            <div class="col-md-3"><strong>Titre :</strong> {{ $banner->title ?? '-' }}</div>
            <div class="col-md-2"><strong>Type :</strong> {{ $banner->type }}</div>
            <div class="col-md-2"><strong>Ordre :</strong> {{ $banner->order }}</div>
            <div class="col-md-2"><strong>Statut :</strong> {{ $banner->is_active ? 'Active' : 'Inactive' }}</div>
            <div class="col-md-3"><strong>Lien :</strong> @if($banner->target_url)<a href="{{ $banner->target_url }}" target="_blank">{{ \Illuminate\Support\Str::limit($banner->target_url, 40) }}</a>@else - @endif</div>
        </div>
        @if($banner->full_media_url)
            @if($banner->type === 'video')
                <video src="{{ $banner->full_media_url }}" controls style="max-width:100%;max-height:420px"></video>
            @else
                <img src="{{ $banner->full_media_url }}" class="img-fluid rounded" style="max-height:420px" alt="Bannière">
            @endif
        @else
            <p class="text-muted">Aucun média.</p>
        @endif
    </div>
</div>
<a href="{{ route('banners.index') }}" class="btn btn-secondary btn-sm">Retour</a>
@endsection
