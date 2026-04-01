@extends('layouts.app')

@section('title', 'Details paiement')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-3">Paiement #{{ $paiement->id }}</h1>
                <div class="text-white-50">Commande: {{ $paiement->commande?->numero ?: '-' }}</div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="bg-white text-dark rounded-4 p-4">
                    <div class="small text-muted">Montant encaisse</div>
                    <div class="fs-3 fw-semibold mb-3">{{ number_format((float) $paiement->montant, 2, ',', ' ') }} FCFA</div>
                    <a href="{{ route('paiements.index') }}" class="btn btn-outline-dark w-100 mb-2">Retour aux paiements</a>
                    @if($paiement->commande?->facture)
                        <a href="{{ route('factures.show', $paiement->commande->facture->id) }}" class="btn btn-outline-secondary w-100">Voir la facture</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Client</div>
                <div class="fs-6 fw-semibold">{{ $paiement->commande && $paiement->commande->client ? $paiement->commande->client->prenom . ' ' . $paiement->commande->client->nom : '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Mode</div>
                <div class="fs-6 fw-semibold">{{ ucfirst($paiement->mode_paiement) }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Date paiement</div>
                <div class="fs-6 fw-semibold">{{ $paiement->date_paiement?->format('d/m/Y H:i') ?: '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Enregistre par</div>
                <div class="fs-6 fw-semibold">{{ $paiement->gestionnaire ? $paiement->gestionnaire->prenom . ' ' . $paiement->gestionnaire->nom : '-' }}</div>
            </div>
        </div>
    </section>
@endsection
