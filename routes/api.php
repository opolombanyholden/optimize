<?php

use App\Http\Controllers\Api\V1\ApproController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\RHController;
use Illuminate\Support\Facades\Route;

// API V1
Route::prefix('v1')->group(function () {

    // Auth publique
    Route::post('/login', [AuthController::class, 'login']);

    // Routes protégées par Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/password', [AuthController::class, 'updatePassword']);

        // Finance
        Route::prefix('finance')->group(function () {
            Route::get('/exercices', [FinanceController::class, 'exercices']);
            Route::get('/exercices/{exercice}', [FinanceController::class, 'showExercice']);
            Route::get('/budgets', [FinanceController::class, 'budgets']);
            Route::get('/budgets/{budgetLigne}', [FinanceController::class, 'showBudget']);
            Route::get('/comptes', [FinanceController::class, 'comptes']);
            Route::get('/grand-livre', [FinanceController::class, 'grandLivre']);
            Route::get('/grand-livre/{grandLivre}', [FinanceController::class, 'showGrandLivre']);
            Route::get('/transactions', [FinanceController::class, 'transactions']);
        });

        // RH
        Route::prefix('rh')->group(function () {
            Route::get('/employees', [RHController::class, 'employees']);
            Route::get('/employees/{employee}', [RHController::class, 'showEmployee']);
            Route::get('/absences', [RHController::class, 'absences']);
            Route::get('/paie', [RHController::class, 'paie']);
            Route::get('/paie/{paie}', [RHController::class, 'showPaie']);
            Route::get('/formations', [RHController::class, 'formations']);
            Route::get('/recrutements', [RHController::class, 'recrutements']);
        });

        // Approvisionnements
        Route::prefix('appro')->group(function () {
            Route::get('/fournisseurs', [ApproController::class, 'fournisseurs']);
            Route::get('/fournisseurs/{fournisseur}', [ApproController::class, 'showFournisseur']);
            Route::get('/produits', [ApproController::class, 'produits']);
            Route::get('/commandes', [ApproController::class, 'commandes']);
            Route::get('/commandes/{commande}', [ApproController::class, 'showCommande']);
            Route::get('/approvisionnements', [ApproController::class, 'approvisionnements']);
        });
    });
});
