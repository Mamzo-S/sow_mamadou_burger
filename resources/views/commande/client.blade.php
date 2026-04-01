@extends('layouts.app')

@section('title', 'Mes commandes')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-0">Mes commandes</h1>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('site.client') }}" class="btn btn-warning btn-lg">Retour au catalogue</a>
            </div>
        </div>
    </section>

    <section class="row g-4">
        @if($commandes->isEmpty())
            <div class="col-12">
                <div class="soft-card bg-white p-5 text-center">
                    <h2 class="h4 mb-3">Aucune commande pour le moment</h2>
                    <a href="{{ route('site.client') }}" class="btn btn-dark">Voir le catalogue</a>
                </div>
            </div>
        @else
            @foreach($commandes as $commande)
                <div class="col-12">
                    <div class="card soft-card border-0">
                        <div class="card-body p-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                                <div>
                                    <div class="text-muted small">Commande</div>
                                    <h2 class="h4 mb-2">{{ $commande->numero }}</h2>
                                    <div class="small text-muted">{{ $commande->date_commande?->format('d/m/Y H:i') ?: '-' }}</div>
                                </div>
                                <div class="text-lg-end">
                                    <span class="status-pill status-{{ $commande->statut }}">
                                        {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                                    </span>
                                    <div class="fs-5 fw-semibold mt-2">{{ number_format((float) $commande->montant_total, 2, ',', ' ') }} FCFA</div>
                                    <div class="small text-muted">Paiement: {{ $commande->paiement ? 'enregistre' : 'non enregistre' }}</div>
                                </div>
                            </div>

                            <div class="row g-3">
                                @foreach($commande->ligneCommandes as $ligne)
                                    <div class="col-md-6 col-xl-4">
                                        <div class="border rounded-4 p-3 h-100">
                                            <div class="fw-semibold">{{ $ligne->burger?->nom ?: '-' }}</div>
                                            <div class="small text-muted mb-2">{{ $ligne->burger?->categorie?->nom ?: 'Sans categorie' }}</div>
                                            <div class="small">Quantite: {{ $ligne->quantite }}</div>
                                            <div class="small">Prix unitaire: {{ number_format((float) $ligne->prix_unitaire, 2, ',', ' ') }} FCFA</div>
                                            <div class="fw-semibold mt-2">Sous-total: {{ number_format((float) $ligne->sous_total, 2, ',', ' ') }} FCFA</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($commande->facture)
                                <div class="d-flex gap-2 mt-4">
                                    <a href="{{ route('client.factures.show', $commande->facture->id) }}" class="btn btn-outline-dark">Voir la facture</a>
                                    <a href="{{ route('client.factures.pdf', $commande->facture->id) }}" class="btn btn-dark">Telecharger le PDF</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </section>

    <div class="mt-4">
        {{ $commandes->links('pagination::bootstrap-5') }}
    </div>
@endsection
