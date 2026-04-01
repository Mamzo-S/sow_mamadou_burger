@extends('layouts.app')

@section('title', 'Details categorie')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill text-bg-warning text-dark mb-3">Categorie</span>
                <h1 class="display-6 fw-semibold mb-3">{{ $categorie->nom }}</h1>
                <p class="text-white-50 mb-0">{{ $categorie->description ?: 'Aucune description pour cette categorie.' }}</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="bg-white text-dark rounded-4 p-4">
                    <div class="small text-muted">Nombre de burgers</div>
                    <div class="fs-2 fw-semibold mb-3">{{ $categorie->burgers->count() }}</div>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-dark w-100">Retour a la gestion</a>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4">
        @if($categorie->burgers->isEmpty())
            <div class="col-12">
                <div class="soft-card bg-white p-5 text-center">
                    <h2 class="h4 mb-2">Aucun burger associe</h2>
                    <p class="text-muted mb-0">Ajoute des burgers dans cette categorie pour enrichir le catalogue.</p>
                </div>
            </div>
        @else
            @foreach($categorie->burgers as $burger)
                <div class="col-md-6 col-xl-4">
                    <div class="card soft-card hover-lift h-100">
                        <div class="card-body p-4 d-flex flex-column">
                            @php
                                $imageUrl = $burger->image ? \Illuminate\Support\Facades\Storage::url($burger->image) : null;
                            @endphp
                            <div class="burger-thumb mb-3">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $burger->nom }}">
                                @endif
                                <div class="burger-thumb-label">
                                    <div class="small text-uppercase opacity-75">{{ $categorie->nom }}</div>
                                    <div class="fs-4">{{ $burger->nom }}</div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mb-3">
                                <span class="badge text-bg-dark">{{ $burger->disponible ? 'Disponible' : 'Indisponible' }}</span>
                                <span class="badge text-bg-light border">Stock: {{ $burger->stock }}</span>
                            </div>

                            <div class="fs-5 fw-semibold mb-2">{{ number_format((float) $burger->prix, 2, ',', ' ') }} FCFA</div>
                            <p class="text-muted flex-grow-1">{{ $burger->description ?: 'Aucune description.' }}</p>

                            <div class="d-flex gap-2">
                                <a href="{{ route('burgers.show', $burger->id) }}" class="btn btn-outline-dark flex-fill">Voir</a>
                                <a href="{{ route('commandes.create', ['burger' => $burger->id]) }}" class="btn btn-warning flex-fill {{ ! $burger->disponible || $burger->stock < 1 ? 'disabled' : '' }}">
                                    Commander
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </section>
@endsection
