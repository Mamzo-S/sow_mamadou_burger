<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ISI Burger')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
    <style>
        body {
            background: linear-gradient(180deg, #fff6ea 0%, #f7f7f7 100%);
            min-height: 100vh;
        }

        .hero-panel {
            background: linear-gradient(135deg, #212529 0%, #5c2f14 100%);
            color: #fff;
            border-radius: 1.5rem;
        }

        .soft-card {
            border: 0;
            border-radius: 1.25rem;
            box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.08);
        }

        .table-shell {
            border: 0;
            border-radius: 1.25rem;
            overflow: hidden;
            box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.08);
        }

        .table-shell .table {
            margin-bottom: 0;
        }

        .table-shell .table thead th {
            background: #fff3e4;
            border-bottom: 0;
            color: #6b3b19;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .burger-thumb {
            position: relative;
            overflow: hidden;
            min-height: 170px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #ffcc80 0%, #ff8a65 100%);
            display: flex;
            align-items: end;
            justify-content: start;
            padding: 1rem;
            color: #4a1f00;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .burger-thumb img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .burger-thumb-label {
            position: relative;
            z-index: 1;
            background: rgba(255, 248, 240, 0.86);
            color: #4a1f00;
            border-radius: 1rem;
            padding: 0.65rem 0.85rem;
            max-width: 100%;
        }

        .category-chip {
            border-radius: 999px;
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 1.25rem 2.5rem rgba(0, 0, 0, 0.12);
        }

        .stat-card {
            border: 0;
            border-radius: 1.25rem;
            background: #fff;
            box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.08);
        }

        .order-card {
            border: 0;
            border-radius: 1.35rem;
            background: linear-gradient(180deg, #ffffff 0%, #fffaf4 100%);
            box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.08);
        }

        .order-meta {
            color: #7a6a5d;
            font-size: 0.92rem;
        }

        .order-stepper {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .order-step {
            position: relative;
            border-radius: 1rem;
            padding: 0.9rem 0.95rem;
            background: #f7efe5;
            border: 1px solid #ead8c4;
            min-height: 88px;
        }

        .order-step.is-done {
            background: #ebf8ef;
            border-color: #bfe3cb;
        }

        .order-step.is-current {
            background: #fff4d6;
            border-color: #f1c86f;
            box-shadow: inset 0 0 0 1px rgba(241, 200, 111, 0.45);
        }

        .order-step.is-cancelled {
            background: #fde8e8;
            border-color: #f3b6b6;
        }

        .order-step-head {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 0.45rem;
        }

        .order-step-dot {
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 50%;
            background: #c9b29d;
            flex-shrink: 0;
        }

        .order-step.is-done .order-step-dot,
        .order-step.is-current .order-step-dot {
            background: #d97706;
        }

        .order-step.is-done .order-step-dot {
            background: #15803d;
        }

        .order-step.is-cancelled .order-step-dot {
            background: #b91c1c;
        }

        .order-step-label {
            font-weight: 700;
            color: #4f2f17;
        }

        .order-step-note {
            color: #8a7768;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .command-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .command-actions form {
            margin: 0;
        }

        .command-chip {
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            border: 1px solid #e2c5aa;
            background: #fff;
            color: #5a3317;
            font-weight: 600;
        }

        .command-chip:hover {
            background: #fff4e9;
            border-color: #d28a54;
            color: #5a3317;
        }

        .command-chip.is-active {
            background: #212529;
            border-color: #212529;
            color: #fff;
        }

        .command-link-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .chart-shell {
            position: relative;
            height: 320px;
            max-height: 320px;
            min-height: 320px;
        }

        .chart-shell canvas {
            width: 100% !important;
            height: 100% !important;
        }

        @media (max-width: 767.98px) {
            .order-stepper {
                grid-template-columns: 1fr;
            }

            .chart-shell {
                height: 260px;
                max-height: 260px;
                min-height: 260px;
            }
        }

        .status-pill {
            border-radius: 999px;
            padding: 0.45rem 0.85rem;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .status-en_attente {
            background: #fff3cd;
            color: #8a5a00;
        }

        .status-en_preparation {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-prete {
            background: #dcfce7;
            color: #166534;
        }

        .status-payee {
            background: #d1fae5;
            color: #047857;
        }

        .status-annulee {
            background: #fee2e2;
            color: #b91c1c;
        }

        .modal .modal-content {
            border: 0;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 1.5rem 3rem rgba(0, 0, 0, 0.18);
            display: flex;
            flex-direction: column;
        }

        .modal .modal-content > form {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-height: 0;
        }

        .modal .modal-header {
            background: linear-gradient(135deg, #1f2327 0%, #6b3b19 100%);
            color: #fff;
            border-bottom: 0;
            padding: 1.25rem 1.5rem;
        }

        .modal .modal-title {
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .modal .btn-close {
            filter: invert(1);
            opacity: 0.9;
        }

        .modal .modal-body {
            background: linear-gradient(180deg, #fffdfb 0%, #fff4e9 100%);
            padding: 1.5rem;
            flex: 1 1 auto;
            overflow-y: auto;
        }

        .modal .modal-footer {
            background: #fff4e9;
            border-top: 0;
            padding: 1rem 1.5rem 1.5rem;
            gap: 0.5rem;
            position: sticky;
            bottom: 0;
            z-index: 1;
        }

        .modal .form-label {
            font-weight: 600;
            color: #5b3317;
            margin-bottom: 0.55rem;
        }

        .modal .form-control,
        .modal .form-select {
            border-radius: 1rem;
            border-color: #e8d2bf;
            padding: 0.8rem 1rem;
            box-shadow: none;
        }

        .modal textarea.form-control {
            min-height: 120px;
        }

        .modal .form-control:focus,
        .modal .form-select:focus {
            border-color: #d28a54;
            box-shadow: 0 0 0 0.2rem rgba(210, 138, 84, 0.18);
        }

        .modal .form-check {
            background: rgba(255, 255, 255, 0.78);
            border: 1px solid #ead6c7;
            border-radius: 1rem;
            padding: 0.9rem 1rem 0.9rem 2.6rem;
        }

        .modal .form-text {
            color: #7b5a44;
        }
    </style>
</head>
<body>
    @php
        $currentUser = auth()->user();
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('home') }}">ISI Burger</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <div class="navbar-nav me-auto">
                    @auth
                        @if($currentUser->isClient())
                            <a class="nav-link {{ request()->routeIs('site.client') ? 'active' : '' }}" href="{{ route('site.client') }}">Catalogue</a>
                            <a class="nav-link {{ request()->routeIs('client.orders') ? 'active' : '' }}" href="{{ route('client.orders') }}">Mes commandes</a>
                        @else
                            <a class="nav-link {{ request()->routeIs('site.home') ? 'active' : '' }}" href="{{ route('site.home') }}">Tableau de bord</a>
                            <a class="nav-link {{ request()->routeIs('site.client') ? 'active' : '' }}" href="{{ route('site.client') }}">Catalogue</a>
                            <a class="nav-link {{ request()->routeIs('burgers.*') ? 'active' : '' }}" href="{{ route('burgers.index') }}">Gestion burgers</a>
                            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Gestion categories</a>
                            <a class="nav-link {{ request()->routeIs('commandes.*') ? 'active' : '' }}" href="{{ route('commandes.index') }}">Commandes</a>
                            <a class="nav-link {{ request()->routeIs('paiements.*') ? 'active' : '' }}" href="{{ route('paiements.index') }}">Paiements</a>
                            <a class="nav-link {{ request()->routeIs('factures.*') ? 'active' : '' }}" href="{{ route('factures.index') }}">Factures</a>
                            <a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">Roles</a>
                        @endif
                    @endauth
                </div>

                <div class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    @auth
                        <span class="navbar-text text-white-50 small">
                            {{ $currentUser->prenom }} {{ $currentUser->nom }}
                            @if($currentUser->role)
                                | {{ ucfirst($currentUser->role->libelle) }}
                            @endif
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm">Deconnexion</button>
                        </form>
                    @else
                        <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Connexion</a>
                        <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Inscription</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4 py-md-5">
        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
        @endif

        @if(session('delete'))
            <div class="alert alert-warning border-0 shadow-sm">{{ session('delete') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <strong>Veuillez corriger les erreurs du formulaire.</strong>
            </div>
        @endif

        @yield('content')

        @isset($header)
            <div class="soft-card bg-white p-4 mb-4">
                {{ $header }}
            </div>
        @endisset

        @isset($slot)
            {{ $slot }}
        @endisset
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
