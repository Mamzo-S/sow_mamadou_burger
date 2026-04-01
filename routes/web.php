<?php

use App\Http\Controllers\BurgerController;
use App\Http\Controllers\CategorieController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

$redirectToSpace = function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    return $user->isAdminOrManager()
        ? redirect()->route('site.home')
        : redirect()->route('site.client');
};

Route::get('/', $redirectToSpace)->name('home');
Route::get('/dashboard', $redirectToSpace)->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin,gestionnaire,client')->group(function () {
        Route::get('/catalogue', [SiteController::class, 'client'])->name('site.client');
        Route::get('/catalogue/burgers/{id}', [BurgerController::class, 'show'])->name('catalogue.burgers.show');
    });

    Route::middleware('role:client')->group(function () {
        Route::get('/mes-commandes', [CommandeController::class, 'myOrders'])->name('client.orders');
        Route::post('/mes-commandes', [CommandeController::class, 'storeForClient'])->name('client.orders.store');
        Route::get('/mes-factures/{id}', [FactureController::class, 'show'])->name('client.factures.show');
        Route::get('/mes-factures/{id}/pdf', [FactureController::class, 'downloadPdf'])->name('client.factures.pdf');
    });

    Route::middleware('role:admin,gestionnaire')->group(function () {
        Route::get('/tableau-de-bord', [SiteController::class, 'index'])->name('site.home');

        Route::resource('burgers', BurgerController::class)->except(['create', 'edit']);
        Route::patch('/burgers/{id}/archive', [BurgerController::class, 'toggleArchive'])->name('burgers.archive');

        Route::resource('categories', CategorieController::class)->except(['create', 'edit']);

        Route::resource('commandes', CommandeController::class);
        Route::patch('/commandes/{id}/statut', [CommandeController::class, 'updateStatus'])->name('commandes.status');

        Route::resource('paiements', PaiementController::class)->except(['edit']);

        Route::resource('factures', FactureController::class)->except(['create', 'edit']);
        Route::get('/factures/{id}/pdf', [FactureController::class, 'downloadPdf'])->name('factures.pdf');

        Route::resource('roles', RoleController::class)->except(['create', 'edit']);
    });
});

require __DIR__.'/auth.php';
