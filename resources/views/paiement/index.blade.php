@extends('layouts.app')

@section('title', 'Paiements')

@section('content')
    <section class="hero-panel p-4 p-md-5 soft-card mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h1 class="display-6 fw-semibold mb-0">Paiements</h1>
            </div>
            <div class="col-lg-4 text-lg-end">
                <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#createPaiementModal">
                    <i class="bi bi-cash-coin me-1"></i> Ajouter un paiement
                </button>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Paiements du jour</div>
                <div class="fs-2 fw-semibold">{{ $stats['paiements_jour'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card p-4 h-100">
                <div class="text-muted small">Recettes du jour</div>
                <div class="fs-2 fw-semibold">{{ number_format((float) $stats['recettes_jour'], 2, ',', ' ') }} FCFA</div>
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
                            <th>Commande</th>
                            <th>Client</th>
                            <th>Montant</th>
                            <th>Mode</th>
                            <th>Date paiement</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($paiements->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center py-4">Aucun paiement enregistre.</td>
                            </tr>
                        @else
                            @foreach($paiements as $paiement)
                                <tr>
                                    <td>{{ $paiement->id }}</td>
                                    <td>{{ $paiement->commande?->numero ?: '-' }}</td>
                                    <td>{{ $paiement->commande && $paiement->commande->client ? $paiement->commande->client->prenom . ' ' . $paiement->commande->client->nom : '-' }}</td>
                                    <td>{{ number_format((float) $paiement->montant, 2, ',', ' ') }} FCFA</td>
                                    <td>{{ ucfirst($paiement->mode_paiement) }}</td>
                                    <td>{{ $paiement->date_paiement?->format('d/m/Y H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('paiements.show', $paiement->id) }}" class="btn btn-sm btn-outline-secondary">Voir</a>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPaiementModal{{ $paiement->id }}">
                                            Modifier
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deletePaiementModal{{ $paiement->id }}">
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
        {{ $paiements->links('pagination::bootstrap-5') }}
    </div>

    <div class="modal fade" id="createPaiementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <form action="{{ route('paiements.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="enregistre_par" value="{{ old('enregistre_par', $defaultManager->id) }}">

                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Ajouter un paiement</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="create_paiement_commande" class="form-label">Commande</label>
                                <select id="create_paiement_commande" name="commande_id" class="form-select @error('commande_id') is-invalid @enderror">
                                    <option value="">Choisir une commande</option>
                                    @foreach($commandesDisponibles as $commande)
                                    <option
                                        value="{{ $commande->id }}"
                                        data-montant="{{ number_format((float) $commande->montant_total, 2, '.', '') }}"
                                        @selected(old('commande_id') == $commande->id)
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
                                <label for="create_paiement_montant" class="form-label">Montant de la commande</label>
                                <input
                                    type="text"
                                    id="create_paiement_montant"
                                    class="form-control"
                                    value="0,00"
                                    readonly
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="create_paiement_mode" class="form-label">Mode de paiement</label>
                                <select id="create_paiement_mode" name="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                                    <option value="especes" @selected(old('mode_paiement', 'especes') === 'especes')>Especes</option>
                                </select>
                                @error('mode_paiement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="create_paiement_date" class="form-label">Date paiement</label>
                                <input
                                    type="datetime-local"
                                    id="create_paiement_date"
                                    name="date_paiement"
                                    class="form-control @error('date_paiement') is-invalid @enderror"
                                    value="{{ old('date_paiement', now()->format('Y-m-d\TH:i')) }}"
                                >
                                @error('date_paiement')
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

    @foreach($paiements as $paiement)
        <div class="modal fade" id="editPaiementModal{{ $paiement->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <form action="{{ route('paiements.update', $paiement->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="enregistre_par" value="{{ $paiement->enregistre_par ?: $defaultManager->id }}">

                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Modifier le paiement</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_paiement_commande_{{ $paiement->id }}" class="form-label">Commande</label>
                                <select id="edit_paiement_commande_{{ $paiement->id }}" name="commande_id" class="form-select @error('commande_id') is-invalid @enderror">
                                    <option value="">Choisir une commande</option>
                                    @foreach($toutesCommandes as $commande)
                                        @if($commande->paiement && $commande->paiement->id !== $paiement->id)
                                            @continue
                                        @endif
                                        <option
                                            value="{{ $commande->id }}"
                                            data-montant="{{ number_format((float) $commande->montant_total, 2, '.', '') }}"
                                            @selected($paiement->commande_id == $commande->id)
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
                                    <label for="edit_paiement_montant_{{ $paiement->id }}" class="form-label">Montant de la commande</label>
                                    <input
                                        type="text"
                                        id="edit_paiement_montant_{{ $paiement->id }}"
                                        class="form-control"
                                        value="{{ number_format((float) $paiement->montant, 2, ',', ' ') }}"
                                        readonly
                                    >
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_paiement_mode_{{ $paiement->id }}" class="form-label">Mode de paiement</label>
                                    <select id="edit_paiement_mode_{{ $paiement->id }}" name="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                                        <option value="especes" @selected($paiement->mode_paiement === 'especes')>Especes</option>
                                    </select>
                                    @error('mode_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_paiement_date_{{ $paiement->id }}" class="form-label">Date paiement</label>
                                    <input
                                        type="datetime-local"
                                        id="edit_paiement_date_{{ $paiement->id }}"
                                        name="date_paiement"
                                        class="form-control @error('date_paiement') is-invalid @enderror"
                                        value="{{ $paiement->date_paiement?->format('Y-m-d\TH:i') }}"
                                    >
                                @error('date_paiement')
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

        <div class="modal fade" id="deletePaiementModal{{ $paiement->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Confirmer la suppression</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        Voulez-vous vraiment supprimer le paiement lie a la commande <strong>{{ $paiement->commande?->numero ?: '-' }}</strong> ?
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <form action="{{ route('paiements.destroy', $paiement->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        (function () {
            const bindMontantSync = (selectId, fieldId) => {
                const select = document.getElementById(selectId);
                const field = document.getElementById(fieldId);

                if (!select || !field) {
                    return;
                }

                const update = () => {
                    const option = select.options[select.selectedIndex];
                    const montant = option ? Number(option.dataset.montant || 0) : 0;
                    field.value = montant.toLocaleString('fr-FR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                };

                select.addEventListener('change', update);
                update();
            };

            bindMontantSync('create_paiement_commande', 'create_paiement_montant');

            @foreach($paiements as $paiement)
                bindMontantSync('edit_paiement_commande_{{ $paiement->id }}', 'edit_paiement_montant_{{ $paiement->id }}');
            @endforeach
        })();
    </script>
@endsection
