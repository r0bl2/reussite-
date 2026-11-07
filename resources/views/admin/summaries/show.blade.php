@extends('layouts.admin')

@section('title', 'Détails du résumé')
@section('page_title', 'Détails du résumé')

@section('content')
<div class="card">
    <div class="card-body py-2 small">
        <div class="row">
            <div class="col-md-3"><strong>Titre :</strong> {{ $summary->title }}</div>
            <div class="col-md-3"><strong>Matière :</strong> {{ $summary->subject->name ?? '-' }}</div>
            <div class="col-md-3"><strong>Niveau / Classe :</strong> {{ $summary->level ?? '-' }} / {{ $summary->class ?? '-' }}</div>
            <div class="col-md-3"><strong>Statut :</strong> {{ $summary->active ? 'Actif' : 'En attente' }}</div>
            <div class="col-md-3"><strong>Auteur :</strong> {{ $summary->teacher?->user?->username ?? 'Administration' }}</div>
            <div class="col-md-9"><strong>Description :</strong> {{ $summary->description ?: '-' }}</div>
        </div>
    </div>
</div>
@include('admin.partials.file-preview', ['fileLabel' => 'Document du résumé', 'filePath' => $summary->file_path, 'previewUrl' => route('summaries.preview', $summary), 'downloadUrl' => route('summaries.download', $summary)])
<a href="{{ route('summaries.index') }}" class="btn btn-secondary btn-sm mt-2">Retour</a>
@endsection
