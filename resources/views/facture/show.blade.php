@extends('layouts.app')

@section('title', 'Details facture')

@section('content')
    @php
        $isClient = auth()->user()?->isClient();
    @endphp

    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-3">{{ $facture->reference }}</h1>
                <div class="text-white-50">Commande: {{ $facture->commande?->numero ?: '-' }}</div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="bg-white text-dark rounded-4 p-4">
                    <div class="small text-muted">Date generation</div>
                    <div class="fs-5 fw-semibold mb-3">{{ $facture->date_generation?->format('d/m/Y H:i') ?: '-' }}</div>
                    <a href="{{ route($isClient ? 'client.factures.pdf' : 'factures.pdf', $facture->id) }}" class="btn btn-dark w-100 mb-2">Telecharger le PDF</a>
                    <a href="{{ $isClient ? route('client.orders') : route('factures.index') }}" class="btn btn-outline-dark w-100 mb-2">
                        {{ $isClient ? 'Retour aux commandes' : 'Retour aux factures' }}
                    </a>
                    @if(! $isClient && $facture->commande?->paiement)
                        <a href="{{ route('paiements.show', $facture->commande->paiement->id) }}" class="btn btn-outline-secondary w-100">Voir le paiement</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Client</div>
                <div class="fs-6 fw-semibold">{{ $facture->commande && $facture->commande->client ? $facture->commande->client->prenom . ' ' . $facture->commande->client->nom : '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Statut commande</div>
                <div class="fs-6 fw-semibold">{{ $facture->commande ? ucfirst(str_replace('_', ' ', $facture->commande->statut)) : '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Montant total</div>
                <div class="fs-6 fw-semibold">{{ number_format((float) ($facture->commande?->montant_total ?? 0), 2, ',', ' ') }} FCFA</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">PDF</div>
                <div class="fs-6 fw-semibold">{{ $facture->fichier_pdf ?: 'Non genere' }}</div>
            </div>
        </div>
    </section>

    <section class="table-shell bg-white">
        <div class="p-4 border-bottom">
            <h2 class="h4 mb-0">Detail de la commande</h2>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Burger</th>
                        <th>Quantite</th>
                        <th>Prix unitaire</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    @if(! $facture->commande || $facture->commande->ligneCommandes->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center py-4">Aucune ligne de commande disponible.</td>
                        </tr>
                    @else
                        @foreach($facture->commande->ligneCommandes as $ligne)
                            <tr>
                                <td>{{ $ligne->burger?->nom ?: '-' }}</td>
                                <td>{{ $ligne->quantite }}</td>
                                <td>{{ number_format((float) $ligne->prix_unitaire, 2, ',', ' ') }} FCFA</td>
                                <td>{{ number_format((float) $ligne->sous_total, 2, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </section>
@endsection
