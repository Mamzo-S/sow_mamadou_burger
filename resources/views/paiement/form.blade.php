@extends('layouts.app')

@section('title', $paiement->exists ? 'Modifier paiement' : 'Ajouter paiement')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card soft-card border-0">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h1 class="h4 mb-0">{{ $paiement->exists ? 'Modifier le paiement' : 'Ajouter un paiement' }}</h1>
                </div>
                <div class="card-body p-4">
                    <form action="{{ $paiement->exists ? route('paiements.update', $paiement->id) : route('paiements.store') }}" method="POST">
                        @csrf

                        @if($paiement->exists)
                            @method('PUT')
                        @endif

                        <input type="hidden" name="enregistre_par" value="{{ old('enregistre_par', $paiement->enregistre_par ?: $defaultManager->id) }}">

                        <div class="mb-3">
                            <label for="commande_id" class="form-label">Commande</label>
                            <select id="commande_id" name="commande_id" class="form-select @error('commande_id') is-invalid @enderror">
                                <option value="">Choisir une commande</option>
                                @foreach($commandes as $commande)
                                <option
                                        value="{{ $commande->id }}"
                                        data-montant="{{ number_format((float) $commande->montant_total, 2, '.', '') }}"
                                        @selected(old('commande_id', $paiement->commande_id ?: ($selectedCommandeId ?? null)) == $commande->id)
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
                                <label for="montant_affiche" class="form-label">Montant de la commande</label>
                                <input
                                    type="text"
                                    id="montant_affiche"
                                    class="form-control"
                                    value="{{ number_format((float) old('montant', $paiement->montant), 2, ',', ' ') }}"
                                    readonly
                                >
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="mode_paiement" class="form-label">Mode de paiement</label>
                                <select id="mode_paiement" name="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror">
                                    <option value="especes" @selected(old('mode_paiement', $paiement->mode_paiement ?: 'especes') === 'especes')>Especes</option>
                                </select>
                                @error('mode_paiement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_paiement" class="form-label">Date paiement</label>
                                <input type="datetime-local" id="date_paiement" name="date_paiement" class="form-control @error('date_paiement') is-invalid @enderror" value="{{ old('date_paiement', $paiement->date_paiement?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}">
                                @error('date_paiement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">{{ $paiement->exists ? 'Mettre a jour' : 'Enregistrer' }}</button>
                            <a href="{{ route('paiements.index') }}" class="btn btn-outline-secondary">Retour</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const commandeSelect = document.getElementById('commande_id');
            const montantField = document.getElementById('montant_affiche');

            if (!commandeSelect || !montantField) {
                return;
            }

            const updateMontant = () => {
                const selectedOption = commandeSelect.options[commandeSelect.selectedIndex];
                const montant = selectedOption ? Number(selectedOption.dataset.montant || 0) : 0;
                montantField.value = montant.toLocaleString('fr-FR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            };

            commandeSelect.addEventListener('change', updateMontant);
            updateMontant();
        })();
    </script>
@endsection
