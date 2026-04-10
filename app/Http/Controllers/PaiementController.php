<?php

namespace App\Http\Controllers;

use App\Mail\FactureClientMail;
use App\Models\Commande;
use App\Models\Facture;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaiementController extends Controller
{
    public function index()
    {
        $paiements = Paiement::with(['commande.client', 'gestionnaire'])
            ->latest()
            ->paginate(10);
        $commandesDisponibles = Commande::with('client')
            ->whereDoesntHave('paiement')
            ->whereIn('statut', ['prete', 'payee'])
            ->orderByDesc('date_commande')
            ->get();
        $toutesCommandes = Commande::with(['client', 'paiement'])
            ->where(function ($query) {
                $query->whereIn('statut', ['prete', 'payee'])
                    ->orWhereHas('paiement');
            })
            ->orderByDesc('date_commande')
            ->get();
        $defaultManager = auth()->user()?->isAdminOrManager() ? auth()->user() : $this->defaultManager();
        $stats = [
            'paiements_jour' => Paiement::whereDate('date_paiement', today())->count(),
            'recettes_jour' => Paiement::whereDate('date_paiement', today())->sum('montant'),
        ];

        return view('paiement.index', compact('paiements', 'commandesDisponibles', 'toutesCommandes', 'defaultManager', 'stats'));
    }

    public function create(Request $request)
    {
        $paiement = new Paiement;
        $selectedCommandeId = $request->integer('commande');
        $commandes = Commande::with('client')
            ->whereDoesntHave('paiement')
            ->whereIn('statut', ['prete', 'payee'])
            ->orderByDesc('date_commande')
            ->get();
        $defaultManager = auth()->user()?->isAdminOrManager() ? auth()->user() : $this->defaultManager();

        return view('paiement.form', compact('paiement', 'commandes', 'defaultManager', 'selectedCommandeId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'commande_id' => ['required', 'exists:commandes,id', Rule::unique('paiements', 'commande_id')],
            'mode_paiement' => ['required', Rule::in(['especes'])],
            'date_paiement' => ['required', 'date'],
            'enregistre_par' => ['nullable', 'exists:users,id'],
        ]);

        $validated['date_paiement'] = Carbon::parse($validated['date_paiement']);
        $validated['enregistre_par'] = $validated['enregistre_par'] ?? auth()->id() ?? $this->defaultManager()->id;
        $commande = Commande::findOrFail($validated['commande_id']);
        $validated['montant'] = $commande->montant_total;

        $facture = DB::transaction(function () use ($validated, $commande) {
            Paiement::create($validated);

            $commande->update([
                'statut' => 'payee',
                'date_paiement' => $validated['date_paiement'],
            ]);

            return $this->ensureFactureForCommande($commande);
        });

        $this->sendFactureToClient($facture);

        return to_route('paiements.index')->with('success', 'Paiement enregistre.');
    }

    public function show(string $id)
    {
        $paiement = Paiement::with(['commande.client', 'commande.facture', 'gestionnaire'])->findOrFail($id);

        return view('paiement.show', compact('paiement'));
    }

    public function update(Request $request, string $id)
    {
        $paiement = Paiement::findOrFail($id);

        $validated = $request->validate([
            'commande_id' => ['required', 'exists:commandes,id', Rule::unique('paiements', 'commande_id')->ignore($paiement->id)],
            'mode_paiement' => ['required', Rule::in(['especes'])],
            'date_paiement' => ['required', 'date'],
            'enregistre_par' => ['nullable', 'exists:users,id'],
        ]);

        $validated['date_paiement'] = Carbon::parse($validated['date_paiement']);
        $validated['enregistre_par'] = $validated['enregistre_par'] ?? auth()->id() ?? $this->defaultManager()->id;
        $commandeSelectionnee = Commande::findOrFail($validated['commande_id']);
        $validated['montant'] = $commandeSelectionnee->montant_total;

        $shouldSendFacture = $paiement->commande_id !== (int) $validated['commande_id'];

        $facture = DB::transaction(function () use ($paiement, $validated, $commandeSelectionnee) {
            $oldCommandeId = $paiement->commande_id;
            $paiement->update($validated);

            $commandeSelectionnee->update([
                'statut' => 'payee',
                'date_paiement' => $validated['date_paiement'],
            ]);

            if ($oldCommandeId !== $paiement->commande_id) {
                $oldCommande = Commande::find($oldCommandeId);

                if ($oldCommande && ! $oldCommande->paiement()->exists()) {
                    $oldCommande->update([
                        'date_paiement' => null,
                        'statut' => $oldCommande->facture ? 'prete' : 'en_preparation',
                    ]);
                }
            }

            return $this->ensureFactureForCommande($commandeSelectionnee);
        });

        if ($shouldSendFacture) {
            $this->sendFactureToClient($facture);
        }

        return to_route('paiements.index')->with('success', 'Paiement modifie.');
    }

    public function destroy(string $id)
    {
        $paiement = Paiement::findOrFail($id);

        DB::transaction(function () use ($paiement) {
            $commande = $paiement->commande;
            $paiement->delete();

            if ($commande) {
                $commande->update([
                    'date_paiement' => null,
                    'statut' => $commande->facture ? 'prete' : 'en_preparation',
                ]);
            }
        });

        return to_route('paiements.index')->with('delete', 'Paiement supprime.');
    }

    private function ensureFactureForCommande(Commande $commande): Facture
    {
        $factureExistante = $commande->facture()->first();

        if ($factureExistante) {
            return $factureExistante;
        }

        return Facture::create([
            'commande_id' => $commande->id,
            'reference' => $this->generateFactureReference(),
            'fichier_pdf' => null,
            'date_generation' => now(),
        ]);
    }

    private function sendFactureToClient(Facture $facture): void
    {
        $facture->loadMissing(['commande.client', 'commande.ligneCommandes.burger', 'commande.paiement']);

        $email = $facture->commande?->client?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new FactureClientMail($facture));
    }

    private function generateFactureReference(): string
    {
        do {
            $reference = 'FAC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Facture::where('reference', $reference)->exists());

        return $reference;
    }
}
