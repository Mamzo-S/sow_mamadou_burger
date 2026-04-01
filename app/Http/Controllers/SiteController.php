<?php

namespace App\Http\Controllers;

use App\Models\Burger;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Facture;
use App\Models\Paiement;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $selectedCategory = $request->integer('categorie');
        $search = trim((string) $request->input('q'));
        $minPrice = $request->filled('prix_min') ? (float) $request->input('prix_min') : null;
        $maxPrice = $request->filled('prix_max') ? (float) $request->input('prix_max') : null;
        $chartMonths = ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec'];
        $chartOrderCounts = array_fill(0, count($chartMonths), 0);
        $chartCategorySeries = [];

        try {
            $categories = Categorie::withCount(['burgers' => function ($query) {
                $query->where('est_archive', false);
            }])
                ->orderBy('nom')
                ->get();

            $burgers = Burger::with('categorie')
                ->where('est_archive', false)
                ->when($selectedCategory, function ($query) use ($selectedCategory) {
                    $query->where('categorie_id', $selectedCategory);
                })
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('nom', 'like', '%'.$search.'%')
                            ->orWhere('description', 'like', '%'.$search.'%');
                    });
                })
                ->when($minPrice !== null, function ($query) use ($minPrice) {
                    $query->where('prix', '>=', $minPrice);
                })
                ->when($maxPrice !== null, function ($query) use ($maxPrice) {
                    $query->where('prix', '<=', $maxPrice);
                })
                ->latest()
                ->paginate(9)
                ->withQueryString();
            $stats = [
                'categories' => $categories->count(),
                'burgers' => $burgers->total(),
                'commandes_jour' => Commande::whereDate('date_commande', today())->count(),
                'commandes_validees_jour' => Commande::whereDate('date_commande', today())
                    ->whereIn('statut', ['prete', 'payee'])
                    ->count(),
                'recettes_jour' => Paiement::whereDate('date_paiement', today())->sum('montant'),
                'factures_jour' => Facture::whereDate('date_generation', today())->count(),
            ];
            $currentYear = (int) now()->year;

            foreach (array_keys($chartMonths) as $index) {
                $month = $index + 1;
                $chartOrderCounts[$index] = Commande::whereYear('date_commande', $currentYear)
                    ->whereMonth('date_commande', $month)
                    ->count();
            }

            $palette = [
                ['border' => '#6b3b19', 'background' => 'rgba(107, 59, 25, 0.18)'],
                ['border' => '#d97706', 'background' => 'rgba(217, 119, 6, 0.18)'],
                ['border' => '#15803d', 'background' => 'rgba(21, 128, 61, 0.18)'],
                ['border' => '#1d4ed8', 'background' => 'rgba(29, 78, 216, 0.18)'],
                ['border' => '#be123c', 'background' => 'rgba(190, 18, 60, 0.18)'],
                ['border' => '#7c3aed', 'background' => 'rgba(124, 58, 237, 0.18)'],
            ];

            $chartCategorySeries = $categories
                ->values()
                ->map(function ($categorie, $index) use ($chartMonths, $currentYear, $palette) {
                    $colors = $palette[$index % count($palette)];
                    $data = [];

                    foreach (array_keys($chartMonths) as $monthIndex) {
                        $month = $monthIndex + 1;
                        $data[] = (float) Burger::query()
                            ->join('ligne_commandes', 'burgers.id', '=', 'ligne_commandes.burger_id')
                            ->join('commandes', 'ligne_commandes.commande_id', '=', 'commandes.id')
                            ->where('burgers.categorie_id', $categorie->id)
                            ->whereYear('commandes.date_commande', $currentYear)
                            ->whereMonth('commandes.date_commande', $month)
                            ->sum('ligne_commandes.quantite');
                    }

                    return [
                        'label' => $categorie->nom,
                        'data' => $data,
                        'borderColor' => $colors['border'],
                        'backgroundColor' => $colors['background'],
                    ];
                })
                ->filter(fn (array $serie) => collect($serie['data'])->sum() > 0)
                ->values()
                ->all();
        } catch (Throwable) {
            $categories = collect();
            $burgers = new LengthAwarePaginator([], 0, 9);
            $stats = [
                'categories' => 0,
                'burgers' => 0,
                'commandes_jour' => 0,
                'commandes_validees_jour' => 0,
                'recettes_jour' => 0,
                'factures_jour' => 0,
            ];
        }

        return view('site.index', compact(
            'categories',
            'burgers',
            'selectedCategory',
            'search',
            'minPrice',
            'maxPrice',
            'stats',
            'chartMonths',
            'chartOrderCounts',
            'chartCategorySeries',
        ));
    }

    public function client(Request $request)
    {
        $selectedCategory = $request->integer('categorie');
        $search = trim((string) $request->input('q'));
        $minPrice = $request->filled('prix_min') ? (float) $request->input('prix_min') : null;
        $maxPrice = $request->filled('prix_max') ? (float) $request->input('prix_max') : null;

        try {
            $categories = Categorie::withCount(['burgers as burgers_disponibles_count' => function ($query) {
                $query->where('est_archive', false)
                    ->where('disponible', true)
                    ->where('stock', '>', 0);
            }])
                ->orderBy('nom')
                ->get()
                ->filter(fn ($categorie) => $categorie->burgers_disponibles_count > 0)
                ->values();

            $burgers = Burger::with('categorie')
                ->where('est_archive', false)
                ->where('disponible', true)
                ->where('stock', '>', 0)
                ->when($selectedCategory, function ($query) use ($selectedCategory) {
                    $query->where('categorie_id', $selectedCategory);
                })
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('nom', 'like', '%'.$search.'%')
                            ->orWhere('description', 'like', '%'.$search.'%');
                    });
                })
                ->when($minPrice !== null, function ($query) use ($minPrice) {
                    $query->where('prix', '>=', $minPrice);
                })
                ->when($maxPrice !== null, function ($query) use ($maxPrice) {
                    $query->where('prix', '<=', $maxPrice);
                })
                ->orderBy('nom')
                ->paginate(9)
                ->withQueryString();

            $stats = [
                'categories' => $categories->count(),
                'burgers' => $burgers->total(),
            ];
        } catch (Throwable) {
            $categories = collect();
            $burgers = new LengthAwarePaginator([], 0, 9);
            $stats = [
                'categories' => 0,
                'burgers' => 0,
            ];
        }

        return view('site.client', compact('categories', 'burgers', 'selectedCategory', 'search', 'minPrice', 'maxPrice', 'stats'));
    }
}
