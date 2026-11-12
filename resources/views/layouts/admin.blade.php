<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Réussite+')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    <style>
        .app-content-header{padding:.35rem 0}
        .app-content-header h3{font-size:1.1rem;font-weight:600}
        .app-content{padding-bottom:.25rem}
        .card{margin-bottom:.5rem}
        .card-header{padding:.35rem .6rem}
        .card-footer{padding:.25rem .6rem}
        .table{margin-bottom:0;font-size:.8rem}
        .table>:not(caption)>*>*{padding:.15rem .5rem;line-height:1.25;border:1px solid var(--bs-border-color)}
        .table thead th{background:var(--bs-tertiary-bg);font-weight:600;white-space:nowrap}
        .table .btn{--bs-btn-padding-y:.05rem;--bs-btn-padding-x:.35rem;--bs-btn-font-size:.75rem}
        .table .badge{font-size:.68rem;font-weight:500}
        .pagination{margin:0}
        .pagination .page-link{padding:.15rem .5rem;font-size:.8rem}
        .app-sidebar .sidebar-menu > .nav-item > .nav-link{position:relative;padding-right:2.4rem}
        .app-sidebar .sidebar-menu > .nav-item > .nav-link > p{margin:0;white-space:normal;line-height:1.25;overflow:visible}
        .app-sidebar .sidebar-menu > .nav-item > .nav-link::after{content:"\F285";font-family:"bootstrap-icons";position:absolute;right:.9rem;top:50%;transform:translateY(-50%);font-size:.8rem;line-height:1;transition:transform .2s}
        .app-sidebar .sidebar-menu > .nav-item.menu-open > .nav-link::after{transform:translateY(-50%) rotate(90deg)}
        .app-sidebar .sidebar-menu > .nav-item > .nav-link > p{display:flex;align-items:center;justify-content:space-between;width:100%;margin:0;white-space:normal;line-height:1.2}
        .app-sidebar .sidebar-menu .nav-arrow{position:static !important;margin-left:.5rem;flex-shrink:0}
    </style>
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
@php
    $gDash = request()->is('dashboard');
    $gPed  = request()->is('dashboard/subjects*', 'dashboard/courses*', 'dashboard/exercises*', 'dashboard/summaries*', 'dashboard/exam-papers*');
    $gCom  = request()->is('dashboard/teachers*', 'dashboard/home-tutor-demands*', 'dashboard/home-tutor-history');
    $gComm = request()->is('dashboard/news*', 'dashboard/banners*');
@endphp
<div class="app-wrapper">

    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3">
                    <span class="text-secondary small">
                        <i class="bi bi-person-circle me-1"></i>
                        @auth {{ Auth::user()->username ?? Auth::user()->email }} @else Administrateur @endauth
                    </span>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right me-1"></i> Déconnexion</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ url('/dashboard') }}" class="brand-link"><span class="brand-text fw-light">Réussite+</span></a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

                    <li class="nav-item {{ $gDash ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $gDash ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Tableau de bord </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Vue d'ensemble</p></a></li>
                        </ul>
                    </li>

                    <li class="nav-item {{ $gPed ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $gPed ? 'active' : '' }}">
                            <i class="nav-icon bi bi-mortarboard"></i>
                            <p>Pédagogie & contenus </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ url('/dashboard/subjects') }}" class="nav-link {{ request()->is('dashboard/subjects*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Matières</p></a></li>
                            <li class="nav-item"><a href="{{ url('/dashboard/courses') }}" class="nav-link {{ request()->is('dashboard/courses*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Cours</p></a></li>
                            <li class="nav-item"><a href="{{ url('/dashboard/exercises') }}" class="nav-link {{ request()->is('dashboard/exercises*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Exercices</p></a></li>
                            <li class="nav-item"><a href="{{ url('/dashboard/summaries') }}" class="nav-link {{ request()->is('dashboard/summaries*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Résumés</p></a></li>
                            <li class="nav-item"><a href="{{ url('/dashboard/exam-papers') }}" class="nav-link {{ request()->is('dashboard/exam-papers*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Épreuves d'examen</p></a></li>
                        </ul>
                    </li>

                    <li class="nav-item {{ $gCom ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $gCom ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Communauté </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ url('/dashboard/teachers') }}" class="nav-link {{ request()->is('dashboard/teachers*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Gestion enseignant</p></a></li>
                            <li class="nav-item"><a href="{{ url('/dashboard/home-tutor-demands') }}" class="nav-link {{ request()->is('dashboard/home-tutor-demands*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Demandes de répétition</p></a></li>
                            <li class="nav-item"><a href="{{ route('home-tutor-demands.history') }}" class="nav-link {{ request()->is('dashboard/home-tutor-history') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Historique des décisions</p></a></li>
                        </ul>
                    </li>

                    <li class="nav-item {{ $gComm ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $gComm ? 'active' : '' }}">
                            <i class="nav-icon bi bi-megaphone"></i>
                            <p>Communication & config </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ url('/dashboard/news') }}" class="nav-link {{ request()->is('dashboard/news*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Actualités</p></a></li>
                            <li class="nav-item"><a href="{{ url('/dashboard/banners') }}" class="nav-link {{ request()->is('dashboard/banners*') ? 'active' : '' }}"><i class="nav-icon bi bi-circle"></i><p>Bannières</p></a></li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid"><h3 class="mb-0">@yield('page_title', 'Tableau de bord')</h3></div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-2 py-2" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-2 py-2" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-2 py-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Attention !</strong> Veuillez corriger les erreurs ci-dessous :
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer"><strong>Réussite+</strong> &copy; {{ date('Y') }}</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@stack('scripts')
</body>
</html>
