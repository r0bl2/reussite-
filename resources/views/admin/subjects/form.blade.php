@extends('layouts.admin')

@php $isEdit = $subject->exists; @endphp

@section('title', $isEdit ? 'Modifier une matière' : 'Nouvelle matière')
@section('page_title', $isEdit ? 'Modifier la matière' : 'Nouvelle matière')

@section('content')
@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif

<div class="card">
    <form method="POST" action="{{ $isEdit ? route('subjects.update', $subject) : route('subjects.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Nom *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Type d'établissement *</label>
                <select name="type_lycee" class="form-select" required>
                    <option value="">Choisir...</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" @selected(old('type_lycee', $subject->type_lycee) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary">{{ $isEdit ? 'Enregistrer' : 'Créer' }}</button>
            <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
@endsection
