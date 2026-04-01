<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $facture->reference }}</title>
    <style>
        @page {
            margin: 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: top;
        }

        .brand {
            font-size: 26px;
            font-weight: bold;
            color: #7c2d12;
        }

        .subtitle {
            color: #6b7280;
            margin-top: 4px;
        }

        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            text-align: right;
        }

        .invoice-meta {
            text-align: right;
            line-height: 1.6;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #7c2d12;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .info-table td {
            border: 1px solid #d6d9de;
            padding: 9px 10px;
            vertical-align: top;
        }

        .label {
            width: 27%;
            background: #f8fafc;
            font-weight: bold;
        }

        .client-name {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
        }

        .items th,
        .items td {
            border: 1px solid #d6d9de;
            padding: 10px;
        }

        .items th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            background: #fff7ed;
            font-weight: bold;
        }

        .footer {
            margin-top: 22px;
            text-align: center;
            color: #6b7280;
            font-size: 11px;
        }
    </style>
</head>
<body>
    @php
        $commande = $facture->commande;
        $client = $commande?->client;
        $clientName = $client ? trim($client->prenom . ' ' . $client->nom) : 'Client non renseigne';
        $clientEmail = $client?->email ?: '-';
        $clientTelephone = $client?->telephone ?: '-';
        $clientAdresse = $client?->adresse ?: '-';
    @endphp

    <table class="header">
        <tr>
            <td style="width: 55%;">
                <div class="brand">ISI Burger</div>
                <div class="subtitle">Facture client</div>
            </td>
            <td style="width: 45%;">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-meta">
                    <div><strong>Reference:</strong> {{ $facture->reference }}</div>
                    <div><strong>Date generation:</strong> {{ $facture->date_generation?->format('d/m/Y H:i') ?: '-' }}</div>
                    <div><strong>Commande:</strong> {{ $commande?->numero ?: '-' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Informations client</div>
        <table class="info-table">
            <tr>
                <td class="label">Client</td>
                <td>
                    <div class="client-name">{{ $clientName }}</div>
                    <div>Email: {{ $clientEmail }}</div>
                    <div>Telephone: {{ $clientTelephone }}</div>
                    <div>Adresse: {{ $clientAdresse }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Informations commande</div>
        <table class="info-table">
            <tr>
                <td class="label">Statut</td>
                <td>{{ $commande ? ucfirst(str_replace('_', ' ', $commande->statut)) : '-' }}</td>
                <td class="label">Paiement</td>
                <td>{{ $commande?->paiement ? ucfirst($commande->paiement->mode_paiement) : 'Non enregistre' }}</td>
            </tr>
            <tr>
                <td class="label">Date commande</td>
                <td>{{ $commande?->date_commande?->format('d/m/Y H:i') ?: '-' }}</td>
                <td class="label">Date paiement</td>
                <td>{{ $commande?->date_paiement?->format('d/m/Y H:i') ?: '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detail des articles</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 40%;">Burger</th>
                    <th style="width: 15%;" class="text-right">Quantite</th>
                    <th style="width: 22%;" class="text-right">Prix unitaire</th>
                    <th style="width: 23%;" class="text-right">Sous-total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commande?->ligneCommandes ?? [] as $ligne)
                    <tr>
                        <td>{{ $ligne->burger?->nom ?: '-' }}</td>
                        <td class="text-right">{{ $ligne->quantite }}</td>
                        <td class="text-right">{{ number_format((float) $ligne->prix_unitaire, 2, ',', ' ') }} FCFA</td>
                        <td class="text-right">{{ number_format((float) $ligne->sous_total, 2, ',', ' ') }} FCFA</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Aucune ligne de commande disponible.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total a payer</td>
                    <td class="text-right">{{ number_format((float) ($commande?->montant_total ?? 0), 2, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Merci pour votre commande chez ISI Burger.
    </div>
</body>
</html>
