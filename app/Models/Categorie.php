<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
    ];

    public function burgers()
    {
        return $this->hasMany(Burger::class, 'categorie_id');
    }
}
