<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MANAGER = 'gestionnaire';

    public const ROLE_CLIENT = 'client';

    protected $fillable = [
        'name',
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'client_id');
    }

    public function paiementsEnregistres()
    {
        return $this->hasMany(Paiement::class, 'enregistre_par');
    }

    public function getNameAttribute(): string
    {
        return trim(collect([$this->prenom, $this->nom])->filter()->implode(' '));
    }

    public function setNameAttribute(string $value): void
    {
        $value = trim($value);

        if ($value === '') {
            $this->attributes['prenom'] = '';
            $this->attributes['nom'] = '';

            return;
        }

        $parts = preg_split('/\s+/', $value, 2) ?: [];

        $this->attributes['prenom'] = $parts[0] ?? $value;
        $this->attributes['nom'] = $parts[1] ?? ($parts[0] ?? $value);
    }

    public function hasRole(string|array $roles): bool
    {
        if (! $this->role) {
            return false;
        }

        $currentRole = Str::lower($this->role->libelle);
        $allowedRoles = collect((array) $roles)
            ->map(fn (string $role) => Str::lower($role))
            ->all();

        return in_array($currentRole, $allowedRoles, true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isManager(): bool
    {
        return $this->hasRole(self::ROLE_MANAGER);
    }

    public function isAdminOrManager(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    public function isClient(): bool
    {
        return $this->hasRole(self::ROLE_CLIENT);
    }
}
