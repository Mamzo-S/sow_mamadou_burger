<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')
            ->latest()
            ->paginate(10);

        return view('role.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', Rule::unique('roles', 'libelle')],
        ]);

        Role::create($validated);

        return to_route('roles.index')->with('success', 'Role ajoute.');
    }

    public function show(string $id)
    {
        $role = Role::with('users')->findOrFail($id);

        return view('role.show', compact('role'));
    }

    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', Rule::unique('roles', 'libelle')->ignore($role->id)],
        ]);

        $role->update($validated);

        return to_route('roles.index')->with('success', 'Role modifie.');
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return to_route('roles.index')->with('delete', 'Role supprime.');
    }
}
