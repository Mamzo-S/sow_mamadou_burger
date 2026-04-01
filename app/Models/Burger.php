<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Burger extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categorie_id',
        'nom',
        'description',
        'prix',
        'image',
        'stock',
        'est_archive',
        'disponible',
    ];

    protected $casts = [
        'prix' => 'decimal:2',
        'est_archive' => 'boolean',
        'disponible' => 'boolean',
    ];

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function ligneCommandes()
    {
        return $this->hasMany(LigneCommande::class, 'burger_id');
    }
}
