<?php

namespace App\Http\Controllers;

use App\Models\Burger;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BurgerController extends Controller
{
    public function index()
    {
        $burgers = Burger::with('categorie')
            ->latest()
            ->paginate(9);
        $categories = Categorie::withCount('burgers')
            ->orderBy('nom')
            ->get();

        return view('burger.index', compact('burgers', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prix' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'stock' => ['required', 'integer', 'min:0'],
            'categorie_id' => ['nullable', 'exists:categories,id'],
            'disponible' => ['nullable', 'boolean'],
            'est_archive' => ['nullable', 'boolean'],
        ]);

        $validated['est_archive'] = $request->boolean('est_archive');
        $validated['disponible'] = $request->boolean('disponible') && (int) $validated['stock'] > 0 && ! $validated['est_archive'];

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('burgers', 'public');
        } else {
            $validated['image'] = null;
        }

        Burger::create($validated);

        return to_route('burgers.index')->with('success', 'Burger ajoute.');
    }

    public function show(Request $request, string $id)
    {
        $burger = Burger::with('categorie')->findOrFail($id);
        $canManage = $request->user()?->isAdminOrManager() ?? false;
        $returnRoute = $canManage ? 'site.home' : 'site.client';

        return view('burger.show', compact('burger', 'canManage', 'returnRoute'));
    }

    public function update(Request $request, string $id)
    {
        $burger = Burger::findOrFail($id);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prix' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'stock' => ['required', 'integer', 'min:0'],
            'categorie_id' => ['nullable', 'exists:categories,id'],
            'disponible' => ['nullable', 'boolean'],
            'est_archive' => ['nullable', 'boolean'],
        ]);

        $validated['est_archive'] = $request->boolean('est_archive');
        $validated['disponible'] = $request->boolean('disponible') && (int) $validated['stock'] > 0 && ! $validated['est_archive'];

        if ($request->hasFile('image')) {
            if ($burger->image && Storage::disk('public')->exists($burger->image)) {
                Storage::disk('public')->delete($burger->image);
            }

            $validated['image'] = $request->file('image')->store('burgers', 'public');
        } else {
            unset($validated['image']);
        }

        $burger->update($validated);

        return to_route('burgers.index')->with('success', 'Burger modifie.');
    }

    public function toggleArchive(string $id)
    {
        $burger = Burger::findOrFail($id);
        $isArchived = ! $burger->est_archive;

        $burger->update([
            'est_archive' => $isArchived,
            'disponible' => $isArchived ? false : ($burger->stock > 0),
        ]);

        return to_route('burgers.index')->with(
            'success',
            $isArchived ? 'Burger archive.' : 'Burger remis en ligne.'
        );
    }

    public function destroy(string $id)
    {
        $burger = Burger::findOrFail($id);

        if ($burger->image && Storage::disk('public')->exists($burger->image)) {
            Storage::disk('public')->delete($burger->image);
        }

        $burger->delete();

        return to_route('burgers.index')->with('delete', 'Burger supprime.');
    }
}
