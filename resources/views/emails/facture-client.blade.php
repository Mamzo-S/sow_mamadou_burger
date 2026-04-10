@php
    $commande = $facture->commande;
    $client = $commande?->client;
    $nomClient = $client ? trim($client->prenom.' '.$client->nom) : 'client';
@endphp

<p>Bonjour {{ $nomClient }},</p>

<p>Votre facture <strong>{{ $facture->reference }}</strong> est disponible.</p>

<p>
    Commande : <strong>{{ $commande?->numero ?: '-' }}</strong><br>
    Date : <strong>{{ $facture->date_generation?->format('d/m/Y H:i') ?: '-' }}</strong><br>
    Montant : <strong>{{ number_format((float) ($commande?->montant_total ?? 0), 2, ',', ' ') }} FCFA</strong>
</p>

<p>La facture est jointe a ce mail au format PDF.</p>

<p>Merci pour votre confiance.</p>

<p>L'equipe ISI Burger</p>
