@extends('layouts.app')

@section('title', 'Factures')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-0">Factures</h1>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#createFactureModal">
                    <i class="bi bi-receipt-cutoff me-1"></i> Ajouter une facture
                </button>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Factures du jour</div>
                <div class="fs-2 fw-semibold">{{ $stats['factures_jour'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Commandes facturables</div>
                <div class="fs-2 fw-semibold">{{ $commandesDisponibles->count() }}</div>
            </div>
        </div>
    </div>

    <div class="table-shell bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Commande</th>
                            <th>Client</th>
                            <th>Date generation</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($factures->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center py-4">Aucune facture enregistree.</td>
                            </tr>
                        @else
                            @foreach($factures as $facture)
                                <tr>
                                    <td>{{ $facture->id }}</td>
                                    <td>{{ $facture->reference }}</td>
                                    <td>{{ $facture->commande?->numero ?: '-' }}</td>
                                    <td>{{ $facture->commande && $facture->commande->client ? $facture->commande->client->prenom . ' ' . $facture->commande->client->nom : '-' }}</td>
                                    <td>{{ $facture->date_generation?->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('factures.show', $facture->id) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
                                        <a href="{{ route('factures.pdf', $facture->id) }}" class="btn btn-sm btn-outline-dark">PDF</a>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editFactureModal{{ $facture->id }}">
                                            Modifier
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteFactureModal{{ $facture->id }}">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $factures->links('pagination::bootstrap-5') }}
    </div>

    <div class="modal fade" id="createFactureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <form action="{{ route('factures.store') }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Ajouter une facture</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_facture_commande" class="form-label">Commande</label>
                                <select id="create_facture_commande" name="commande_id" class="form-select @error('commande_id') is-invalid @enderror">
                                    <option value="">Choisir une commande</option>
                                    @foreach($commandesDisponibles as $commande)
                                    <option value="{{ $commande->id }}" @selected(old('commande_id') == $commande->id)>
                                        {{ $commande->numero }} - {{ $commande->client ? $commande->client->prenom . ' ' . $commande->client->nom : 'Sans client' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('commande_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="create_facture_reference" class="form-label">Reference</label>
                                <input
                                    type="text"
                                    id="create_facture_reference"
                                    name="reference"
                                    class="form-control @error('reference') is-invalid @enderror"
                                    value="{{ old('reference') }}"
                                >
                                @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="create_facture_date" class="form-label">Date generation</label>
                                <input
                                    type="datetime-local"
                                    id="create_facture_date"
                                    name="date_generation"
                                    class="form-control @error('date_generation') is-invalid @enderror"
                                    value="{{ old('date_generation', now()->format('Y-m-d\TH:i')) }}"
                                >
                                @error('date_generation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach($factures as $facture)
        <div class="modal fade" id="editFactureModal{{ $facture->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <form action="{{ route('factures.update', $facture->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Modifier la facture</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_facture_commande_{{ $facture->id }}" class="form-label">Commande</label>
                                <select id="edit_facture_commande_{{ $facture->id }}" name="commande_id" class="form-select @error('commande_id') is-invalid @enderror">
                                    <option value="">Choisir une commande</option>
                                    @foreach($toutesCommandes as $commande)
                                        @if($commande->facture && $commande->facture->id !== $facture->id)
                                            @continue
                                        @endif
                                        <option
                                            value="{{ $commande->id }}"
                                            @selected($facture->commande_id == $commande->id)
                                        >
                                            {{ $commande->numero }} - {{ $commande->client ? $commande->client->prenom . ' ' . $commande->client->nom : 'Sans client' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('commande_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_facture_reference_{{ $facture->id }}" class="form-label">Reference</label>
                                    <input
                                        type="text"
                                        id="edit_facture_reference_{{ $facture->id }}"
                                        name="reference"
                                        class="form-control @error('reference') is-invalid @enderror"
                                        value="{{ $facture->reference }}"
                                    >
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_facture_date_{{ $facture->id }}" class="form-label">Date generation</label>
                                    <input
                                        type="datetime-local"
                                        id="edit_facture_date_{{ $facture->id }}"
                                        name="date_generation"
                                        class="form-control @error('date_generation') is-invalid @enderror"
                                        value="{{ $facture->date_generation?->format('Y-m-d\TH:i') }}"
                                    >
                                    @error('date_generation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Mettre a jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteFactureModal{{ $facture->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Confirmer la suppression</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        Voulez-vous vraiment supprimer la facture <strong>{{ $facture->reference }}</strong> ?
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form action="{{ route('factures.destroy', $facture->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
