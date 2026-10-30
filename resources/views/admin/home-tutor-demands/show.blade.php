@extends('layouts.admin')

@section('title', 'Demande #' . $demand->id)
@section('page_title', 'Demande #' . $demand->id)

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header">Candidat</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nom</dt><dd class="col-sm-8">{{ $demand->last_name }} {{ $demand->first_name }}</dd>
                    <dt class="col-sm-4">Téléphone</dt><dd class="col-sm-8">{{ $demand->phone_number }}</dd>
                    <dt class="col-sm-4">Ville / Quartier</dt><dd class="col-sm-8">{{ $demand->city }} / {{ $demand->neighborhood }}</dd>
                    <dt class="col-sm-4">Type de lycée</dt><dd class="col-sm-8">{{ $demand->type_lycee ?? '-' }}</dd>
                    <dt class="col-sm-4">Niveaux</dt><dd class="col-sm-8">{{ implode(', ', $demand->levels ?? []) ?: '-' }}</dd>
                    <dt class="col-sm-4">Matières</dt><dd class="col-sm-8">{{ implode(', ', $demand->subjects ?? []) ?: '-' }}</dd>
                    <dt class="col-sm-4">Statut</dt><dd class="col-sm-8">{{ ['pending' => 'En attente', 'approved' => 'Validée', 'rejected' => 'Rejetée'][$demand->status] ?? $demand->status }}</dd>
                    <dt class="col-sm-4">Commentaire admin</dt><dd class="col-sm-8">{{ $demand->admin_comment ?? '-' }}</dd>
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Documents</div>
            <div class="card-body d-flex gap-2 flex-wrap">
                @foreach(['photo_path' => 'Photo', 'diploma_path' => 'Diplôme', 'cv_path' => 'CV'] as $f => $label)
                    @if($demand->$f)
                        <a href="{{ route('home-tutor-demands.file', [$demand, $f]) }}" target="_blank" class="btn btn-outline-secondary btn-sm">{{ $label }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

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
        <a href="{{ route('home-tutor-demands.index') }}" class="btn btn-secondary mt-3">Retour</a>
    </div>
</div>
@endsection
