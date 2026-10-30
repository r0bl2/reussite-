<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Edutec Admin')</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <!-- HEADER / NAVBAR -->
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
                        @auth
                            {{ Auth::user()->name ?? Auth::user()->email }}
                        @else
                            Administrateur
                        @endauth
                    </span>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ url('/dashboard') }}" class="brand-link">
                <span class="brand-text fw-light">Edutec Admin</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">

                    <!-- TABLEAU DE BORD -->
                    <li class="nav-item">
                        <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i><p>Tableau de bord</p>
                        </a>
                    </li>

                    <!-- CONTENUS PÉDAGOGIQUES -->
                    <li class="nav-header">PÉDAGOGIE & CONTENUS</li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/subjects') }}" class="nav-link {{ request()->is('dashboard/subjects*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-journal-bookmark"></i><p>Matières / Sujets</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/courses') }}" class="nav-link {{ request()->is('dashboard/courses*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-journal-text"></i><p>Cours</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/exercises') }}" class="nav-link {{ request()->is('dashboard/exercises*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-code"></i><p>Exercices & Corrections</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/summaries') }}" class="nav-link {{ request()->is('dashboard/summaries*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-file-text"></i><p>Résumés / Fiches</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/books') }}" class="nav-link {{ request()->is('dashboard/books*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-book"></i><p>Manuels & Livres</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/trainings') }}" class="nav-link {{ request()->is('dashboard/trainings*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-award"></i><p>Formations</p>
                        </a>
                    </li>

                    <!-- COMMUNAUTÉ & DEMANDES -->
                    <li class="nav-header">COMMUNAUTÉ</li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/students') }}" class="nav-link {{ request()->is('dashboard/students*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i><p>Élèves / Étudiants</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/teachers') }}" class="nav-link {{ request()->is('dashboard/teachers*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-badge"></i><p>Enseignants</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/home-tutor-demands') }}" class="nav-link {{ request()->is('dashboard/home-tutor-demands*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-workspace"></i><p>Demandes Répétiteurs</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/institutions') }}" class="nav-link {{ request()->is('dashboard/institutions*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-building"></i><p>Établissements</p>
                        </a>
                    </li>

                    <!-- COMMUNICATION & PARAMS -->
                    <li class="nav-header">COMMUNICATION & CONFIG</li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/news') }}" class="nav-link {{ request()->is('dashboard/news*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-newspaper"></i><p>Actualités</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/banners') }}" class="nav-link {{ request()->is('dashboard/banners*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-images"></i><p>Bannières</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/dashboard/ad-configurations') }}" class="nav-link {{ request()->is('dashboard/ad-configurations*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-sliders"></i><p>Publicités & Config</p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <h3 class="mb-0">@yield('page_title', 'Tableau de bord')</h3>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                <!-- Notifications Flash -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Erreurs de validation globales -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        <strong>Attention !</strong> Veuillez corriger les erreurs ci-dessous :
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Injection de la vue fille -->
                @yield('content')
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="app-footer">
        <strong>Edutec</strong> &copy; {{ date('Y') }}
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@stack('scripts')
</body>
</html>
