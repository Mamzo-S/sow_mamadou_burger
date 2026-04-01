<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'numero',
        'statut',
        'montant_total',
        'date_commande',
        'date_preparation',
        'date_pret',
        'date_paiement',
    ];

    protected $casts = [
        'montant_total' => 'decimal:2',
        'date_commande' => 'datetime',
        'date_preparation' => 'datetime',
        'date_pret' => 'datetime',
        'date_paiement' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function ligneCommandes()
    {
        return $this->hasMany(LigneCommande::class, 'commande_id');
    }

    public function facture()
    {
        return $this->hasOne(Facture::class, 'commande_id');
    }

    public function paiement()
    {
        return $this->hasOne(Paiement::class, 'commande_id');
    }
}
