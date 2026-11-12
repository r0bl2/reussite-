@extends('layouts.admin')

@section('title', 'Demande #' . $demand->id)
@section('page_title', 'Demande #' . $demand->id)

@php
    $labels = ['pending' => ['En attente', 'text-bg-warning'], 'approved' => ['Validée', 'text-bg-success'], 'rejected' => ['Rejetée', 'text-bg-danger']];
    $typeLabels = ['college' => 'Collège', 'lycee_moderne' => 'Lycée moderne', 'lycee_technique' => 'Lycée technique'];
    $pending = $demand->status === 'pending';
@endphp

@section('content')
<div class="row g-2">
    <div class="{{ $pending ? 'col-md-7' : 'col-12' }}">
        <div class="card">
            <div class="card-header">Candidat</div>
            <div class="card-body py-2">
                <dl class="row mb-0 small">
                    <dt class="col-sm-3">Nom</dt><dd class="col-sm-9">{{ $demand->last_name }} {{ $demand->first_name }}</dd>
                    <dt class="col-sm-3">Téléphone</dt><dd class="col-sm-9">{{ $demand->phone_number }}</dd>
                    <dt class="col-sm-3">Ville / Quartier</dt><dd class="col-sm-9">{{ $demand->city }} / {{ $demand->neighborhood }}</dd>
                    <dt class="col-sm-3">Type de lycée</dt><dd class="col-sm-9">{{ $typeLabels[$demand->type_lycee] ?? ($demand->type_lycee ?? '-') }}</dd>
                    <dt class="col-sm-3">Niveaux</dt><dd class="col-sm-9">{{ implode(', ', $demand->levels ?? []) ?: '-' }}</dd>
                    <dt class="col-sm-3">Matières</dt><dd class="col-sm-9">{{ implode(', ', $demand->subjects ?? []) ?: '-' }}</dd>
                    <dt class="col-sm-3">Statut</dt>
                    <dd class="col-sm-9"><span class="badge {{ $labels[$demand->status][1] ?? 'text-bg-secondary' }}">{{ $labels[$demand->status][0] ?? $demand->status }}</span></dd>
                    @unless($pending)
                        <dt class="col-sm-3">Commentaire</dt><dd class="col-sm-9">{{ $demand->admin_comment ?: '-' }}</dd>
                        <dt class="col-sm-3">Date de décision</dt><dd class="col-sm-9">{{ $demand->updated_at?->format('d/m/Y H:i') }}</dd>
                    @endunless
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Documents</div>
            <div class="card-body d-flex gap-2 flex-wrap py-2">
                @foreach(['photo_path' => 'Photo', 'diploma_path' => 'Diplôme', 'cv_path' => 'CV'] as $f => $label)
                    @if($demand->$f)
                        <a href="{{ route('home-tutor-demands.file', [$demand, $f]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">{{ $label }}</a>
                    @endif
                @endforeach
            </div>
        </div>
        <a href="{{ $pending ? route('home-tutor-demands.index') : route('home-tutor-demands.history') }}" class="btn btn-secondary btn-sm">Retour</a>
    </div>

    @if($pending)
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Décision</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('home-tutor-demands.validate', $demand) }}" class="mb-3">
                        @csrf @method('PUT')
                        <textarea name="admin_comment" rows="2" class="form-control mb-2" placeholder="Commentaire (facultatif)"></textarea>
                        <button class="btn btn-success w-100">Valider la demande</button>
                    </form>
                    <form id="rejet" method="POST" action="{{ route('home-tutor-demands.reject', $demand) }}">
                        @csrf @method('PUT')
                        <textarea name="admin_comment" rows="2" class="form-control mb-2" placeholder="Motif du rejet (obligatoire)" required></textarea>
                        <button class="btn btn-danger w-100">Rejeter la demande</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
