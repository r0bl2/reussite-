@extends('layouts.admin')

@section('title', 'Détails de l\'exercice')
@section('page_title', 'Détails de l\'exercice')

@php
    $typeLabels = ['college' => 'Collège', 'lycee_moderne' => 'Lycée moderne', 'lycee_technique' => 'Lycée technique'];
    $docs = ['file' => ['Exercice', $exercise->file_path], 'correction' => ['Corrigé', $exercise->correction_path]];
@endphp

@section('content')
<div class="card">
    <div class="card-body py-2">
        <div class="row small">
            <div class="col-md-3"><strong>Titre :</strong> {{ $exercise->title }}</div>
            <div class="col-md-3"><strong>Matière :</strong> {{ $exercise->subject->name ?? '-' }} ({{ $typeLabels[$exercise->subject->type_lycee ?? ''] ?? '—' }})</div>
            <div class="col-md-3"><strong>Niveau / Classe :</strong> {{ $exercise->niveau }} / {{ $exercise->classe }}</div>
            <div class="col-md-3"><strong>Statut :</strong> {{ $exercise->active ? 'Actif' : 'En attente' }}</div>
        </div>
    </div>
</div>

<div class="row g-2">
    @foreach($docs as $type => $doc)
        @php
            [$label, $path] = $doc;
            $ext = strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION));
        @endphp
        <div class="col-lg-6">
            <div class="card mb-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">{{ $label }}</span>
                    @if($path)
                        <a href="{{ route('exercises.download', [$exercise, $type]) }}" class="btn btn-sm btn-outline-secondary" title="Télécharger"><i class="bi bi-download"></i></a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if(!$path)
                        <p class="text-muted text-center my-4">Aucun corrigé n'a été ajouté.</p>
                    @elseif($ext === 'pdf')
                        <iframe src="{{ route('exercises.preview', [$exercise, $type]) }}" style="width:100%;height:72vh;border:0"></iframe>
                    @elseif(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ route('exercises.preview', [$exercise, $type]) }}" class="img-fluid d-block mx-auto" alt="{{ $label }}">
                    @else
                        <p class="text-muted text-center my-4">Aperçu indisponible pour ce format (.{{ $ext }}). Utilisez le bouton de téléchargement.</p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<a href="{{ route('exercises.index') }}" class="btn btn-secondary btn-sm mt-2">Retour</a>
@endsection
