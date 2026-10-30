@extends('layouts.admin')

@php
    $isEdit = $summary->exists;
    $typeLabels = ['college' => 'Collège', 'lycee_moderne' => 'Lycée moderne', 'lycee_technique' => 'Lycée technique'];
@endphp

@section('title', $isEdit ? 'Modifier un résumé' : 'Nouveau résumé')
@section('page_title', $isEdit ? 'Modifier le résumé' : 'Nouveau résumé')

@section('content')
<div class="card">
    <form method="POST" enctype="multipart/form-data" action="{{ $isEdit ? route('summaries.update', $summary) : route('summaries.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Titre *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $summary->title) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description *</label>
                <textarea name="description" rows="4" class="form-control" required>{{ old('description', $summary->description) }}</textarea>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Matière *</label>
                    <select name="subject_id" id="subject_id" class="form-select" required>
                        <option value="">Choisir...</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" data-type="{{ $s->type_lycee }}" @selected(old('subject_id', $summary->subject_id) == $s->id)>{{ $s->name }} ({{ $typeLabels[$s->type_lycee] ?? '—' }})</option>
                        @endforeach
                    </select>
                </div>
                @include('admin.partials.level-class', ['levelName' => 'level', 'className' => 'class', 'levelValue' => $summary->level, 'classValue' => $summary->class])
            </div>
            <div class="mb-3">
                <label class="form-label">Fichier {{ $isEdit ? '(laisser vide pour conserver l\'actuel)' : '*' }}</label>
                <input type="file" name="file" class="form-control" {{ $isEdit ? '' : 'required' }}>
                @if($isEdit && $summary->file_path)<small class="text-muted">Actuel : {{ basename($summary->file_path) }}</small>@endif
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
            <a href="{{ route('summaries.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
