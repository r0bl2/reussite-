@extends('layouts.admin')

@section('title', 'Détails de l\'épreuve')
@section('page_title', 'Détails de l\'épreuve')

@php $labels = ['pending' => 'En attente', 'approved' => 'Validée', 'rejected' => 'Rejetée']; @endphp

@section('content')
<div class="card">
    <div class="card-body py-2 small">
        <div class="row">
            <div class="col-md-3"><strong>Titre :</strong> {{ $paper->title }}</div>
            <div class="col-md-3"><strong>Matière :</strong> {{ $paper->subject }}</div>
            <div class="col-md-3"><strong>Niveau :</strong> {{ $paper->level }}</div>
            <div class="col-md-3"><strong>Statut :</strong> {{ $labels[$paper->status] ?? $paper->status }}</div>
            <div class="col-md-3"><strong>Auteur :</strong> {{ $paper->author?->role === 'admin' ? 'Administration' : ($paper->author->username ?? '-') }}</div>
            <div class="col-md-9"><strong>Description :</strong> {{ $paper->description ?: '-' }}</div>
            @if($paper->rejection_reason)
                <div class="col-12"><strong>Motif du rejet :</strong> {{ $paper->rejection_reason }}</div>
            @endif
        </div>
    </div>
</div>
@include('admin.partials.file-preview', ['fileLabel' => 'Épreuve', 'filePath' => $paper->file_path, 'previewUrl' => route('exam-papers.preview', $paper), 'downloadUrl' => route('exam-papers.download', $paper)])
<a href="{{ route('exam-papers.index') }}" class="btn btn-secondary btn-sm mt-2">Retour</a>
@endsection
