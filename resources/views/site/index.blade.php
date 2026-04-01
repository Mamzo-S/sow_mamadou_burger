@extends('layouts.app')

@section('title', 'Catalogue des burgers')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-5 fw-semibold mb-4">Vue d'ensemble</h1>
                <form method="GET" action="{{ route('site.home') }}" class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="q" value="{{ $search }}" class="form-control form-control-lg" placeholder="Libelle ou description">
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
            <div class="col-lg-5">
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
                            <a href="{{ route('site.client') }}" class="btn btn-warning w-100 mb-2">Voir le catalogue</a>
                        </div>
                        <div class="col-12">
                            <a href="{{ route('burgers.index') }}" class="btn btn-dark w-100">Gerer les burgers</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-3">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card p-4 h-100">
                    <div class="text-muted small">Commandes du jour</div>
                    <div class="fs-3 fw-semibold">{{ $stats['commandes_jour'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card p-4 h-100">
                    <div class="text-muted small">Commandes validees</div>
                    <div class="fs-3 fw-semibold">{{ $stats['commandes_validees_jour'] }}</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card p-4 h-100">
                    <div class="text-muted small">Recettes du jour</div>
                    <div class="fs-5 fw-semibold">{{ number_format((float) $stats['recettes_jour'], 2, ',', ' ') }} FCFA</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card p-4 h-100">
                    <div class="text-muted small">Factures du jour</div>
                    <div class="fs-3 fw-semibold">{{ $stats['factures_jour'] }}</div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card soft-card border-0 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-4">Commandes par mois</h2>
                    <div class="chart-shell">
                        <canvas id="ordersByMonthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card soft-card border-0 h-100">
                <div class="card-body p-4">
                    <h2 class="h5 mb-4">Produits commandes par categorie</h2>
                    @if(empty($chartCategorySeries))
                        <div class="text-muted">Aucune donnee disponible.</div>
                    @else
                        <div class="chart-shell">
                            <canvas id="categoryProductsChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('site.home', ['q' => $search, 'prix_min' => $minPrice, 'prix_max' => $maxPrice]) }}"
               class="btn category-chip {{ $selectedCategory ? 'btn-outline-dark' : 'btn-dark' }}">
                Toutes les categories
            </a>
            @foreach($categories as $categorie)
                <a href="{{ route('site.home', ['categorie' => $categorie->id, 'q' => $search, 'prix_min' => $minPrice, 'prix_max' => $maxPrice]) }}"
                   class="btn category-chip {{ $selectedCategory === $categorie->id ? 'btn-warning text-dark' : 'btn-outline-dark' }}">
                    {{ $categorie->nom }} <span class="badge text-bg-light ms-1">{{ $categorie->burgers_count }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section>
        <div class="row g-4">
            @if($burgers->isEmpty())
                <div class="col-12">
                    <div class="soft-card bg-white p-5 text-center">
                        <h2 class="h4 mb-0">Aucun burger trouve</h2>
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
                                        <span class="badge text-bg-dark">{{ $burger->disponible ? 'Disponible' : 'Indisponible' }}</span>
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

                                <div class="d-flex gap-2">
                                    <a href="{{ route('catalogue.burgers.show', $burger->id) }}" class="btn btn-outline-dark flex-fill">Details</a>
                                    <a href="{{ route('commandes.create', ['burger' => $burger->id]) }}"
                                       class="btn btn-warning flex-fill {{ ! $burger->disponible || $burger->stock < 1 ? 'disabled' : '' }}">
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

    <div class="mt-4">
        {{ $burgers->links('pagination::bootstrap-5') }}
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (() => {
            const months = {{ Illuminate\Support\Js::from($chartMonths) }};
            const orderCounts = {{ Illuminate\Support\Js::from($chartOrderCounts) }};
            const categorySeries = {{ Illuminate\Support\Js::from($chartCategorySeries) }};

            const ordersCanvas = document.getElementById('ordersByMonthChart');
            if (ordersCanvas) {
                new Chart(ordersCanvas, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Commandes',
                            data: orderCounts,
                            backgroundColor: 'rgba(217, 119, 6, 0.28)',
                            borderColor: '#d97706',
                            borderWidth: 1.5,
                            borderRadius: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            const categoryCanvas = document.getElementById('categoryProductsChart');
            if (categoryCanvas && categorySeries.length > 0) {
                new Chart(categoryCanvas, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: categorySeries.map((serie) => ({
                            label: serie.label,
                            data: serie.data,
                            borderColor: serie.borderColor,
                            backgroundColor: serie.backgroundColor,
                            tension: 0.35,
                            fill: false
                        }))
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>
@endpush
