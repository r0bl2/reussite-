@extends('layouts.admin')

@section('title', 'Ajouter une épreuve')
@section('page_title', 'Ajouter une épreuve d\'examen')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('exam-papers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label">Matière <span class="text-danger">*</span></label>
                <select class="form-select @error('subject') is-invalid @enderror" id="subject" name="subject" required>
                    <option value="">Sélectionner une matière</option>
                    @foreach($subjects as $subj)
                        <option value="{{ $subj->name }}" @selected(old('subject') === $subj->name)>
                            {{ $subj->name }}
                        </option>
                    @endforeach
                </select>
                @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="level" class="form-label">Niveau <span class="text-danger">*</span></label>
                <select class="form-select @error('level') is-invalid @enderror" id="level" name="level" required>
                    <option value="">Sélectionner un niveau</option>
                    @foreach($levels as $lvl)
                        <option value="{{ $lvl }}" @selected(old('level') === $lvl)>{{ $lvl }}</option>
                    @endforeach
                </select>
                @error('level')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="file" class="form-label">Fichier de l'épreuve (PDF / Image) <span class="text-danger">*</span></label>
                <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('exam-papers.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-success">Enregistrer l'épreuve</button>
            </div>
        </form>
    </div>
</div>
@endsection
