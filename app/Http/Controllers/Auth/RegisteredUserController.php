<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'nom' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'prenom' => ['required_without:name', 'nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if ((! filled($validated['nom'] ?? null) || ! filled($validated['prenom'] ?? null)) && filled($validated['name'] ?? null)) {
            $parts = preg_split('/\s+/', trim((string) $validated['name']), 2) ?: [];
            $validated['prenom'] = $validated['prenom'] ?? ($parts[0] ?? '');
            $validated['nom'] = $validated['nom'] ?? ($parts[1] ?? ($parts[0] ?? ''));
        }

        $role = Role::firstOrCreate(['libelle' => User::ROLE_CLIENT]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?: null,
            'adresse' => $validated['adresse'] ?: null,
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Compte cree. Bienvenue.');
    }
}
