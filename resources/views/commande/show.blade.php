@extends('layouts.app')

@section('title', 'Details commande')

@section('content')
    @php
        $statusLabels = [
            'en_attente' => 'En attente',
            'en_preparation' => 'En preparation',
            'prete' => 'Prete',
            'payee' => 'Payee',
        ];
        $statusKeys = array_keys($statusLabels);
        $currentIndex = array_search($commande->statut, $statusKeys, true);
        $isCancelled = $commande->statut === 'annulee';
    @endphp

    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="text-white-50 mb-2">Commande</div>
                <h1 class="display-6 fw-semibold mb-2">{{ $commande->numero }}</h1>
                <div class="text-white-50">
                    {{ $commande->client ? $commande->client->prenom . ' ' . $commande->client->nom : '-' }}
                    | {{ $commande->date_commande?->format('d/m/Y H:i') ?: '-' }}
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bg-white text-dark rounded-4 p-4">
                    <div class="text-muted small">Montant total</div>
                    <div class="fs-3 fw-semibold mb-3">{{ number_format((float) $commande->montant_total, 2, ',', ' ') }} FCFA</div>
                    <span class="status-pill status-{{ $commande->statut }}">
                        {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="order-card p-4 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h4 mb-1">Progression</h2>
                <div class="order-meta">Mise a jour rapide du cycle de traitement.</div>
            </div>
            <div class="command-link-row">
                <a href="{{ route('commandes.index') }}" class="btn btn-outline-dark">Retour</a>
                <a href="{{ route('commandes.edit', $commande->id) }}" class="btn btn-outline-primary">Modifier</a>
            </div>
        </div>

        @if($isCancelled)
            <div class="order-step is-cancelled mb-4">
                <div class="order-step-head">
                    <span class="order-step-dot"></span>
                    <span class="order-step-label">Commande annulee</span>
                </div>
                <div class="order-step-note">Le stock a ete restitue et la commande est sortie du flux principal.</div>
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
                                Commande creee.
                            @elseif($key === 'en_preparation')
                                Preparation lancee.
                            @elseif($key === 'prete')
                                Facture disponible.
                            @else
                                Paiement enregistre.
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="command-actions">
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
                    <i class="bi bi-receipt me-1"></i> Voir le paiement
                </a>
            @endif

            @if($commande->facture)
                <a href="{{ route('factures.show', $commande->facture->id) }}" class="btn btn-outline-secondary rounded-pill">Voir la facture</a>
            @endif
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Date preparation</div>
                <div class="fs-6 fw-semibold">{{ $commande->date_preparation?->format('d/m/Y H:i') ?: '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Date pret</div>
                <div class="fs-6 fw-semibold">{{ $commande->date_pret?->format('d/m/Y H:i') ?: '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Date paiement</div>
                <div class="fs-6 fw-semibold">{{ $commande->date_paiement?->format('d/m/Y H:i') ?: '-' }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Lignes</div>
                <div class="fs-6 fw-semibold">{{ $commande->ligneCommandes->count() }}</div>
            </div>
        </div>
    </section>

    <section>
        <h2 class="h4 mb-3">Lignes de commande</h2>
        <div class="row g-4">
            @forelse($commande->ligneCommandes as $ligne)
                <div class="col-md-6 col-xl-4">
                    <div class="card soft-card hover-lift h-100 border-0">
                        <div class="card-body p-4 d-flex flex-column">
                            @php
                                $imageUrl = $ligne->burger?->image ? \Illuminate\Support\Facades\Storage::url($ligne->burger->image) : null;
                            @endphp

                            <div class="burger-thumb mb-3">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $ligne->burger?->nom ?: '-' }}">
                                @endif
                                <div class="burger-thumb-label">
                                    <div class="small text-uppercase opacity-75">{{ $ligne->burger?->categorie?->nom ?: 'Sans categorie' }}</div>
                                    <div class="fs-4">{{ $ligne->burger?->nom ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge text-bg-light border">Quantite: {{ $ligne->quantite }}</span>
                                <span class="badge text-bg-light border">PU: {{ number_format((float) $ligne->prix_unitaire, 2, ',', ' ') }} FCFA</span>
                            </div>

                            <div class="mt-auto">
                                <div class="text-muted small">Sous-total</div>
                                <div class="fs-5 fw-semibold">{{ number_format((float) $ligne->sous_total, 2, ',', ' ') }} FCFA</div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="soft-card bg-white p-5 text-center">
                        <h3 class="h5 mb-0">Aucune ligne de commande</h3>
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@endsection
