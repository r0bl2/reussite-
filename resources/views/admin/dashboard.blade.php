@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page_title', 'Tableau de bord')

@section('content')
<div class="row g-2 mb-2">
    @foreach($cards as [$label, $value, $color])
        <div class="col-lg-2 col-4">
            <div class="border rounded bg-body text-center py-1">
                <div class="fs-4 fw-bold text-{{ $color }}">{{ $value }}</div>
                <div class="small text-muted">{{ $label }}</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-2">
    <div class="col-lg-8">
        <div class="card mb-0">
            <div class="card-header small fw-semibold">Contenus : actifs et en attente</div>
            <div class="card-body p-2" style="height:260px"><canvas id="contentChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-0">
            <div class="card-header small fw-semibold">Demandes de répétition</div>
            <div class="card-body p-2" style="height:260px"><canvas id="demandChart"></canvas></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('contentChart'), {
    type: 'bar',
    data: {
        labels: @json($chart['labels']),
        datasets: [
            { label: 'Actifs', data: @json($chart['actifs']), backgroundColor: '#198754' },
            { label: 'En attente', data: @json($chart['attente']), backgroundColor: '#ffc107' }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});
new Chart(document.getElementById('demandChart'), {
    type: 'doughnut',
    data: {
        labels: ['En attente', 'Validées', 'Rejetées'],
        datasets: [{ data: @json($demands), backgroundColor: ['#ffc107', '#198754', '#dc3545'] }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
