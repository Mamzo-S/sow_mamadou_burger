<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;

abstract class Controller
{
    protected function defaultClient(): User
    {
        return $this->ensureDefaultUser(
            email: 'client.simple@isi-burger.local',
            defaults: [
                'nom' => 'Client',
                'prenom' => 'Simple',
                'telephone' => null,
                'adresse' => null,
                'password' => 'password',
            ],
            roleLabel: User::ROLE_CLIENT,
        );
    }

    protected function defaultManager(): User
    {
        return $this->ensureDefaultUser(
            email: 'gestionnaire.simple@isi-burger.local',
            defaults: [
                'nom' => 'Gestionnaire',
                'prenom' => 'Simple',
                'telephone' => null,
                'adresse' => null,
                'password' => 'password',
            ],
            roleLabel: User::ROLE_MANAGER,
        );
    }

    protected function defaultAdmin(): User
    {
        return $this->ensureDefaultUser(
            email: 'admin.simple@isi-burger.local',
            defaults: [
                'nom' => 'Admin',
                'prenom' => 'Simple',
                'telephone' => null,
                'adresse' => null,
                'password' => 'password',
            ],
            roleLabel: User::ROLE_ADMIN,
        );
    }

    private function ensureDefaultUser(string $email, array $defaults, string $roleLabel): User
    {
        $role = Role::firstOrCreate(['libelle' => $roleLabel]);

        $user = User::firstOrCreate(
            ['email' => $email],
            $defaults + ['role_id' => $role->id],
        );

        if ($user->role_id !== $role->id) {
            $user->forceFill(['role_id' => $role->id])->save();
        }

        return $user;
    }
}
