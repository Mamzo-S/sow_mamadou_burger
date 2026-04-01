@extends('layouts.app')

@section('title', $commande->exists ? 'Modifier commande' : 'Passer commande')

@section('content')
    @if(! $commande->exists)
        <section class="hero-panel p-4 p-md-5 soft-card mb-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h1 class="display-6 fw-semibold mb-0">Nouvelle commande</h1>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('site.home') }}" class="btn btn-outline-light">Retour au catalogue</a>
                </div>
            </div>
        </section>

        <form action="{{ route('commandes.store') }}" method="POST">
            @csrf

            <input type="hidden" name="client_id" value="{{ old('client_id', $defaultClient->id) }}">
            <input type="hidden" name="numero" value="{{ old('numero', $generatedNumero) }}">
            <input type="hidden" name="statut" value="en_attente">
            <input type="hidden" name="date_commande" value="{{ old('date_commande', now()->format('Y-m-d H:i:s')) }}">

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="row g-4">
                        @if($burgers->isEmpty())
                            <div class="col-12">
                                <div class="soft-card bg-white p-5 text-center">
                                    <h2 class="h5 mb-2">Aucun burger disponible</h2>
                                </div>
                            </div>
                        @else
                            @foreach($burgers as $burger)
                                @php
                                    $imageUrl = $burger->image ? \Illuminate\Support\Facades\Storage::url($burger->image) : null;
                                    $defaultQty = old('quantites.' . $burger->id, $selectedBurgerId === $burger->id ? 1 : 0);
                                @endphp
                                <div class="col-md-6">
                                    <div class="card soft-card hover-lift h-100 border-0">
                                        <div class="card-body p-3 d-flex flex-column">
                                            <div class="burger-thumb mb-3">
                                                @if($imageUrl)
                                                    <img src="{{ $imageUrl }}" alt="{{ $burger->nom }}">
                                                @endif
                                                <div class="burger-thumb-label">
                                                    <div class="small text-uppercase opacity-75">{{ $burger->categorie?->nom ?: 'Sans categorie' }}</div>
                                                    <div class="fs-4">{{ $burger->nom }}</div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                <span class="badge text-bg-dark">{{ $burger->disponible ? 'Disponible' : 'Indisponible' }}</span>
                                                <span class="badge text-bg-light border">Stock: {{ $burger->stock }}</span>
                                            </div>

                                            <div class="fs-5 fw-semibold mb-2">{{ number_format((float) $burger->prix, 2, ',', ' ') }} FCFA</div>
                                            @if($burger->description)
                                                <div class="text-muted flex-grow-1">{{ $burger->description }}</div>
                                            @else
                                                <div class="flex-grow-1"></div>
                                            @endif

                                            <div>
                                                <label for="quantites_{{ $burger->id }}" class="form-label">Quantite</label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max="{{ $burger->stock }}"
                                                    id="quantites_{{ $burger->id }}"
                                                    name="quantites[{{ $burger->id }}]"
                                                    value="{{ $defaultQty }}"
                                                    class="form-control @error('quantites.' . $burger->id) is-invalid @enderror"
                                                    {{ ! $burger->disponible || $burger->stock < 1 ? 'disabled' : '' }}
                                                >
                                                @error('quantites.' . $burger->id)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    @error('quantites')
                        <div class="alert alert-danger border-0 shadow-sm mt-4 mb-0">{{ $message }}</div>
                    @enderror

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-warning btn-lg">
                            <i class="bi bi-bag-check me-1"></i> Valider la commande
                        </button>
                        <a href="{{ route('site.home') }}" class="btn btn-outline-secondary btn-lg">Retour</a>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card soft-card border-0">
                    <div class="card-header bg-white border-0 p-4 pb-0">
                        <h1 class="h4 mb-0">Modifier la commande</h1>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('commandes.update', $commande->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="client_id" value="{{ old('client_id', $commande->client_id ?: $defaultClient->id) }}">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Client</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        value="{{ $commande->client ? $commande->client->prenom . ' ' . $commande->client->nom : $defaultClient->prenom . ' ' . $defaultClient->nom }}"
                                        readonly
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="numero" class="form-label">Numero</label>
                                    <input type="text" id="numero" name="numero" class="form-control @error('numero') is-invalid @enderror" value="{{ old('numero', $commande->numero ?: $generatedNumero) }}">
                                    @error('numero')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="statut" class="form-label">Statut</label>
                                    <select id="statut" name="statut" class="form-select @error('statut') is-invalid @enderror">
                                        @foreach($statuts as $statut)
                                            <option value="{{ $statut }}" @selected(old('statut', $commande->statut ?: 'en_attente') === $statut)>
                                                {{ ucfirst(str_replace('_', ' ', $statut)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('statut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="montant_total" class="form-label">Montant total</label>
                                    <input type="number" step="0.01" min="0" id="montant_total" name="montant_total" class="form-control @error('montant_total') is-invalid @enderror" value="{{ old('montant_total', $commande->montant_total ?? 0) }}">
                                    @error('montant_total')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_commande" class="form-label">Date commande</label>
                                    <input type="datetime-local" id="date_commande" name="date_commande" class="form-control @error('date_commande') is-invalid @enderror" value="{{ old('date_commande', $commande->date_commande?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}">
                                    @error('date_commande')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="date_preparation" class="form-label">Date preparation</label>
                                    <input type="datetime-local" id="date_preparation" name="date_preparation" class="form-control @error('date_preparation') is-invalid @enderror" value="{{ old('date_preparation', $commande->date_preparation?->format('Y-m-d\TH:i')) }}">
                                    @error('date_preparation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_pret" class="form-label">Date pret</label>
                                    <input type="datetime-local" id="date_pret" name="date_pret" class="form-control @error('date_pret') is-invalid @enderror" value="{{ old('date_pret', $commande->date_pret?->format('Y-m-d\TH:i')) }}">
                                    @error('date_pret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="date_paiement" class="form-label">Date paiement</label>
                                    <input type="datetime-local" id="date_paiement" name="date_paiement" class="form-control @error('date_paiement') is-invalid @enderror" value="{{ old('date_paiement', $commande->date_paiement?->format('Y-m-d\TH:i')) }}">
                                    @error('date_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Mettre a jour</button>
                                <a href="{{ route('commandes.index') }}" class="btn btn-outline-secondary">Retour</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
