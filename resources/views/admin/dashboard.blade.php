@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary"><div class="inner"><h3>{{ $stats['students'] }}</h3><p>Élèves</p></div></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success"><div class="inner"><h3>{{ $stats['teachers'] }}</h3><p>Gestion enseignant</p></div></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-info"><div class="inner"><h3>{{ $stats['subjects'] }}</h3><p>Matières</p></div></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-danger"><div class="inner"><h3>{{ $stats['demands_pending'] }}</h3><p>Demandes en attente</p></div></div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4 col-12">
        <div class="small-box text-bg-warning"><div class="inner"><h3>{{ $stats['courses'] }}</h3><p>Cours ({{ $stats['courses_pending'] }} en attente)</p></div></div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box text-bg-secondary"><div class="inner"><h3>{{ $stats['summaries'] }}</h3><p>Résumés ({{ $stats['summaries_pending'] }} en attente)</p></div></div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box text-bg-dark"><div class="inner"><h3>{{ $stats['exercises'] }}</h3><p>Exercices</p></div></div>
    </div>
</div>
@endsection
