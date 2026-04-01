<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    public function index()
    {
        $categories = Categorie::with(['burgers' => function ($query) {
            $query->latest();
        }])
            ->withCount('burgers')
            ->latest()
            ->paginate(9);

        return view('categorie.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('categories', 'nom')],
            'description' => ['nullable', 'string'],
        ]);

        Categorie::create($validated);

        return to_route('categories.index')->with('success', 'Categorie ajoutee.');
    }

    public function show(string $id)
    {
        $categorie = Categorie::with('burgers')->findOrFail($id);

        return view('categorie.show', compact('categorie'));
    }

    public function update(Request $request, string $id)
    {
        $categorie = Categorie::findOrFail($id);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('categories', 'nom')->ignore($categorie->id)],
            'description' => ['nullable', 'string'],
        ]);

        $categorie->update($validated);

        return to_route('categories.index')->with('success', 'Categorie modifiee.');
    }

    public function destroy(string $id)
    {
        $categorie = Categorie::findOrFail($id);
        $categorie->delete();

        return to_route('categories.index')->with('delete', 'Categorie supprimee.');
    }
}
