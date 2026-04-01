<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = collect([
            User::ROLE_ADMIN,
            User::ROLE_MANAGER,
            User::ROLE_CLIENT,
        ])->mapWithKeys(function (string $libelle) {
            $role = Role::firstOrCreate(['libelle' => $libelle]);

            return [$libelle => $role];
        });

        User::updateOrCreate(
            ['email' => 'admin.simple@isi-burger.local'],
            [
                'nom' => 'Admin',
                'prenom' => 'Simple',
                'telephone' => null,
                'adresse' => null,
                'password' => 'password',
                'role_id' => $roles[User::ROLE_ADMIN]->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'gestionnaire.simple@isi-burger.local'],
            [
                'nom' => 'Gestionnaire',
                'prenom' => 'Simple',
                'telephone' => null,
                'adresse' => null,
                'password' => 'password',
                'role_id' => $roles[User::ROLE_MANAGER]->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'client.simple@isi-burger.local'],
            [
                'nom' => 'Client',
                'prenom' => 'Simple',
                'telephone' => null,
                'adresse' => null,
                'password' => 'password',
                'role_id' => $roles[User::ROLE_CLIENT]->id,
            ]
        );
    }
}
