@extends('layouts.admin')

@section('title', 'Détails du cours')
@section('page_title', 'Détails du cours')

@section('content')
<div class="card">
    <div class="card-body py-2 small">
        <div class="row">
            <div class="col-md-3"><strong>Titre :</strong> {{ $course->title }}</div>
            <div class="col-md-3"><strong>Matière :</strong> {{ $course->subject->name ?? '-' }}</div>
            <div class="col-md-3"><strong>Niveau / Classe :</strong> {{ $course->level ?? '-' }} / {{ $course->class ?? '-' }}</div>
            <div class="col-md-3"><strong>Statut :</strong> {{ $course->active ? 'Actif' : 'En attente' }}</div>
            <div class="col-md-3"><strong>Auteur :</strong> {{ $course->teacher?->user?->username ?? 'Administration' }}</div>
            <div class="col-md-9"><strong>Description :</strong> {{ $course->description ?: '-' }}</div>
        </div>
    </div>
</div>
@include('admin.partials.file-preview', ['fileLabel' => 'Document du cours', 'filePath' => $course->file_path, 'previewUrl' => route('courses.preview', $course), 'downloadUrl' => route('courses.download', $course)])
<a href="{{ route('courses.index') }}" class="btn btn-secondary btn-sm mt-2">Retour</a>
@endsection
