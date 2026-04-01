<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->enum('statut', [
                'en_attente',
                'en_preparation',
                'prete',
                'payee',
                'annulee'
            ])->default('en_attente');
            $table->decimal('montant_total', 10, 2)->default(0);
            $table->dateTime('date_commande');
            $table->dateTime('date_preparation')->nullable();
            $table->dateTime('date_pret')->nullable();
            $table->dateTime('date_paiement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
