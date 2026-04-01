@extends('layouts.app')

@section('title', 'Espace client')

@section('content')
    @php
        $isClient = auth()->user()?->isClient();
    @endphp

    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-5 fw-semibold mb-4">Catalogue</h1>
                <form method="GET" action="{{ route('site.client') }}" class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="q" value="{{ $search }}" class="form-control form-control-lg" placeholder="Libelle du burger">
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" min="0" name="prix_min" value="{{ $minPrice }}" class="form-control form-control-lg" placeholder="Prix min">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" min="0" name="prix_max" value="{{ $maxPrice }}" class="form-control form-control-lg" placeholder="Prix max">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning btn-lg w-100">
                            <i class="bi bi-search me-1"></i> Filtrer
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="bg-white text-dark rounded-4 p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small">Categories</div>
                                <div class="fs-3 fw-semibold">{{ $stats['categories'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="text-muted small">Burgers</div>
                                <div class="fs-3 fw-semibold">{{ $stats['burgers'] }}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            @if($isClient)
                                <a href="{{ route('client.orders') }}" class="btn btn-dark w-100">Mes commandes</a>
                            @else
                                <a href="{{ route('commandes.create') }}" class="btn btn-dark w-100">Nouvelle commande</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('site.client', ['q' => $search, 'prix_min' => $minPrice, 'prix_max' => $maxPrice]) }}"
               class="btn category-chip {{ $selectedCategory ? 'btn-outline-dark' : 'btn-dark' }}">
                Tous les burgers
            </a>
            @foreach($categories as $categorie)
                <a href="{{ route('site.client', ['categorie' => $categorie->id, 'q' => $search, 'prix_min' => $minPrice, 'prix_max' => $maxPrice]) }}"
                   class="btn category-chip {{ $selectedCategory === $categorie->id ? 'btn-warning text-dark' : 'btn-outline-dark' }}">
                    {{ $categorie->nom }}
                    <span class="badge text-bg-light ms-1">{{ $categorie->burgers_disponibles_count }}</span>
                </a>
            @endforeach
        </div>
    </section>

    @if($isClient)
        <form action="{{ route('client.orders.store') }}" method="POST">
            @csrf
            <section>
                <div class="row g-4">
                    @if($burgers->isEmpty())
                        <div class="col-12">
                            <div class="soft-card bg-white p-5 text-center">
                                <h2 class="h4 mb-0">Aucun burger disponible</h2>
                            </div>
                        </div>
                    @else
                        @foreach($burgers as $burger)
                            <div class="col-md-6 col-xl-4">
                                <div class="card soft-card hover-lift h-100">
                                    <div class="card-body d-flex flex-column p-3 p-md-4">
                                        @php
                                            $imageUrl = $burger->image ? \Illuminate\Support\Facades\Storage::url($burger->image) : null;
                                        @endphp
                                        <div class="burger-thumb mb-3">
                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $burger->nom }}">
                                            @endif
                                            <div class="burger-thumb-label">
                                                <div class="small text-uppercase opacity-75">{{ $burger->categorie?->nom ?: 'Sans categorie' }}</div>
                                                <div class="fs-4">{{ $burger->nom }}</div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                            <div>
                                                <span class="badge text-bg-success">Disponible</span>
                                                <span class="badge text-bg-light border">Stock: {{ $burger->stock }}</span>
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-muted">Prix</div>
                                                <div class="fs-5 fw-semibold">{{ number_format((float) $burger->prix, 2, ',', ' ') }} FCFA</div>
                                            </div>
                                        </div>

                                        @if($burger->description)
                                            <div class="text-muted flex-grow-1 mb-4">{{ $burger->description }}</div>
                                        @else
                                            <div class="flex-grow-1 mb-4"></div>
                                        @endif

                                        <div class="mt-auto">
                                            <label for="quantites_{{ $burger->id }}" class="form-label">Quantite</label>
                                            <input
                                                type="number"
                                                min="0"
                                                max="{{ $burger->stock }}"
                                                id="quantites_{{ $burger->id }}"
                                                name="quantites[{{ $burger->id }}]"
                                                value="{{ old('quantites.' . $burger->id, 0) }}"
                                                class="form-control @error('quantites.' . $burger->id) is-invalid @enderror"
                                            >
                                            @error('quantites.' . $burger->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <a href="{{ route('catalogue.burgers.show', $burger->id) }}" class="btn btn-outline-dark w-100 mt-2">Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </section>

            @error('quantites')
                <div class="alert alert-danger border-0 shadow-sm mt-4 mb-0">{{ $message }}</div>
            @enderror

            @if($burgers->isNotEmpty())
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-warning btn-lg">Commander la selection</button>
                    <a href="{{ route('client.orders') }}" class="btn btn-outline-secondary btn-lg">Mes commandes</a>
                </div>
            @endif
        </form>
    @else
        <section>
            <div class="row g-4">
                @if($burgers->isEmpty())
                    <div class="col-12">
                        <div class="soft-card bg-white p-5 text-center">
                            <h2 class="h4 mb-0">Aucun burger disponible</h2>
                        </div>
                    </div>
                @else
                    @foreach($burgers as $burger)
                        <div class="col-md-6 col-xl-4">
                            <div class="card soft-card hover-lift h-100">
                                <div class="card-body d-flex flex-column p-3 p-md-4">
                                    @php
                                        $imageUrl = $burger->image ? \Illuminate\Support\Facades\Storage::url($burger->image) : null;
                                    @endphp
                                    <div class="burger-thumb mb-3">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $burger->nom }}">
                                        @endif
                                        <div class="burger-thumb-label">
                                            <div class="small text-uppercase opacity-75">{{ $burger->categorie?->nom ?: 'Sans categorie' }}</div>
                                            <div class="fs-4">{{ $burger->nom }}</div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                        <div>
                                            <span class="badge text-bg-success">Disponible</span>
                                            <span class="badge text-bg-light border">Stock: {{ $burger->stock }}</span>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">Prix</div>
                                            <div class="fs-5 fw-semibold">{{ number_format((float) $burger->prix, 2, ',', ' ') }} FCFA</div>
                                        </div>
                                    </div>

                                    @if($burger->description)
                                        <div class="text-muted flex-grow-1 mb-4">{{ $burger->description }}</div>
                                    @else
                                        <div class="flex-grow-1 mb-4"></div>
                                    @endif

                                    <div class="d-flex gap-2 mt-auto">
                                        <a href="{{ route('catalogue.burgers.show', $burger->id) }}" class="btn btn-outline-dark flex-fill">Details</a>
                                        <a href="{{ route('commandes.create', ['burger' => $burger->id]) }}" class="btn btn-warning flex-fill">
                                            Commander
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
    @endif

    <div class="mt-4">
        {{ $burgers->links('pagination::bootstrap-5') }}
    </div>
@endsection
