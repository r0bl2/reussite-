@extends('layouts.admin')

@php
    $isEdit = $exercise->exists;
    $typeLabels = ['college' => 'Collège', 'lycee_moderne' => 'Lycée moderne', 'lycee_technique' => 'Lycée technique'];
@endphp

@section('title', $isEdit ? 'Modifier un exercice' : 'Nouvel exercice')
@section('page_title', $isEdit ? 'Modifier l\'exercice' : 'Nouvel exercice')

@section('content')
<div class="card">
    <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('exercises.update', $exercise) : route('exercises.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Titre *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $exercise->title) }}" required>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Matière *</label>
                    <select name="subject_id" id="subject_id" class="form-select" required>
                        <option value="">Choisir...</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" data-type="{{ $s->type_lycee }}" @selected(old('subject_id', $exercise->subject_id) == $s->id)>{{ $s->name }} ({{ $typeLabels[$s->type_lycee] ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                @include('admin.partials.level-class', ['levelName' => 'niveau', 'className' => 'classe', 'levelValue' => $exercise->niveau, 'classValue' => $exercise->classe])
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Exercice (PDF) {{ $isEdit ? '(vide = conserver)' : '*' }}</label>
                    <input type="file" name="file" class="form-control" {{ $isEdit ? '' : 'required' }}>
                    @if($isEdit && $exercise->file_path)<small class="text-muted">Actuel : {{ basename($exercise->file_path) }}</small>@endif
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Corrigé (optionnel){{ $isEdit ? ' (vide = conserver)' : '' }}</label>
                    <input type="file" name="correction" class="form-control">
                    @if($isEdit && $exercise->correction_path)<small class="text-muted">Actuel : {{ basename($exercise->correction_path) }}</small>@endif
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary">{{ $isEdit ? 'Enregistrer' : 'Publier l\'exercice' }}</button>
            <a href="{{ route('exercises.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
