@extends('layouts.app')

@section('title', 'Commandes')

@section('content')
    @php
        $statusLabels = [
            'en_attente' => 'En attente',
            'en_preparation' => 'En preparation',
            'prete' => 'Prete',
            'payee' => 'Payee',
        ];
    @endphp

    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-2">Gestion des commandes</h1>
                <div class="text-white-50">Suivi des statuts, annulation, facture et paiement depuis une seule vue.</div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('commandes.create') }}" class="btn btn-warning btn-lg">
                        <i class="bi bi-plus-lg me-1"></i> Nouvelle commande
                    </a>
                    <a href="{{ route('paiements.index') }}" class="btn btn-outline-light">Voir les paiements</a>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Commandes du jour</div>
                <div class="fs-2 fw-semibold">{{ $stats['commandes_jour'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Commandes validees aujourd'hui</div>
                <div class="fs-2 fw-semibold">{{ $stats['commandes_validees_jour'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Recettes du jour</div>
                <div class="fs-2 fw-semibold">{{ number_format((float) $stats['recettes_jour'], 2, ',', ' ') }} FCFA</div>
            </div>
        </div>
    </div>

    <section class="row g-4">
        @forelse($commandes as $commande)
            @php
                $statusKeys = array_keys($statusLabels);
                $currentIndex = array_search($commande->statut, $statusKeys, true);
                $isCancelled = $commande->statut === 'annulee';
            @endphp

            <div class="col-12">
                <article class="order-card p-4">
                    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3 mb-4">
                        <div>
                            <div class="order-meta mb-1">Commande</div>
                            <h2 class="h3 mb-2">{{ $commande->numero }}</h2>
                            <div class="order-meta">
                                {{ $commande->client ? $commande->client->prenom . ' ' . $commande->client->nom : $defaultClient->prenom . ' ' . $defaultClient->nom }}
                                | {{ $commande->date_commande?->format('d/m/Y H:i') ?: '-' }}
                            </div>
                        </div>

                        <div class="text-xl-end">
                            <span class="status-pill status-{{ $commande->statut }}">
                                {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                            </span>
                            <div class="fs-4 fw-semibold mt-2">{{ number_format((float) $commande->montant_total, 2, ',', ' ') }} FCFA</div>
                            <div class="order-meta mt-1">
                                {{ $commande->ligneCommandes->count() }} ligne(s)
                                | Facture: {{ $commande->facture ? 'Oui' : 'Non' }}
                                | Paiement: {{ $commande->paiement ? 'Oui' : 'Non' }}
                            </div>
                        </div>
                    </div>

                    @if($isCancelled)
                        <div class="order-step is-cancelled mb-4">
                            <div class="order-step-head">
                                <span class="order-step-dot"></span>
                                <span class="order-step-label">Commande annulee</span>
                            </div>
                            <div class="order-step-note">Le stock a ete restaure et la commande n'avance plus dans le cycle de traitement.</div>
                        </div>
                    @else
                        <div class="order-stepper mb-4">
                            @foreach($statusLabels as $key => $label)
                                @php
                                    $stepIndex = array_search($key, $statusKeys, true);
                                    $stepClass = 'order-step';
                                    if ($currentIndex !== false && $stepIndex < $currentIndex) {
                                        $stepClass .= ' is-done';
                                    }
                                    if ($commande->statut === $key) {
                                        $stepClass .= ' is-current';
                                    }
                                @endphp
                                <div class="{{ $stepClass }}">
                                    <div class="order-step-head">
                                        <span class="order-step-dot"></span>
                                        <span class="order-step-label">{{ $label }}</span>
                                    </div>
                                    <div class="order-step-note">
                                        @if($key === 'en_attente')
                                            Commande creee et en attente de traitement.
                                        @elseif($key === 'en_preparation')
                                            Preparation en cuisine en cours.
                                        @elseif($key === 'prete')
                                            Commande terminee, facture disponible.
                                        @else
                                            Paiement enregistre.
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="command-actions mb-3">
                        @foreach(['en_attente', 'en_preparation', 'prete'] as $quickStatus)
                            <form action="{{ route('commandes.status', $commande->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="statut" value="{{ $quickStatus }}">
                                <button
                                    type="submit"
                                    class="command-chip {{ $commande->statut === $quickStatus ? 'is-active' : '' }}"
                                    {{ $commande->paiement ? 'disabled' : '' }}
                                >
                                    {{ ucfirst(str_replace('_', ' ', $quickStatus)) }}
                                </button>
                            </form>
                        @endforeach

                        @if(! $commande->paiement && $commande->statut !== 'annulee')
                            <form action="{{ route('commandes.status', $commande->id) }}" method="POST" onsubmit="return confirm('Annuler cette commande ?');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="statut" value="annulee">
                                <button type="submit" class="btn btn-outline-danger rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> Annuler la commande
                                </button>
                            </form>
                        @endif

                        @if(! $commande->paiement && in_array($commande->statut, ['prete', 'payee'], true))
                            <a href="{{ route('paiements.create', ['commande' => $commande->id]) }}" class="btn btn-dark rounded-pill">
                                <i class="bi bi-cash-coin me-1"></i> Enregistrer le paiement
                            </a>
                        @elseif($commande->paiement)
                            <a href="{{ route('paiements.show', $commande->paiement->id) }}" class="btn btn-dark rounded-pill">
                                <i class="bi bi-receipt me-1"></i> Paiement enregistre
                            </a>
                        @endif
                    </div>

                    <div class="command-link-row">
                        <a href="{{ route('commandes.show', $commande->id) }}" class="btn btn-outline-dark">Details</a>
                        <a href="{{ route('commandes.edit', $commande->id) }}" class="btn btn-outline-primary">Modifier</a>
                        @if($commande->facture)
                            <a href="{{ route('factures.show', $commande->facture->id) }}" class="btn btn-outline-secondary">Facture</a>
                        @endif
                        <form action="{{ route('commandes.destroy', $commande->id) }}" method="POST" onsubmit="return confirm('Supprimer cette commande ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Supprimer</button>
                        </form>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="soft-card bg-white p-5 text-center">
                    <h2 class="h4 mb-0">Aucune commande enregistree</h2>
                </div>
            </div>
        @endforelse
    </section>

    <div class="mt-4">
        {{ $commandes->links('pagination::bootstrap-5') }}
    </div>
@endsection
