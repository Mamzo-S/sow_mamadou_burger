<?php

namespace App\Http\Controllers;

use App\Models\Burger;
use App\Models\Commande;
use App\Models\Facture;
use App\Models\LigneCommande;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommandeController extends Controller
{
    public function myOrders(Request $request)
    {
        $commandes = Commande::with(['ligneCommandes.burger.categorie', 'paiement', 'facture'])
            ->where('client_id', $request->user()->id)
            ->latest('date_commande')
            ->paginate(10);

        return view('commande.client', compact('commandes'));
    }

    public function index()
    {
        $commandes = Commande::with(['client', 'paiement', 'facture', 'ligneCommandes'])
            ->latest()
            ->paginate(10);
        $statuts = $this->statuts();
        $defaultClient = $this->defaultClient();
        $stats = [
            'commandes_jour' => Commande::whereDate('date_commande', today())->count(),
            'commandes_validees_jour' => Commande::whereDate('date_commande', today())
                ->whereIn('statut', ['prete', 'payee'])
                ->count(),
            'recettes_jour' => Paiement::whereDate('date_paiement', today())->sum('montant'),
        ];

        return view('commande.index', compact('commandes', 'statuts', 'stats', 'defaultClient'));
    }

    public function create(Request $request)
    {
        $commande = new Commande;
        $statuts = $this->statuts();
        $generatedNumero = $this->generateNumero();
        $defaultClient = $this->defaultClient();
        $selectedBurgerId = $request->integer('burger') ?: null;
        $burgers = Burger::with('categorie')
            ->where('est_archive', false)
            ->orderBy('nom')
            ->get();
        $selectedBurger = $selectedBurgerId ? $burgers->firstWhere('id', $selectedBurgerId) : null;

        if ($selectedBurgerId && ! $selectedBurger) {
            abort(404);
        }

        return view('commande.form', compact(
            'commande',
            'statuts',
            'selectedBurger',
            'selectedBurgerId',
            'generatedNumero',
            'burgers',
            'defaultClient',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'exists:users,id'],
            'burger_id' => ['nullable', 'exists:burgers,id'],
            'quantite' => ['nullable', 'integer', 'min:1'],
            'quantites' => ['nullable', 'array'],
            'quantites.*' => ['nullable', 'integer', 'min:0'],
            'numero' => ['nullable', 'string', 'max:255', Rule::unique('commandes', 'numero')],
            'statut' => ['nullable', Rule::in($this->statuts())],
            'montant_total' => ['nullable', 'numeric', 'min:0'],
            'date_commande' => ['nullable', 'date'],
            'date_preparation' => ['nullable', 'date'],
            'date_pret' => ['nullable', 'date'],
            'date_paiement' => ['nullable', 'date'],
        ]);

        $lignes = $this->resolveOrderLines($request);

        if ($lignes->isEmpty() && ! isset($validated['montant_total'])) {
            throw ValidationException::withMessages([
                'quantites' => 'Selectionnez au moins un burger ou renseignez un montant.',
            ]);
        }

        $validated['numero'] = $validated['numero'] ?? $this->generateNumero();
        $validated['statut'] = $validated['statut'] ?? 'en_attente';
        $validated['client_id'] = $validated['client_id']
            ?? ($request->user()?->isClient() ? $request->user()->id : $this->defaultClient()->id);
        $validated['date_commande'] = $validated['date_commande'] ?? now()->format('Y-m-d H:i:s');
        $validated['montant_total'] = $lignes->isNotEmpty()
            ? $lignes->sum('sous_total')
            : $validated['montant_total'];

        $payload = $this->normalizeDates($validated, [
            'date_commande',
            'date_preparation',
            'date_pret',
            'date_paiement',
        ]);

        $commandeData = collect($payload)
            ->except(['burger_id', 'quantite', 'quantites'])
            ->toArray();

        $commande = $this->createCommandeWithLines($commandeData, $lignes);

        if ($lignes->isNotEmpty()) {
            return to_route('commandes.show', $commande)->with('success', 'Commande enregistree.');
        }

        return to_route('commandes.index')->with('success', 'Commande enregistree.');
    }

    public function storeForClient(Request $request)
    {
        $request->validate([
            'burger_id' => ['nullable', 'exists:burgers,id'],
            'quantite' => ['nullable', 'integer', 'min:1'],
            'quantites' => ['nullable', 'array'],
            'quantites.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $lignes = $this->resolveOrderLines($request);

        if ($lignes->isEmpty()) {
            throw ValidationException::withMessages([
                'quantites' => 'Selectionnez au moins un burger.',
            ]);
        }

        $this->createCommandeWithLines([
            'client_id' => $request->user()->id,
            'numero' => $this->generateNumero(),
            'statut' => 'en_attente',
            'montant_total' => $lignes->sum('sous_total'),
            'date_commande' => now(),
            'date_preparation' => null,
            'date_pret' => null,
            'date_paiement' => null,
        ], $lignes);

        return to_route('client.orders')->with('success', 'Commande envoyee.');
    }

    public function show(string $id)
    {
        $commande = Commande::with(['client', 'ligneCommandes.burger.categorie', 'paiement', 'facture'])->findOrFail($id);

        return view('commande.show', compact('commande'));
    }

    public function edit(string $id)
    {
        $commande = Commande::findOrFail($id);
        $statuts = $this->statuts();
        $selectedBurger = $commande->ligneCommandes()->with('burger.categorie')->first()?->burger;
        $selectedBurgerId = $selectedBurger?->id;
        $generatedNumero = $commande->numero;
        $defaultClient = $this->defaultClient();
        $burgers = Burger::with('categorie')
            ->where('est_archive', false)
            ->orderBy('nom')
            ->get();

        return view('commande.form', compact(
            'commande',
            'statuts',
            'selectedBurger',
            'selectedBurgerId',
            'generatedNumero',
            'burgers',
            'defaultClient',
        ));
    }

    public function update(Request $request, string $id)
    {
        $commande = Commande::with('ligneCommandes')->findOrFail($id);

        $validated = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'numero' => ['required', 'string', 'max:255', Rule::unique('commandes', 'numero')->ignore($commande->id)],
            'statut' => ['required', Rule::in($this->statuts())],
            'montant_total' => ['required', 'numeric', 'min:0'],
            'date_commande' => ['required', 'date'],
            'date_preparation' => ['nullable', 'date'],
            'date_pret' => ['nullable', 'date'],
            'date_paiement' => ['nullable', 'date'],
        ]);

        $oldStatus = $commande->statut;

        if ($validated['statut'] === 'en_preparation' && empty($validated['date_preparation'])) {
            $validated['date_preparation'] = now();
        }

        if (in_array($validated['statut'], ['prete', 'payee'], true) && empty($validated['date_pret'])) {
            $validated['date_pret'] = now();
        }

        if ($validated['statut'] === 'payee' && empty($validated['date_paiement'])) {
            $validated['date_paiement'] = now();
        }

        $validated = $this->normalizeDates($validated, [
            'date_commande',
            'date_preparation',
            'date_pret',
            'date_paiement',
        ]);

        $this->applyCommandeUpdate($commande, $validated, $oldStatus);

        return to_route('commandes.index')->with('success', 'Commande modifiee.');
    }

    public function updateStatus(Request $request, string $id)
    {
        $commande = Commande::with(['ligneCommandes', 'paiement'])->findOrFail($id);

        $validated = $request->validate([
            'statut' => ['required', Rule::in(['en_attente', 'en_preparation', 'prete', 'annulee'])],
        ]);

        if ($commande->paiement && $validated['statut'] !== 'payee') {
            return back()->with('error', 'Le statut ne peut plus etre change apres le paiement.');
        }

        $oldStatus = $commande->statut;
        $payload = $this->buildQuickStatusPayload($commande, $validated['statut']);

        $this->applyCommandeUpdate($commande, $payload, $oldStatus);

        return back()->with('success', 'Statut mis a jour.');
    }

    public function destroy(string $id)
    {
        $commande = Commande::with('ligneCommandes')->findOrFail($id);

        DB::transaction(function () use ($commande) {
            if ($commande->statut !== 'annulee') {
                $this->restoreStockForCommande($commande);
            }

            $commande->delete();
        });

        return to_route('commandes.index')->with('delete', 'Commande supprimee.');
    }

    private function statuts(): array
    {
        return [
            'en_attente',
            'en_preparation',
            'prete',
            'payee',
            'annulee',
        ];
    }

    private function normalizeDates(array $validated, array $fields): array
    {
        foreach ($fields as $field) {
            if (! empty($validated[$field])) {
                $validated[$field] = Carbon::parse($validated[$field]);
            }
        }

        return $validated;
    }

    private function generateNumero(): string
    {
        return 'CMD-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function generateFactureReference(): string
    {
        do {
            $reference = 'FAC-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Facture::where('reference', $reference)->exists());

        return $reference;
    }

    private function createCommandeWithLines(array $commandeData, Collection $lignes): Commande
    {
        return DB::transaction(function () use ($commandeData, $lignes) {
            $commande = Commande::create($commandeData);

            foreach ($lignes as $ligne) {
                LigneCommande::create([
                    'commande_id' => $commande->id,
                    'burger_id' => $ligne['burger']->id,
                    'quantite' => $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'sous_total' => $ligne['sous_total'],
                ]);

                $this->decreaseBurgerStock($ligne['burger'], $ligne['quantite']);
            }

            if (in_array($commande->statut, ['prete', 'payee'], true)) {
                $this->ensureFactureForCommande($commande);
            }

            return $commande->fresh(['client', 'ligneCommandes.burger', 'paiement', 'facture']);
        });
    }

    private function resolveOrderLines(Request $request): Collection
    {
        $quantites = collect($request->input('quantites', []))
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0);

        if ($quantites->isEmpty() && $request->filled('burger_id')) {
            $quantites = collect([
                (int) $request->input('burger_id') => (int) $request->input('quantite', 1),
            ])->filter(fn ($value) => $value > 0);
        }

        if ($quantites->isEmpty()) {
            return collect();
        }

        $burgerIds = $quantites->keys()->map(fn ($id) => (int) $id)->values();
        $burgers = Burger::whereIn('id', $burgerIds)->get()->keyBy('id');

        if ($burgers->count() !== $burgerIds->count()) {
            throw ValidationException::withMessages([
                'quantites' => 'Un burger selectionne est introuvable.',
            ]);
        }

        return $quantites->map(function ($quantite, $burgerId) use ($burgers) {
            $burger = $burgers->get((int) $burgerId);

            if (! $burger || $burger->est_archive) {
                throw ValidationException::withMessages([
                    'quantites' => 'Un burger selectionne n est plus disponible.',
                ]);
            }

            if (! $burger->disponible || $burger->stock < $quantite) {
                throw ValidationException::withMessages([
                    'quantites' => 'Stock insuffisant pour le burger '.$burger->nom.'.',
                ]);
            }

            return [
                'burger' => $burger,
                'quantite' => $quantite,
                'prix_unitaire' => (float) $burger->prix,
                'sous_total' => (float) $burger->prix * $quantite,
            ];
        })->values();
    }

    private function decreaseBurgerStock(Burger $burger, int $quantite): void
    {
        $freshBurger = Burger::withTrashed()->findOrFail($burger->id);

        if ($freshBurger->stock < $quantite) {
            throw ValidationException::withMessages([
                'quantites' => 'Stock insuffisant pour le burger '.$freshBurger->nom.'.',
            ]);
        }

        $newStock = $freshBurger->stock - $quantite;

        $freshBurger->update([
            'stock' => $newStock,
            'disponible' => $newStock > 0,
        ]);
    }

    private function restoreStockForCommande(Commande $commande): void
    {
        foreach ($commande->ligneCommandes as $ligne) {
            $burger = Burger::withTrashed()->find($ligne->burger_id);

            if (! $burger) {
                continue;
            }

            $burger->update([
                'stock' => $burger->stock + $ligne->quantite,
                'disponible' => true,
            ]);
        }
    }

    private function reserveStockForCommande(Commande $commande): void
    {
        foreach ($commande->ligneCommandes as $ligne) {
            $burger = Burger::withTrashed()->find($ligne->burger_id);

            if (! $burger) {
                continue;
            }

            $this->decreaseBurgerStock($burger, $ligne->quantite);
        }
    }

    private function ensureFactureForCommande(Commande $commande): void
    {
        if ($commande->facture()->exists()) {
            return;
        }

        Facture::create([
            'commande_id' => $commande->id,
            'reference' => $this->generateFactureReference(),
            'fichier_pdf' => null,
            'date_generation' => now(),
        ]);
    }

    private function applyCommandeUpdate(Commande $commande, array $validated, string $oldStatus): void
    {
        DB::transaction(function () use ($commande, $validated, $oldStatus) {
            if ($oldStatus !== 'annulee' && $validated['statut'] === 'annulee') {
                $this->restoreStockForCommande($commande);
            }

            if ($oldStatus === 'annulee' && $validated['statut'] !== 'annulee') {
                $this->reserveStockForCommande($commande);
            }

            $commande->update($validated);

            if (in_array($commande->statut, ['prete', 'payee'], true)) {
                $this->ensureFactureForCommande($commande);
            }
        });
    }

    private function buildQuickStatusPayload(Commande $commande, string $statut): array
    {
        $payload = ['statut' => $statut];

        if ($statut === 'en_attente') {
            $payload['date_preparation'] = null;
            $payload['date_pret'] = null;
            $payload['date_paiement'] = null;
        }

        if ($statut === 'en_preparation') {
            $payload['date_preparation'] = $commande->date_preparation ?? now();
            $payload['date_pret'] = null;
            $payload['date_paiement'] = null;
        }

        if ($statut === 'prete') {
            $payload['date_preparation'] = $commande->date_preparation ?? now();
            $payload['date_pret'] = $commande->date_pret ?? now();
            $payload['date_paiement'] = null;
        }

        if ($statut === 'annulee') {
            $payload['date_paiement'] = null;
        }

        return $payload;
    }
}
