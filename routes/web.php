<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\ExerciceController;
use App\Http\Controllers\Finance\BudgetController;
use App\Http\Controllers\Finance\CompteController;
use App\Http\Controllers\Finance\GrandLivreController;
use App\Http\Controllers\RH\EmployeeController;
use App\Http\Controllers\RH\AbsenceController;
use App\Http\Controllers\RH\PaieController;
use App\Http\Controllers\RH\RecrutementController;
use App\Http\Controllers\RH\AffilieController;
use App\Http\Controllers\RH\QualificationController;
use App\Http\Controllers\RH\CompetenceController;
use App\Http\Controllers\RH\FormationController;
use App\Http\Controllers\RH\MissionController;
use App\Http\Controllers\RH\RubriqueController;
use App\Http\Controllers\RH\EvenementCarriereController;
use App\Http\Controllers\RH\SanctionController;
use App\Http\Controllers\RH\DepartController;
use App\Http\Controllers\RH\EvaluationPerformanceController;
use App\Http\Controllers\RH\CongeSoldeController;
use App\Http\Controllers\RH\PlanningController;
use App\Http\Controllers\RH\PayementController;
use App\Http\Controllers\RH\PayementGlobalController;
use App\Http\Controllers\RH\RhDashboardController;
use App\Http\Controllers\RH\CampagnePaieController;
use App\Http\Controllers\RH\DeclarationSocialeController;
use App\Http\Controllers\RH\EmployeePasswordController;
use App\Http\Controllers\RH\EchantillonPaieController;
use App\Http\Controllers\RH\PostulantController;
use App\Http\Controllers\RH\PointageController;
use App\Http\Controllers\RH\PointageQrController;
use App\Http\Controllers\RH\PointageSelfController;
use App\Http\Controllers\RH\PointageGeneriqueController;
use App\Http\Controllers\Appro\FournisseurController;
use App\Http\Controllers\Appro\CommandeController;
use App\Http\Controllers\MG\ImmobilisationController;
use App\Http\Controllers\MG\DysfonctionnementController;
use App\Http\Controllers\MG\InterventionController;
use App\Http\Controllers\Systeme\OrganisationController;
use App\Http\Controllers\Intranet\AnnonceController;
use App\Http\Controllers\Intranet\NewsController;
use App\Http\Controllers\Intranet\EvenementController;
use App\Http\Controllers\Intranet\ContactController;
use App\Http\Controllers\Intranet\ContactOrganisationController;
use App\Http\Controllers\Intranet\OpportuniteController;
use App\Http\Controllers\Intranet\CourrierController;
use App\Http\Controllers\Intranet\RessourceController;
use App\Http\Controllers\Intranet\MediathequeController;

// Page d'accueil publique (vitrine)
Route::get('/', [\App\Http\Controllers\VitrineController::class, 'home'])->name('home');

// Auth
// ─── ROUTES PUBLIQUES POINTAGE QR ──────────────────────
// L'employé scanne le QR avec son téléphone (pas de session Laravel)
Route::get('/p/qr/{token}', [PointageQrController::class, 'scan'])->name('pointage.qr.scan');
Route::post('/p/qr/{token}', [PointageQrController::class, 'confirmer'])->name('pointage.qr.confirmer');
// QR générique partagé (affiché à l'entrée) — auth par matricule + PIN
Route::get('/p/g/{token}', [PointageGeneriqueController::class, 'show'])->name('pointage.qr-generique.show');
Route::post('/p/g/{token}', [PointageGeneriqueController::class, 'check'])->name('pointage.qr-generique.check');

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Changement de mot de passe forcé (post-reset admin) — accessible avant que le middleware ne bloque
Route::middleware(['auth'])->group(function () {
    Route::get('/password/force-change', [App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])
        ->name('password.force-change.show');
    Route::post('/password/force-change', [App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])
        ->name('password.force-change.update');
});

// Routes protégées
Route::middleware(['auth'])->group(function () {

    // ═════════ GUIDE & DOCS — Manuels utilisateurs ═════════
    Route::prefix('docs')->name('docs.')->group(function () {
        $dc = \App\Http\Controllers\DocsController::class;
        Route::get('/',              [$dc, 'index'])->name('index');
        Route::get('/{slug}',        [$dc, 'show'])->name('show');
        Route::get('/{slug}/pdf',    [$dc, 'pdf'])->name('pdf');
    });

    // ═════════ SOCIAL — Réseau social interne ═════════
    Route::prefix('social')->name('social.')->group(function () {
        $sc = \App\Http\Controllers\Social\SocialController::class;
        Route::get('/',                    [$sc, 'dashboard'])->name('dashboard');
        Route::get('/badges',              [$sc, 'badges'])->name('badges');
        Route::post('/posts',              [$sc, 'store'])->name('posts.store');
        Route::get('/posts/{post}',        [$sc, 'showPost'])->name('posts.show');
        Route::put('/posts/{post}',        [$sc, 'update'])->name('posts.update');
        Route::delete('/posts/{post}',     [$sc, 'destroy'])->name('posts.destroy');
        Route::post('/posts/{post}/like',  [$sc, 'toggleLike'])->name('posts.like');
        Route::post('/posts/{post}/comment', [$sc, 'comment'])->name('posts.comment');
        Route::post('/media/{media}/like',    [$sc, 'toggleMediaLike'])->name('media.like');
        Route::post('/media/{media}/comment', [$sc, 'commentMedia'])->name('media.comment');

        // Groupes de discussion (chat)
        $gc = \App\Http\Controllers\Social\GroupController::class;
        Route::get('/groups',                   [$gc, 'index'])->name('groups.index');
        Route::post('/groups',                  [$gc, 'store'])->name('groups.store');
        Route::get('/groups/{group}',           [$gc, 'show'])->name('groups.show');
        Route::post('/groups/{group}/messages', [$gc, 'postMessage'])->name('groups.postMessage');
        Route::get('/groups/{group}/messages',  [$gc, 'fetchMessages'])->name('groups.fetchMessages');
        Route::post('/groups/{group}/invite',   [$gc, 'invite'])->name('groups.invite');
        Route::post('/groups/{group}/leave',    [$gc, 'leave'])->name('groups.leave');
        Route::delete('/groups/{group}',        [$gc, 'destroy'])->name('groups.destroy');
        Route::get('/shareable/search',         [$gc, 'searchShareable'])->name('shareable.search');
        Route::get('/mention/autocomplete',     [$gc, 'mentionAutocomplete'])->name('mention.autocomplete');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ─── POINTAGE SELF-SERVICE (tout utilisateur connecté lié à un employé) ─
    Route::get('/pointage', [PointageSelfController::class, 'show'])->name('pointage.self');
    Route::post('/pointage', [PointageSelfController::class, 'pointer'])->name('pointage.self.pointer');
    // Validation N+1 (le manager du collaborateur ou super-admin)
    Route::get('/pointage/validation-n1', [PointageSelfController::class, 'validationN1Index'])->name('pointage.validation-n1');
    Route::post('/pointage/validation-n1/{pointage}', [PointageSelfController::class, 'validationN1Action'])->name('pointage.validation-n1.action');

    // ===== FINANCE V2 — désactivé
    //   Le dashboard Finance principal (/finance) est désormais l'entrée unique.
    //   Ancienne bookmarks : redirection permanente vers /finance.
    Route::redirect('/finance/v2', '/finance', 301);
    Route::redirect('/finance/v2/{any}', '/finance', 301)->where('any', '.*');

    // ===== FINANCE (ancienne architecture — conservée) =====
    Route::prefix('finance')->name('finance.')->middleware('permission:read:exercice|read:budget|read:grandlivre|read:compte|read:client|read:facture|read:operation')->group(function () {
        // Dashboard Finance (KPIs, exécution budgétaire, alertes)
        Route::get('/', [\App\Http\Controllers\Finance\FinanceDashboardController::class, 'index'])->name('dashboard');

        // Dashboard d'exécution budgétaire (par ligne : planif / modifs / consommation / solde)
        Route::get('execution-budgetaire', [\App\Http\Controllers\Finance\BudgetExecutionController::class, 'index'])
            ->middleware('permission:read:budget')->name('execution-budgetaire');

        // Exports PDF (spec CdC §2 « modèle d'impression personnalisable »)
        Route::get('exports/budget/{exercice}', [\App\Http\Controllers\Finance\FinanceExportController::class, 'budgetPdf'])
            ->middleware('permission:export:budget|read:budget')->name('exports.budget-pdf');
        Route::get('exports/grand-livre', [\App\Http\Controllers\Finance\FinanceExportController::class, 'grandLivrePdf'])
            ->middleware('permission:export:grandlivre|read:grandlivre')->name('exports.grand-livre-pdf');
        Route::get('exports/balance/{exercice}', [\App\Http\Controllers\Finance\FinanceExportController::class, 'balancePdf'])
            ->middleware('permission:read:grandlivre')->name('exports.balance-pdf');

        // Exercices : permissions par action
        Route::get('exercices', [ExerciceController::class, 'index'])->middleware('permission:read:exercice')->name('exercices.index');
        Route::get('exercices/create', [ExerciceController::class, 'create'])->middleware('permission:create:exercice')->name('exercices.create');
        Route::post('exercices', [ExerciceController::class, 'store'])->middleware('permission:create:exercice')->name('exercices.store');
        Route::get('exercices/{exercice}', [ExerciceController::class, 'show'])->middleware('permission:read:exercice')->name('exercices.show');
        Route::get('exercices/{exercice}/edit', [ExerciceController::class, 'edit'])->middleware('permission:update:exercice')->name('exercices.edit');
        Route::put('exercices/{exercice}', [ExerciceController::class, 'update'])->middleware('permission:update:exercice')->name('exercices.update');
        Route::delete('exercices/{exercice}', [ExerciceController::class, 'destroy'])->middleware('permission:delete:exercice')->name('exercices.destroy');
        // Workflow exercice — Planification & validation top management
        Route::get('exercices/{exercice}/planification', [ExerciceController::class, 'planification'])
            ->middleware('permission:update:exercice')->name('exercices.planification');
        Route::post('exercices/{exercice}/planification', [ExerciceController::class, 'planificationSave'])
            ->middleware('permission:update:exercice')->name('exercices.planification.save');
        Route::post('exercices/{exercice}/planification/sync-referentiel', [ExerciceController::class, 'synchroniserReferentiel'])
            ->middleware('permission:update:exercice')->name('exercices.planification.sync-referentiel');
        Route::post('exercices/{exercice}/planification/lignes/{budgetLigne}/retirer', [ExerciceController::class, 'retirerLigne'])
            ->middleware('permission:update:exercice')->name('exercices.planification.lignes.retirer');
        Route::post('exercices/{exercice}/planification/lignes/{budgetLigne}/remettre', [ExerciceController::class, 'remettreLigne'])
            ->middleware('permission:update:exercice')->name('exercices.planification.lignes.remettre');
        Route::post('exercices/{exercice}/soumettre', [ExerciceController::class, 'soumettre'])
            ->middleware('permission:update:exercice')->name('exercices.soumettre');
        Route::post('exercices/{exercice}/valider', [ExerciceController::class, 'valider'])
            ->middleware('permission:validate:exercice')->name('exercices.valider');
        Route::post('exercices/{exercice}/rejeter', [ExerciceController::class, 'rejeter'])
            ->middleware('permission:validate:exercice')->name('exercices.rejeter');
        Route::post('exercices/{exercice}/cloturer', [ExerciceController::class, 'cloturer'])
            ->middleware('permission:validate:exercice')->name('exercices.cloturer');
        Route::post('exercices/{exercice}/annuler', [ExerciceController::class, 'annuler'])
            ->middleware('permission:validate:exercice')->name('exercices.annuler');

        // Budgets : permissions par action
        Route::get('budgets', [BudgetController::class, 'index'])->middleware('permission:read:budget')->name('budgets.index');
        Route::get('budgets/create', [BudgetController::class, 'create'])->middleware('permission:create:budget')->name('budgets.create');
        Route::post('budgets', [BudgetController::class, 'store'])->middleware('permission:create:budget')->name('budgets.store');
        Route::get('budgets/{budget}', [BudgetController::class, 'show'])->middleware('permission:read:budget')->name('budgets.show');
        Route::get('budgets/{budget}/edit', [BudgetController::class, 'edit'])->middleware('permission:update:budget')->name('budgets.edit');
        Route::put('budgets/{budget}', [BudgetController::class, 'update'])->middleware('permission:update:budget')->name('budgets.update');
        Route::delete('budgets/{budget}', [BudgetController::class, 'destroy'])->middleware('permission:delete:budget')->name('budgets.destroy');
        // Workflow validation des lignes budgétaires
        Route::post('budgets/{budget}/valider', [BudgetController::class, 'valider'])->middleware('permission:validate:budget')->name('budgets.valider');
        Route::post('budgets/{budget}/devalider', [BudgetController::class, 'devalider'])->middleware('permission:validate:budget')->name('budgets.devalider');

        // Comptes : permissions par action
        Route::get('comptes', [CompteController::class, 'index'])->middleware('permission:read:compte')->name('comptes.index');
        Route::get('comptes/create', [CompteController::class, 'create'])->middleware('permission:create:compte')->name('comptes.create');
        Route::post('comptes', [CompteController::class, 'store'])->middleware('permission:create:compte')->name('comptes.store');
        Route::get('comptes/{compte}', [CompteController::class, 'show'])->middleware('permission:read:compte')->name('comptes.show');
        Route::get('comptes/{compte}/edit', [CompteController::class, 'edit'])->middleware('permission:update:compte')->name('comptes.edit');
        Route::put('comptes/{compte}', [CompteController::class, 'update'])->middleware('permission:update:compte')->name('comptes.update');
        Route::delete('comptes/{compte}', [CompteController::class, 'destroy'])->middleware('permission:delete:compte')->name('comptes.destroy');

        // Modifications budgétaires (workflow CdC §1) — déclarées AVANT le resource grand-livre
        // car les actions custom (soumettre, approuver…) doivent matcher avant /{id}
        Route::get('modifications-budgetaires', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'index'])
            ->middleware('permission:read:budget')->name('modifications-budgetaires.index');
        Route::get('modifications-budgetaires/create', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'create'])
            ->middleware('permission:create:budget')->name('modifications-budgetaires.create');
        Route::post('modifications-budgetaires', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'store'])
            ->middleware('permission:create:budget')->name('modifications-budgetaires.store');
        Route::get('modifications-budgetaires/{modifications_budgetaire}', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'show'])
            ->middleware('permission:read:budget')->name('modifications-budgetaires.show');
        Route::get('modifications-budgetaires/{modifications_budgetaire}/edit', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'edit'])
            ->middleware('permission:update:budget')->name('modifications-budgetaires.edit');
        Route::put('modifications-budgetaires/{modifications_budgetaire}', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'update'])
            ->middleware('permission:update:budget')->name('modifications-budgetaires.update');
        Route::delete('modifications-budgetaires/{modifications_budgetaire}', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'destroy'])
            ->middleware('permission:delete:budget')->name('modifications-budgetaires.destroy');
        // Workflow
        Route::post('modifications-budgetaires/{modifications_budgetaire}/soumettre', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'soumettre'])
            ->middleware('permission:update:budget')->name('modifications-budgetaires.soumettre');
        Route::post('modifications-budgetaires/{modifications_budgetaire}/approuver', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'approuver'])
            ->middleware('permission:validate:budget')->name('modifications-budgetaires.approuver');
        Route::post('modifications-budgetaires/{modifications_budgetaire}/rejeter', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'rejeter'])
            ->middleware('permission:validate:budget')->name('modifications-budgetaires.rejeter');
        Route::post('modifications-budgetaires/{modifications_budgetaire}/appliquer', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'appliquer'])
            ->middleware('permission:validate:budget')->name('modifications-budgetaires.appliquer');
        Route::post('modifications-budgetaires/{modifications_budgetaire}/annuler', [\App\Http\Controllers\Finance\ModificationBudgetaireController::class, 'annuler'])
            ->middleware('permission:validate:budget')->name('modifications-budgetaires.annuler');

        // Grand-livre : permissions par action (les écritures sont sensibles)
        // Grand-livre : LECTURE SEULE (saisie unitaire) + EXPORT CSV + IMPORT CSV en bulk
        Route::get('grand-livre',                       [GrandLivreController::class, 'index'])->middleware('permission:read:grandlivre')->name('grand-livre.index');
        Route::get('grand-livre/export',                [GrandLivreController::class, 'export'])->middleware('permission:read:grandlivre|export:grandlivre')->name('grand-livre.export');
        Route::get('grand-livre/import',                [GrandLivreController::class, 'importForm'])->middleware('permission:create:grandlivre')->name('grand-livre.import.form');
        Route::post('grand-livre/import',               [GrandLivreController::class, 'importStore'])->middleware('permission:create:grandlivre')->name('grand-livre.import.store');
        Route::get('grand-livre/import/template',       [GrandLivreController::class, 'importTemplate'])->middleware('permission:create:grandlivre')->name('grand-livre.import.template');
        Route::get('grand-livre/{grand_livre}',         [GrandLivreController::class, 'show'])->middleware('permission:read:grandlivre')->name('grand-livre.show');

        // ───── CLIENTS → redirigés vers /intranet/organisations?type=client (fusion) ─────
        Route::get('clients', fn() => redirect()->route('intranet.organisations.index', ['type' => 'client']))->name('clients.index');
        Route::get('clients/create', fn() => redirect()->route('intranet.organisations.create', ['type' => 'client']))->name('clients.create');
        Route::get('clients/{client}', function ($client) {
            $orga = \App\Models\Intranet\ContactOrganisation::where('type', 'client')
                ->where(function ($q) use ($client) { $q->where('id', $client)->orWhere('code', $client); })
                ->first();
            return $orga ? redirect()->route('intranet.organisations.show', $orga)
                         : redirect()->route('intranet.organisations.index', ['type' => 'client']);
        })->name('clients.show');
        Route::get('clients/{client}/edit', function ($client) {
            $orga = \App\Models\Intranet\ContactOrganisation::where('type', 'client')
                ->where(function ($q) use ($client) { $q->where('id', $client)->orWhere('code', $client); })
                ->first();
            return $orga ? redirect()->route('intranet.organisations.edit', $orga)
                         : redirect()->route('intranet.organisations.index', ['type' => 'client']);
        })->name('clients.edit');

        // ───── FACTURES ─────
        Route::get('factures', [\App\Http\Controllers\Finance\FactureController::class, 'index'])->middleware('permission:read:facture')->name('factures.index');
        Route::get('factures/create', [\App\Http\Controllers\Finance\FactureController::class, 'create'])->middleware('permission:create:facture')->name('factures.create');
        Route::post('factures', [\App\Http\Controllers\Finance\FactureController::class, 'store'])->middleware('permission:create:facture')->name('factures.store');
        Route::get('factures/{facture}', [\App\Http\Controllers\Finance\FactureController::class, 'show'])->middleware('permission:read:facture')->name('factures.show');
        Route::get('factures/{facture}/edit', [\App\Http\Controllers\Finance\FactureController::class, 'edit'])->middleware('permission:update:facture')->name('factures.edit');
        Route::put('factures/{facture}', [\App\Http\Controllers\Finance\FactureController::class, 'update'])->middleware('permission:update:facture')->name('factures.update');
        Route::delete('factures/{facture}', [\App\Http\Controllers\Finance\FactureController::class, 'destroy'])->middleware('permission:delete:facture')->name('factures.destroy');
        Route::post('factures/{facture}/valider', [\App\Http\Controllers\Finance\FactureController::class, 'valider'])->middleware('permission:validate:facture')->name('factures.valider');
        Route::delete('factures/{facture}/pieces-jointes/{piece}', [\App\Http\Controllers\Finance\FactureController::class, 'destroyPieceJointe'])
             ->middleware('permission:update:facture')->name('factures.pieces-jointes.destroy');
        Route::post('factures/{facture}/annuler', [\App\Http\Controllers\Finance\FactureController::class, 'annuler'])->middleware('permission:validate:facture')->name('factures.annuler');
        Route::post('factures/{facture}/ordonnancer', [\App\Http\Controllers\Finance\FactureController::class, 'ordonnancer'])->middleware('permission:validate:facture')->name('factures.ordonnancer');

        // ───── OPÉRATIONS FINANCIÈRES (ordres dépense/recette) ─────
        Route::get('operations', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'index'])->middleware('permission:read:operation')->name('operations.index');
        Route::get('operations/create', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'create'])->middleware('permission:create:operation')->name('operations.create');
        Route::post('operations', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'store'])->middleware('permission:create:operation')->name('operations.store');
        Route::get('operations/{operation}', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'show'])->middleware('permission:read:operation')->name('operations.show');
        Route::get('operations/{operation}/edit', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'edit'])->middleware('permission:update:operation')->name('operations.edit');
        Route::put('operations/{operation}', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'update'])->middleware('permission:update:operation')->name('operations.update');
        Route::delete('operations/{operation}', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'destroy'])->middleware('permission:delete:operation')->name('operations.destroy');
        Route::post('operations/{operation}/soumettre', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'soumettre'])->middleware('permission:update:operation')->name('operations.soumettre');
        Route::post('operations/{operation}/approuver', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'approuver'])->middleware('permission:validate:operation')->name('operations.approuver');
        Route::post('operations/{operation}/rejeter', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'rejeter'])->middleware('permission:validate:operation')->name('operations.rejeter');
        Route::post('operations/{operation}/executer', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'executer'])->middleware('permission:validate:operation')->name('operations.executer');
        Route::post('operations/{operation}/annuler', [\App\Http\Controllers\Finance\OperationFinanciereController::class, 'annuler'])->middleware('permission:validate:operation')->name('operations.annuler');

        // ───── ORDRES (Dépenses & Recettes) — nouveaux formulaires dynamiques ─────
        $ord = \App\Http\Controllers\Finance\OrdreController::class;
        Route::get('ordres',                       [$ord, 'index'])->middleware('permission:read:operation')->name('ordres.index');
        Route::get('ordres/create',                [$ord, 'create'])->middleware('permission:create:operation')->name('ordres.create');
        Route::post('ordres',                      [$ord, 'store'])->middleware('permission:create:operation')->name('ordres.store');
        Route::get('ordres/rubriques/{budgetLigne}/{sens}', [$ord, 'rubriquesPourLigne'])
             ->middleware('permission:read:operation')->name('ordres.rubriques-pour-ligne');
        Route::get('api/exercices/{exercice}/budget-lignes', [$ord, 'budgetLignesPourExercice'])
             ->middleware('permission:read:operation')->name('api.exercices.budget-lignes');
        Route::get('ordres/{ordre}',               [$ord, 'show'])->middleware('permission:read:operation')->name('ordres.show');
        Route::get('ordres/{ordre}/edit',          [$ord, 'edit'])->middleware('permission:update:operation')->name('ordres.edit');
        Route::put('ordres/{ordre}',               [$ord, 'update'])->middleware('permission:update:operation')->name('ordres.update');
        Route::delete('ordres/{ordre}',            [$ord, 'destroy'])->middleware('permission:delete:operation')->name('ordres.destroy');
        // Workflow signature + exécution au Grand Livre
        Route::post('ordres/{ordre}/soumettre',    [$ord, 'soumettre'])->middleware('permission:update:operation')->name('ordres.soumettre');
        Route::post('ordres/{ordre}/signer',       [$ord, 'signer'])->middleware('permission:validate:operation')->name('ordres.signer');
        Route::post('ordres/{ordre}/executer',     [$ord, 'executer'])->middleware('permission:validate:operation')->name('ordres.executer');
        Route::post('ordres/{ordre}/annuler',      [$ord, 'annuler'])->middleware('permission:validate:operation')->name('ordres.annuler');
        Route::get('ordres/{ordre}/pdf',           [$ord, 'pdf'])->middleware('permission:read:operation')->name('ordres.pdf');
        Route::delete('ordres/{ordre}/justificatifs/{piece}', [$ord, 'destroyJustificatif'])
             ->middleware('permission:update:operation')->name('ordres.justificatifs.destroy');

        // ───── RÉFÉRENTIELS FINANCE ─────
        Route::prefix('referentiels')->name('referentiels.')->group(function () {
            // Titres
            Route::get('titres', [\App\Http\Controllers\Finance\TitreController::class, 'index'])->middleware('permission:read:titre')->name('titres.index');
            Route::get('titres/create', [\App\Http\Controllers\Finance\TitreController::class, 'create'])->middleware('permission:create:titre')->name('titres.create');
            Route::post('titres', [\App\Http\Controllers\Finance\TitreController::class, 'store'])->middleware('permission:create:titre')->name('titres.store');
            Route::get('titres/{titre}', [\App\Http\Controllers\Finance\TitreController::class, 'show'])->middleware('permission:read:titre')->name('titres.show');
            Route::get('titres/{titre}/edit', [\App\Http\Controllers\Finance\TitreController::class, 'edit'])->middleware('permission:update:titre')->name('titres.edit');
            Route::put('titres/{titre}', [\App\Http\Controllers\Finance\TitreController::class, 'update'])->middleware('permission:update:titre')->name('titres.update');
            Route::delete('titres/{titre}', [\App\Http\Controllers\Finance\TitreController::class, 'destroy'])->middleware('permission:delete:titre')->name('titres.destroy');

            // Lignes
            Route::get('lignes', [\App\Http\Controllers\Finance\LigneController::class, 'index'])->middleware('permission:read:ligne')->name('lignes.index');
            Route::get('lignes/create', [\App\Http\Controllers\Finance\LigneController::class, 'create'])->middleware('permission:create:ligne')->name('lignes.create');
            Route::post('lignes', [\App\Http\Controllers\Finance\LigneController::class, 'store'])->middleware('permission:create:ligne')->name('lignes.store');
            Route::get('lignes/{ligne}', [\App\Http\Controllers\Finance\LigneController::class, 'show'])->middleware('permission:read:ligne')->name('lignes.show');
            Route::get('lignes/{ligne}/edit', [\App\Http\Controllers\Finance\LigneController::class, 'edit'])->middleware('permission:update:ligne')->name('lignes.edit');
            Route::put('lignes/{ligne}', [\App\Http\Controllers\Finance\LigneController::class, 'update'])->middleware('permission:update:ligne')->name('lignes.update');
            Route::delete('lignes/{ligne}', [\App\Http\Controllers\Finance\LigneController::class, 'destroy'])->middleware('permission:delete:ligne')->name('lignes.destroy');

            // Rubriques d'opérations — déclarer create AVANT {rubrique} sinon "create" matche le param
            Route::get('rubriques', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'index'])->middleware('permission:read:rubrique_operation')->name('rubriques.index');
            Route::get('rubriques/create', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'create'])->middleware('permission:create:rubrique_operation')->name('rubriques.create');
            Route::post('rubriques', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'store'])->middleware('permission:create:rubrique_operation')->name('rubriques.store');
            // Endpoint AJAX : rubriques disponibles pour une BudgetLigne
            Route::get('rubriques/par-budget-ligne/{budget_ligne}', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'parBudgetLigne'])
                ->middleware('permission:read:rubrique_operation')->name('rubriques.par-budget-ligne');
            Route::get('rubriques/{rubrique}', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'show'])->middleware('permission:read:rubrique_operation')->name('rubriques.show');
            Route::get('rubriques/{rubrique}/edit', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'edit'])->middleware('permission:update:rubrique_operation')->name('rubriques.edit');
            Route::put('rubriques/{rubrique}', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'update'])->middleware('permission:update:rubrique_operation')->name('rubriques.update');
            Route::delete('rubriques/{rubrique}', [\App\Http\Controllers\Finance\RubriqueOperationController::class, 'destroy'])->middleware('permission:delete:rubrique_operation')->name('rubriques.destroy');

            // Sources de financement (référentiel)
            Route::get('sources',                [\App\Http\Controllers\Finance\SourceController::class, 'index'])->middleware('permission:read:budget')->name('sources.index');
            Route::post('sources',               [\App\Http\Controllers\Finance\SourceController::class, 'store'])->middleware('permission:create:budget')->name('sources.store');
            Route::put('sources/{source}',       [\App\Http\Controllers\Finance\SourceController::class, 'update'])->middleware('permission:update:budget')->name('sources.update');
            Route::delete('sources/{source}',    [\App\Http\Controllers\Finance\SourceController::class, 'destroy'])->middleware('permission:delete:budget')->name('sources.destroy');

            // ─── Ordres : administration des modèles (templates) ───
            $om = \App\Http\Controllers\Finance\OrdreModeleController::class;
            Route::prefix('ordres-modeles')->name('ordres-modeles.')->group(function () use ($om) {
                Route::get('/',                             [$om, 'index'])->middleware('permission:read:rubrique_operation')->name('index');
                Route::get('create',                        [$om, 'create'])->middleware('permission:create:rubrique_operation')->name('create');
                Route::post('/',                            [$om, 'store'])->middleware('permission:create:rubrique_operation')->name('store');
                Route::get('{ordres_modele}/edit',          [$om, 'edit'])->middleware('permission:update:rubrique_operation')->name('edit');
                Route::put('{ordres_modele}',               [$om, 'update'])->middleware('permission:update:rubrique_operation')->name('update');
                Route::delete('{ordres_modele}',            [$om, 'destroy'])->middleware('permission:delete:rubrique_operation')->name('destroy');
                Route::post('{ordres_modele}/champs',       [$om, 'storeChamp'])->middleware('permission:update:rubrique_operation')->name('champs.store');
                Route::put('{ordres_modele}/champs/{champ}',[$om, 'updateChamp'])->middleware('permission:update:rubrique_operation')->name('champs.update');
                Route::delete('{ordres_modele}/champs/{champ}',[$om, 'destroyChamp'])->middleware('permission:update:rubrique_operation')->name('champs.destroy');
                Route::post('{ordres_modele}/champs-reorder',[$om, 'reordonnerChamps'])->middleware('permission:update:rubrique_operation')->name('champs.reorder');
                Route::post('{ordres_modele}/signataires',  [$om, 'storeSignataire'])->middleware('permission:update:rubrique_operation')->name('signataires.store');
                Route::delete('{ordres_modele}/signataires/{signataire}',[$om, 'destroySignataire'])->middleware('permission:update:rubrique_operation')->name('signataires.destroy');
            });
        });
    });

    // ===== RESSOURCES HUMAINES =====
    Route::prefix('rh')->name('rh.')->middleware('permission:read:employee|read:absence|read:paie|read:recrutement|read:affilie|read:qualification|read:competence|read:formation|read:mission|read:rubrique|read:evenement_carriere|read:sanction|read:depart|read:performance|read:conge_solde|read:planning|read:payement|read:payement_global')->group(function () {
        Route::resource('employees', EmployeeController::class)->middleware('permission:read:employee');
        Route::resource('absences', AbsenceController::class)->middleware('permission:read:absence');
        Route::resource('paie', PaieController::class)->middleware('permission:read:paie');
        Route::resource('recrutements', RecrutementController::class)->middleware('permission:read:recrutement');
        // Endpoint AJAX pour récupérer les profils d'un recrutement (utilisé dans le formulaire postulant)
        Route::get('recrutements/{recrutement}/profils', [PostulantController::class, 'profilsParRecrutement'])
            ->middleware('permission:read:recrutement')->name('recrutements.profils');
        // Postulants (candidats) — workflow nouveau → entretien → retenu → embauché / rejeté
        // ⚠️ create AVANT {id} pour éviter conflit de routing
        Route::get('postulants', [PostulantController::class, 'index'])
            ->middleware('permission:read:postulant')->name('postulants.index');
        Route::get('postulants/create', [PostulantController::class, 'create'])
            ->middleware('permission:create:postulant')->name('postulants.create');
        Route::post('postulants', [PostulantController::class, 'store'])
            ->middleware('permission:create:postulant')->name('postulants.store');
        Route::get('postulants/{postulant}', [PostulantController::class, 'show'])
            ->middleware('permission:read:postulant')->name('postulants.show');
        Route::get('postulants/{postulant}/edit', [PostulantController::class, 'edit'])
            ->middleware('permission:update:postulant')->name('postulants.edit');
        Route::put('postulants/{postulant}', [PostulantController::class, 'update'])
            ->middleware('permission:update:postulant')->name('postulants.update');
        Route::delete('postulants/{postulant}', [PostulantController::class, 'destroy'])
            ->middleware('permission:delete:postulant')->name('postulants.destroy');
        Route::post('postulants/{postulant}/statut', [PostulantController::class, 'changerStatut'])
            ->middleware('permission:update:postulant')->name('postulants.statut');
        Route::post('postulants/{postulant}/embaucher', [PostulantController::class, 'embaucher'])
            ->middleware('permission:create:employee')->name('postulants.embaucher');
        Route::resource('affilies', AffilieController::class)->middleware('permission:read:affilie');
        Route::resource('qualifications', QualificationController::class)->middleware('permission:read:qualification');
        Route::resource('competences', CompetenceController::class)->middleware('permission:read:competence');
        Route::resource('formations', FormationController::class)->middleware('permission:read:formation');
        Route::resource('missions', MissionController::class)->middleware('permission:read:mission');
        Route::resource('rubriques', RubriqueController::class)->middleware('permission:read:rubrique_operation');

        // ── Grades (référentiel) + Avancements (historique) ──
        $gc = \App\Http\Controllers\RH\GradeController::class;
        Route::get   ('grades',                  [$gc, 'index'])->middleware('permission:read:employee')->name('grades.index');
        Route::get   ('grades/{grade}',          [$gc, 'show'])->middleware('permission:read:employee')->name('grades.show');
        Route::post  ('grades',                  [$gc, 'store'])->middleware('permission:update:employee')->name('grades.store');
        Route::put   ('grades/{grade}',          [$gc, 'update'])->middleware('permission:update:employee')->name('grades.update');
        Route::delete('grades/{grade}',          [$gc, 'destroy'])->middleware('permission:delete:employee')->name('grades.destroy');
        Route::post  ('grades/{grade}/criteres', [$gc, 'storeCritere'])->middleware('permission:update:employee')->name('grades.criteres.store');
        Route::put   ('grades/{grade}/criteres/{critere}', [$gc, 'updateCritere'])->middleware('permission:update:employee')->name('grades.criteres.update');
        Route::delete('grades/{grade}/criteres/{critere}', [$gc, 'destroyCritere'])->middleware('permission:update:employee')->name('grades.criteres.destroy');

        $ac = \App\Http\Controllers\RH\AvancementController::class;
        Route::get   ('employees/{employee}/avancements',              [$ac, 'index'])->middleware('permission:read:employee')->name('employees.avancements.index');
        Route::post  ('employees/{employee}/avancements',              [$ac, 'store'])->middleware('permission:update:employee')->name('employees.avancements.store');
        Route::delete('employees/{employee}/avancements/{avancement}', [$ac, 'destroy'])->middleware('permission:delete:employee')->name('employees.avancements.destroy');
        Route::post  ('avancements/detecter-auto',                     [$ac, 'detecterAutomatiques'])->middleware('permission:update:employee')->name('avancements.detecter-auto');
        Route::post  ('avancements/{avancement}/valider',              [$ac, 'valider'])->middleware('permission:update:employee')->name('avancements.valider');
        Route::post  ('avancements/{avancement}/refuser',              [$ac, 'refuser'])->middleware('permission:update:employee')->name('avancements.refuser');
        Route::resource('evenements-carriere', EvenementCarriereController::class)
            ->parameters(['evenements-carriere' => 'evenements_carriere'])
            ->middleware('permission:read:evenement_carriere');
        Route::resource('sanctions', SanctionController::class)->middleware('permission:read:sanction');
        Route::resource('departs', DepartController::class)->middleware('permission:read:depart');
        Route::resource('evaluations-performance', EvaluationPerformanceController::class)
            ->parameters(['evaluations-performance' => 'evaluations_performance'])
            ->middleware('permission:read:performance');
        Route::resource('conges-soldes', CongeSoldeController::class)
            ->parameters(['conges-soldes' => 'conges_solde'])
            ->middleware('permission:read:conge_solde');
        Route::resource('plannings', PlanningController::class)->middleware('permission:read:planning');
        // Pointages — routes spéciales AVANT le resource pour éviter conflits avec {id}
        Route::get('pointages/grille/{employee}', [PointageController::class, 'grilleEmploye'])
            ->middleware('permission:read:paie')->name('pointages.grille-employe');
        Route::post('pointages/grille/{employee}', [PointageController::class, 'grilleEmployeStore'])
            ->middleware('permission:create:paie')->name('pointages.grille-employe.store');
        Route::post('pointages/valider', [PointageController::class, 'valider'])
            ->middleware('permission:update:paie')->name('pointages.valider');
        // QR codes (admin) — déclarées AVANT resource sinon /{id} les intercepte
        Route::get('pointages/qr/{employee}/image', [PointageQrController::class, 'image'])
            ->middleware('permission:update:employee')->name('pointages.qr.image');
        Route::get('pointages/qr/{employee}/imprimer', [PointageQrController::class, 'imprimer'])
            ->middleware('permission:update:employee')->name('pointages.qr.imprimer');
        Route::post('pointages/qr/{employee}/regenerer', [PointageQrController::class, 'regenererToken'])
            ->middleware('permission:update:employee')->name('pointages.qr.regenerer');
        // QR générique partagé (admin) — déclarées AVANT resource
        Route::get('pointages/qr-generique', [PointageGeneriqueController::class, 'admin'])
            ->middleware('permission:update:employee')->name('pointages.qr-generique.admin');
        Route::get('pointages/qr-generique/image', [PointageGeneriqueController::class, 'image'])
            ->middleware('permission:update:employee')->name('pointages.qr-generique.image');
        Route::get('pointages/qr-generique/imprimer', [PointageGeneriqueController::class, 'imprimer'])
            ->middleware('permission:update:employee')->name('pointages.qr-generique.imprimer');
        Route::post('pointages/qr-generique/regenerer', [PointageGeneriqueController::class, 'regenererToken'])
            ->middleware('permission:update:employee')->name('pointages.qr-generique.regenerer');
        // Resource (catch-all à la fin)
        Route::resource('pointages', PointageController::class)
            ->middleware('permission:read:paie');
        // PIN d'un employé (admin)
        Route::post('employees/{employee}/pointage-pin', [PointageGeneriqueController::class, 'definirPinEmployee'])
            ->middleware('permission:update:employee')->name('employees.pointage-pin');

        // Paie — actions avancées (moteur à rubriques + workflow)
        Route::post('paie/apercu', [PaieController::class, 'apercu'])->middleware('permission:read:paie')->name('paie.apercu');
        Route::post('paie/generer', [PaieController::class, 'genererBulletin'])->middleware('permission:create:paie')->name('paie.generer');
        Route::post('paie/{paie}/valider', [PaieController::class, 'valider'])->middleware('permission:validate:paie')->name('paie.valider');
        Route::get('paie/{paie}/pdf', [PaieController::class, 'pdf'])->middleware('permission:read:paie')->name('paie.pdf');

        // Payements (règlements bulletins)
        Route::resource('payements', PayementController::class)
            ->only(['index', 'create', 'store', 'show', 'destroy'])
            ->middleware('permission:read:payement');

        // PayementsGlobals (agrégats masse salariale)
        Route::get('payements-globals', [PayementGlobalController::class, 'index'])->middleware('permission:read:payement_global')->name('payements-globals.index');
        Route::get('payements-globals/{payements_global}', [PayementGlobalController::class, 'show'])->middleware('permission:read:payement_global')->name('payements-globals.show');
        Route::post('payements-globals/recalculer', [PayementGlobalController::class, 'recalculer'])->middleware('permission:read:payement_global')->name('payements-globals.recalculer');
        Route::post('payements-globals/{payements_global}/cloturer', [PayementGlobalController::class, 'cloturer'])->middleware('permission:validate:payement_global')->name('payements-globals.cloturer');

        // Campagnes de paie (traitement en lot)
        Route::resource('campagnes-paie', CampagnePaieController::class)
            ->parameters(['campagnes-paie' => 'campagnes_paie'])
            ->only(['index', 'create', 'store', 'show', 'destroy'])
            ->middleware('permission:read:paie');
        // Edit partiel des campagnes en brouillon (libellé, commentaire, date paiement prévue)
        Route::get('campagnes-paie/{campagnes_paie}/edit', [CampagnePaieController::class, 'edit'])
            ->middleware('permission:update:paie')->name('campagnes-paie.edit');
        Route::put('campagnes-paie/{campagnes_paie}', [CampagnePaieController::class, 'update'])
            ->middleware('permission:update:paie')->name('campagnes-paie.update');
        Route::post('campagnes-paie/{campagnes_paie}/generer', [CampagnePaieController::class, 'generer'])
            ->middleware('permission:create:paie')->name('campagnes-paie.generer');
        Route::post('campagnes-paie/{campagnes_paie}/valider', [CampagnePaieController::class, 'valider'])
            ->middleware('permission:validate:paie')->name('campagnes-paie.valider');
        Route::post('campagnes-paie/{campagnes_paie}/cloturer', [CampagnePaieController::class, 'cloturer'])
            ->middleware('permission:validate:paie')->name('campagnes-paie.cloturer');
        Route::get('campagnes-paie/{campagnes_paie}/ov.csv', [CampagnePaieController::class, 'ovCsv'])
            ->middleware('permission:export:paie')->name('campagnes-paie.ov-csv');
        Route::get('campagnes-paie/{campagnes_paie}/ov.pdf', [CampagnePaieController::class, 'ovPdf'])
            ->middleware('permission:read:paie')->name('campagnes-paie.ov-pdf');
        Route::get('campagnes-paie/{campagnes_paie}/ov.cfonb', [CampagnePaieController::class, 'ovCfonb'])
            ->middleware('permission:export:paie')->name('campagnes-paie.ov-cfonb');
        // Échantillons / groupes de paie (sélections réutilisables d'employés)
        // ⚠️ create AVANT {id} pour éviter que "create" ne soit pris pour un id
        Route::get('echantillons-paie', [EchantillonPaieController::class, 'index'])
            ->middleware('permission:read:paie')->name('echantillons-paie.index');
        Route::get('echantillons-paie/create', [EchantillonPaieController::class, 'create'])
            ->middleware('permission:create:paie')->name('echantillons-paie.create');
        Route::post('echantillons-paie', [EchantillonPaieController::class, 'store'])
            ->middleware('permission:create:paie')->name('echantillons-paie.store');
        Route::get('echantillons-paie/{echantillons_paie}', [EchantillonPaieController::class, 'show'])
            ->middleware('permission:read:paie')->name('echantillons-paie.show');
        Route::get('echantillons-paie/{echantillons_paie}/edit', [EchantillonPaieController::class, 'edit'])
            ->middleware('permission:update:paie')->name('echantillons-paie.edit');
        Route::put('echantillons-paie/{echantillons_paie}', [EchantillonPaieController::class, 'update'])
            ->middleware('permission:update:paie')->name('echantillons-paie.update');
        Route::delete('echantillons-paie/{echantillons_paie}', [EchantillonPaieController::class, 'destroy'])
            ->middleware('permission:delete:paie')->name('echantillons-paie.destroy');
        // Déclarations sociales (CNSS, CNAMGS, FNH, CFP)
        Route::get('declarations-sociales', [DeclarationSocialeController::class, 'index'])
            ->middleware('permission:read:paie')->name('declarations-sociales.index');
        Route::get('declarations-sociales/create', [DeclarationSocialeController::class, 'create'])
            ->middleware('permission:create:paie')->name('declarations-sociales.create');
        Route::post('declarations-sociales/apercu', [DeclarationSocialeController::class, 'apercu'])
            ->middleware('permission:read:paie')->name('declarations-sociales.apercu');
        Route::post('declarations-sociales', [DeclarationSocialeController::class, 'store'])
            ->middleware('permission:create:paie')->name('declarations-sociales.store');
        Route::get('declarations-sociales/{declarations_sociale}', [DeclarationSocialeController::class, 'show'])
            ->middleware('permission:read:paie')->name('declarations-sociales.show');
        Route::delete('declarations-sociales/{declarations_sociale}', [DeclarationSocialeController::class, 'destroy'])
            ->middleware('permission:delete:paie')->name('declarations-sociales.destroy');
        Route::post('declarations-sociales/{declarations_sociale}/valider', [DeclarationSocialeController::class, 'valider'])
            ->middleware('permission:validate:paie')->name('declarations-sociales.valider');
        Route::post('declarations-sociales/{declarations_sociale}/deposer', [DeclarationSocialeController::class, 'deposer'])
            ->middleware('permission:validate:paie')->name('declarations-sociales.deposer');
        Route::get('declarations-sociales/{declarations_sociale}/pdf', [DeclarationSocialeController::class, 'pdf'])
            ->middleware('permission:read:paie')->name('declarations-sociales.pdf');
        Route::get('declarations-sociales/{declarations_sociale}/csv', [DeclarationSocialeController::class, 'csv'])
            ->middleware('permission:export:paie')->name('declarations-sociales.csv');

        // Dashboard + Audit + RGPD
        Route::get('/', [RhDashboardController::class, 'index'])->name('dashboard');
        Route::get('audit-log', [RhDashboardController::class, 'auditLog'])->middleware('permission:read:employee')->name('audit-log');
        Route::get('employees/{employee}/rgpd/export', [RhDashboardController::class, 'exportRgpd'])->middleware('permission:read:employee')->name('employees.rgpd.export');
        Route::post('employees/{employee}/rgpd/anonymiser', [RhDashboardController::class, 'anonymiserRgpd'])->middleware('permission:delete:employee')->name('employees.rgpd.anonymiser');
        // Réinitialisation de mot de passe (procédure dédiée, tracée)
        Route::get('employees/{employee}/reset-password', [EmployeePasswordController::class, 'show'])
            ->middleware('permission:update:user')->name('employees.reset-password.show');
        Route::post('employees/{employee}/reset-password', [EmployeePasswordController::class, 'reset'])
            ->middleware('permission:update:user')->name('employees.reset-password.action');
    });

    // ===== ACHATS / APPROVISIONNEMENTS =====
    // Dashboard combiné Achats + Moyens Généraux — ouvert à tout utilisateur authentifié
    Route::get('appro/dashboard', [\App\Http\Controllers\Appro\LogistiqueDashboardController::class, 'index'])
        ->name('appro.dashboard');
    Route::get('appro', fn() => redirect()->route('appro.dashboard'))->name('appro.index');

    // Endpoint AJAX polling ruptures stock — restreint aux gestionnaires Achats/MG + admins
    Route::get('appro/stock/rupture-check', [\App\Http\Controllers\Appro\LogistiqueDashboardController::class, 'stockRuptureCheck'])
        ->middleware('permission:update:produit|update:commande|update:dysfonctionnement')
        ->name('appro.stock.rupture-check');

    Route::prefix('appro')->name('appro.')->middleware('permission:read:fournisseur|read:produit|read:commande')->group(function () {
        // ── Fournisseurs → redirigés vers /intranet/organisations?type=fournisseur (fusion) ──
        Route::get('fournisseurs', fn() => redirect()->route('intranet.organisations.index', ['type' => 'fournisseur']))->name('fournisseurs.index');
        Route::get('fournisseurs/create', fn() => redirect()->route('intranet.organisations.create', ['type' => 'fournisseur']))->name('fournisseurs.create');
        Route::get('fournisseurs/{fournisseur}', function ($fournisseur) {
            $orga = \App\Models\Intranet\ContactOrganisation::where('type', 'fournisseur')
                ->where(function ($q) use ($fournisseur) { $q->where('id', $fournisseur)->orWhere('code', $fournisseur); })
                ->first();
            return $orga ? redirect()->route('intranet.organisations.show', $orga)
                         : redirect()->route('intranet.organisations.index', ['type' => 'fournisseur']);
        })->name('fournisseurs.show');
        Route::get('fournisseurs/{fournisseur}/edit', function ($fournisseur) {
            $orga = \App\Models\Intranet\ContactOrganisation::where('type', 'fournisseur')
                ->where(function ($q) use ($fournisseur) { $q->where('id', $fournisseur)->orWhere('code', $fournisseur); })
                ->first();
            return $orga ? redirect()->route('intranet.organisations.edit', $orga)
                         : redirect()->route('intranet.organisations.index', ['type' => 'fournisseur']);
        })->name('fournisseurs.edit');
        // Produits : fusionné dans /referentiel/catalogue. Redirections préservées pour rétro-compat (bookmarks, anciens liens).
        Route::get('produits',                    fn() => redirect()->route('referentiel.catalogue.index'))->name('produits.index');
        Route::get('produits/create',             fn() => redirect()->route('referentiel.catalogue.create'))->name('produits.create');
        Route::get('produits/{produit}',          fn($produit) => redirect()->route('referentiel.catalogue.show', $produit))->name('produits.show');
        Route::get('produits/{produit}/edit',     fn($produit) => redirect()->route('referentiel.catalogue.edit', $produit))->name('produits.edit');
        Route::resource('commandes', CommandeController::class)->middleware('permission:read:commande');
        // Workflow commandes
        $cmd = \App\Http\Controllers\Appro\CommandeController::class;
        Route::post('commandes/{commande}/soumettre',       [$cmd, 'soumettre'])->middleware('permission:update:commande')->name('commandes.soumettre');
        Route::post('commandes/{commande}/approuver',       [$cmd, 'approuver'])->middleware('permission:validate:commande')->name('commandes.approuver');
        Route::post('commandes/{commande}/livrer',          [$cmd, 'livrer'])->middleware('permission:update:commande')->name('commandes.livrer');
        Route::post('commandes/{commande}/annuler',         [$cmd, 'annuler'])->middleware('permission:update:commande')->name('commandes.annuler');
        Route::post('commandes/{commande}/generer-facture', [$cmd, 'genererFacture'])->middleware('permission:create:facture')->name('commandes.generer-facture');

        // ── Contrats fournisseurs ─────────────────────────────
        $ctr = \App\Http\Controllers\Appro\ContratFournisseurController::class;
        Route::resource('contrats', $ctr)->parameters(['contrats' => 'contrat']);
        Route::post('contrats/{contrat}/activer',   [$ctr, 'activer'])->name('contrats.activer');
        Route::post('contrats/{contrat}/resilier',  [$ctr, 'resilier'])->name('contrats.resilier');
        Route::post('contrats/{contrat}/renouveler',[$ctr, 'renouveler'])->name('contrats.renouveler');
        Route::post('contrats/{contrat}/avenants',  [$ctr, 'addAvenant'])->name('contrats.avenants.store');
    });

    // ===== APPRO — accès élargi (commandes internes ouvertes à tout agent, évaluations aux référents) =====
    Route::prefix('appro')->name('appro.')->group(function () {
        // ── Commandes internes (workflow demandeur → N+1 → Appro → livraison) ──
        // Ouvert à tout utilisateur authentifié — le workflow interne (demandeur/N+1/appro) gère les droits.
        Route::resource('commandes-internes', \App\Http\Controllers\Appro\CommandeInterneController::class)
            ->parameters(['commandes-internes' => 'commande']);
        $ci = \App\Http\Controllers\Appro\CommandeInterneController::class;
        Route::post('commandes-internes/{commande}/soumettre-n1',    [$ci, 'soumettreN1'])->name('commandes-internes.soumettre-n1');
        Route::post('commandes-internes/{commande}/valider-n1',      [$ci, 'validerN1'])->name('commandes-internes.valider-n1');
        Route::post('commandes-internes/{commande}/refuser-n1',      [$ci, 'refuserN1'])->name('commandes-internes.refuser-n1');
        Route::post('commandes-internes/{commande}/transmettre',     [$ci, 'transmettreAppro'])->middleware('permission:update:commande')->name('commandes-internes.transmettre');
        Route::post('commandes-internes/{commande}/assigner-n1',     [$ci, 'assignerN1'])->middleware('permission:update:commande')->name('commandes-internes.assigner-n1');
        Route::post('commandes-internes/{commande}/livrer',          [$ci, 'livrer'])->middleware('permission:update:commande')->name('commandes-internes.livrer');
        Route::post('commandes-internes/{commande}/annuler',         [$ci, 'annuler'])->name('commandes-internes.annuler');

        // ── Évaluation prestataires ──
        Route::resource('evaluations', \App\Http\Controllers\Appro\EvaluationPrestataireController::class)
            ->only(['index', 'create', 'store', 'show', 'destroy'])->parameters(['evaluations' => 'evaluation']);

        // ── Campagnes d'évaluation ──
        Route::resource('campagnes', \App\Http\Controllers\Appro\CampagneEvaluationController::class)
            ->parameters(['campagnes' => 'campagne']);
        $cp = \App\Http\Controllers\Appro\CampagneEvaluationController::class;
        Route::post('campagnes/{campagne}/lancer',   [$cp, 'lancer'])->name('campagnes.lancer');
        Route::post('campagnes/{campagne}/cloturer', [$cp, 'cloturer'])->name('campagnes.cloturer');

        // ── Devis fournisseur ──
        Route::resource('devis-fournisseur', \App\Http\Controllers\Appro\DevisFournisseurController::class)
            ->parameters(['devis-fournisseur' => 'devis']);
        Route::post('devis-fournisseur/{devis}/selectionner', [\App\Http\Controllers\Appro\DevisFournisseurController::class, 'selectionner'])->name('devis-fournisseur.selectionner');
        Route::post('devis-fournisseur/{devis}/rejeter',      [\App\Http\Controllers\Appro\DevisFournisseurController::class, 'rejeter'])->name('devis-fournisseur.rejeter');

        // ── Livraisons fournisseur ──
        Route::resource('livraisons-fournisseur', \App\Http\Controllers\Appro\LivraisonFournisseurController::class)
            ->only(['index', 'create', 'store', 'show'])
            ->parameters(['livraisons-fournisseur' => 'livraison']);

        // ── Gestion des stocks ──
        $stk = \App\Http\Controllers\Appro\StockController::class;
        Route::get('stock',                [$stk, 'dashboard'])->name('stock.dashboard');
        Route::get('stock/emplacements',   [$stk, 'parEmplacement'])->name('stock.emplacements');
        Route::get('stock/mouvements',     [$stk, 'mouvements'])->name('stock.mouvements');

        // ── Inventaires ──
        Route::resource('inventaires', \App\Http\Controllers\Appro\InventaireController::class)
            ->parameters(['inventaires' => 'inventaire']);
        $iv = \App\Http\Controllers\Appro\InventaireController::class;
        Route::get('inventaires/{inventaire}/saisie',     [$iv, 'saisie'])->name('inventaires.saisie');
        Route::post('inventaires/{inventaire}/saisie',    [$iv, 'enregistrerSaisie'])->name('inventaires.saisie.save');
        Route::post('inventaires/{inventaire}/lancer',    [$iv, 'lancer'])->name('inventaires.lancer');
        Route::post('inventaires/{inventaire}/cloturer',  [$iv, 'cloturer'])->name('inventaires.cloturer');
        Route::post('inventaires/{inventaire}/annuler',   [$iv, 'annuler'])->name('inventaires.annuler');
    });

    // ===== RÉFÉRENTIELS ===== (familles, catalogue, critères d'évaluation, thématiques MG)
    Route::prefix('referentiel')->name('referentiel.')->middleware('permission:read:produit|read:organisation')->group(function () {
        // Familles hiérarchiques d'articles
        Route::resource('familles', \App\Http\Controllers\Referentiel\FamilleArticleController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['familles' => 'famille']);
        Route::post('familles/{famille}/toggle', [\App\Http\Controllers\Referentiel\FamilleArticleController::class, 'toggle'])
            ->name('familles.toggle');

        // Catalogue biens/services
        Route::resource('catalogue', \App\Http\Controllers\Referentiel\CatalogueController::class)
            ->parameters(['catalogue' => 'article']);
        // Formulaires dédiés — pages autonomes
        Route::get('catalogue/{article}/ajuster-stock',    [\App\Http\Controllers\Referentiel\CatalogueController::class, 'formAjusterStock'])->name('catalogue.ajuster-stock.form');
        Route::post('catalogue/{article}/ajuster-stock',   [\App\Http\Controllers\Referentiel\CatalogueController::class, 'ajusterStock'])->name('catalogue.ajuster-stock');
        Route::get('catalogue/{article}/transferer-stock', [\App\Http\Controllers\Referentiel\CatalogueController::class, 'formTransfererStock'])->name('catalogue.transferer-stock.form');
        Route::post('catalogue/{article}/transferer-stock',[\App\Http\Controllers\Referentiel\CatalogueController::class, 'transfererStock'])->name('catalogue.transferer-stock');

        // Référentiel évaluation (thèmes + critères)
        $er = \App\Http\Controllers\Referentiel\EvaluationReferentielController::class;
        Route::get('evaluations',                     [$er, 'index'])->name('evaluations.index');
        Route::post('evaluations/themes',             [$er, 'storeTheme'])->name('evaluations.themes.store');
        Route::put('evaluations/themes/{theme}',      [$er, 'updateTheme'])->name('evaluations.themes.update');
        Route::delete('evaluations/themes/{theme}',   [$er, 'destroyTheme'])->name('evaluations.themes.destroy');
        Route::post('evaluations/criteres',           [$er, 'storeCritere'])->name('evaluations.criteres.store');
        Route::put('evaluations/criteres/{critere}',  [$er, 'updateCritere'])->name('evaluations.criteres.update');
        Route::delete('evaluations/criteres/{critere}', [$er, 'destroyCritere'])->name('evaluations.criteres.destroy');

        // Thématiques MG
        Route::resource('mg-thematiques', \App\Http\Controllers\Referentiel\MgThematiqueController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['mg-thematiques' => 'thematique']);
        Route::post('mg-thematiques/{thematique}/toggle', [\App\Http\Controllers\Referentiel\MgThematiqueController::class, 'toggle'])
            ->name('mg-thematiques.toggle');

        // Familles de dysfonctionnement (parent des types)
        Route::resource('familles-dysfonctionnement', \App\Http\Controllers\Referentiel\FamilleDysfonctionnementController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['familles-dysfonctionnement' => 'famille']);

        // Types de dysfonctionnement
        Route::resource('types-dysfonctionnement', \App\Http\Controllers\Referentiel\TypeDysfonctionnementController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['types-dysfonctionnement' => 'type']);

        // Natures d'intervention
        Route::resource('natures-intervention', \App\Http\Controllers\Referentiel\NatureInterventionController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['natures-intervention' => 'nature']);

        // Types d'engagement fournisseur
        Route::resource('types-engagement', \App\Http\Controllers\Referentiel\TypeEngagementController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['types-engagement' => 'type']);

        // Fréquences de paiement
        Route::resource('frequences-paiement', \App\Http\Controllers\Referentiel\FrequencePaiementController::class)
            ->only(['index', 'store', 'update', 'destroy'])->parameters(['frequences-paiement' => 'frequence']);

        // Emplacements de stockage (hiérarchiques)
        Route::resource('emplacements', \App\Http\Controllers\Referentiel\EmplacementController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy'])->parameters(['emplacements' => 'emplacement']);
        Route::post('emplacements/{emplacement}/toggle', [\App\Http\Controllers\Referentiel\EmplacementController::class, 'toggle'])
            ->name('emplacements.toggle');
    });

    // ===== MOYENS GENERAUX =====
    Route::prefix('mg')->name('mg.')->middleware('permission:read:immobilisation|read:dysfonctionnement|read:intervention')->group(function () {
        Route::resource('immobilisations', ImmobilisationController::class)->middleware('permission:read:immobilisation');
        Route::post('immobilisations/{immobilisation}/dotation', [ImmobilisationController::class, 'dotation'])->name('immobilisations.dotation')->middleware('permission:update:immobilisation');
        Route::post('immobilisations/{immobilisation}/sortir', [ImmobilisationController::class, 'sortir'])->name('immobilisations.sortir')->middleware('permission:update:immobilisation');

        Route::resource('dysfonctionnements', DysfonctionnementController::class)->middleware('permission:read:dysfonctionnement');
        Route::post('dysfonctionnements/{dysfonctionnement}/prendre-en-charge', [DysfonctionnementController::class, 'prendreEnCharge'])->name('dysfonctionnements.prendre-en-charge')->middleware('permission:update:dysfonctionnement');
        Route::post('dysfonctionnements/{dysfonctionnement}/resoudre', [DysfonctionnementController::class, 'resoudre'])->name('dysfonctionnements.resoudre')->middleware('permission:update:dysfonctionnement');
        Route::post('dysfonctionnements/{dysfonctionnement}/valider-resolution', [DysfonctionnementController::class, 'validerResolution'])->name('dysfonctionnements.valider-resolution');
        Route::post('dysfonctionnements/{dysfonctionnement}/rejeter-resolution', [DysfonctionnementController::class, 'rejeterResolution'])->name('dysfonctionnements.rejeter-resolution');
        Route::post('dysfonctionnements/{dysfonctionnement}/fermer', [DysfonctionnementController::class, 'fermer'])->name('dysfonctionnements.fermer')->middleware('permission:update:dysfonctionnement');
        Route::post('dysfonctionnements/{dysfonctionnement}/planifier-intervention', [DysfonctionnementController::class, 'planifierIntervention'])->name('dysfonctionnements.planifier-intervention')->middleware('permission:create:intervention');
        Route::post('dysfonctionnements/{dysfonctionnement}/prioriser', [DysfonctionnementController::class, 'prioriser'])->name('dysfonctionnements.prioriser')->middleware('permission:validate:dysfonctionnement');

        Route::resource('interventions', InterventionController::class)->middleware('permission:read:intervention');
        Route::post('interventions/{intervention}/demarrer', [InterventionController::class, 'demarrer'])->name('interventions.demarrer')->middleware('permission:update:intervention');
        Route::post('interventions/{intervention}/terminer', [InterventionController::class, 'terminer'])->name('interventions.terminer')->middleware('permission:update:intervention');
        Route::post('interventions/{intervention}/annuler', [InterventionController::class, 'annuler'])->name('interventions.annuler')->middleware('permission:update:intervention');
    });

    // ===== ADMINISTRATION / SYSTEME =====
    Route::prefix('systeme')->name('systeme.')->middleware('permission:read:organisation')->group(function () {
        Route::resource('organisations', OrganisationController::class)->middleware('permission:read:organisation');
    });

    // ===== PROJETS / TÂCHES (PMP) =====
    Route::prefix('projet')->name('projet.')->group(function () {
        // Dashboard global
        Route::get('/', [\App\Http\Controllers\Projet\DashboardController::class, 'index'])->name('dashboard');
        // Dashboard d'un projet
        Route::get('{projet}/overview', [\App\Http\Controllers\Projet\DashboardController::class, 'show'])->name('overview');

        // Endpoint AJAX polling — alertes tâches perso (retard + non démarrées)
        Route::get('alertes/taches-check', [\App\Http\Controllers\Projet\DashboardController::class, 'tachesAlertesCheck'])
            ->middleware('permission:read:tache_intranet')
            ->name('alertes.taches-check');

        // Tâches du projet
        Route::get('{projet}/taches', [\App\Http\Controllers\Projet\TacheProjetController::class, 'index'])->name('taches.index');
        Route::post('{projet}/taches', [\App\Http\Controllers\Projet\TacheProjetController::class, 'store'])->name('taches.store');
        Route::put('{projet}/taches/{tache}', [\App\Http\Controllers\Projet\TacheProjetController::class, 'update'])->name('taches.update');
        Route::delete('{projet}/taches/{tache}', [\App\Http\Controllers\Projet\TacheProjetController::class, 'destroy'])->name('taches.destroy');

        // WBS / Phases
        Route::get('{projet}/wbs', [\App\Http\Controllers\Projet\WbsController::class, 'index'])->name('wbs.index');
        Route::post('{projet}/wbs', [\App\Http\Controllers\Projet\WbsController::class, 'store'])->name('wbs.store');
        Route::get('{projet}/wbs/{phase}', [\App\Http\Controllers\Projet\WbsController::class, 'show'])->name('wbs.show');
        Route::put('{projet}/wbs/{phase}', [\App\Http\Controllers\Projet\WbsController::class, 'update'])->name('wbs.update');
        Route::delete('{projet}/wbs/{phase}', [\App\Http\Controllers\Projet\WbsController::class, 'destroy'])->name('wbs.destroy');
        Route::post('{projet}/wbs/reorder', [\App\Http\Controllers\Projet\WbsController::class, 'reorder'])->name('wbs.reorder');
        Route::delete('{projet}/wbs/{phase}/pieces-jointes/{piece}', [\App\Http\Controllers\Projet\WbsController::class, 'destroyPiece'])->name('wbs.pieces-jointes.destroy');

        // Jalons
        Route::get('{projet}/jalons', [\App\Http\Controllers\Projet\JalonController::class, 'index'])->name('jalons.index');
        Route::post('{projet}/jalons', [\App\Http\Controllers\Projet\JalonController::class, 'store'])->name('jalons.store');
        Route::get('{projet}/jalons/{jalon}', [\App\Http\Controllers\Projet\JalonController::class, 'show'])->name('jalons.show');
        Route::put('{projet}/jalons/{jalon}', [\App\Http\Controllers\Projet\JalonController::class, 'update'])->name('jalons.update');
        Route::delete('{projet}/jalons/{jalon}', [\App\Http\Controllers\Projet\JalonController::class, 'destroy'])->name('jalons.destroy');
        Route::post('{projet}/jalons/{jalon}/atteint', [\App\Http\Controllers\Projet\JalonController::class, 'marquerAtteint'])->name('jalons.atteint');
        Route::delete('{projet}/jalons/{jalon}/pieces-jointes/{piece}', [\App\Http\Controllers\Projet\JalonController::class, 'destroyPiece'])->name('jalons.pieces-jointes.destroy');

        // Ressources
        Route::get('{projet}/ressources', [\App\Http\Controllers\Projet\RessourceController::class, 'index'])->name('ressources.index');
        Route::post('{projet}/ressources', [\App\Http\Controllers\Projet\RessourceController::class, 'store'])->name('ressources.store');
        Route::put('{projet}/ressources/{ressource}', [\App\Http\Controllers\Projet\RessourceController::class, 'update'])->name('ressources.update');
        Route::delete('{projet}/ressources/{ressource}', [\App\Http\Controllers\Projet\RessourceController::class, 'destroy'])->name('ressources.destroy');

        // Feuilles de temps
        Route::get('{projet}/feuilles-temps', [\App\Http\Controllers\Projet\FeuilleTempsController::class, 'index'])->name('feuilles-temps.index');
        Route::post('{projet}/feuilles-temps', [\App\Http\Controllers\Projet\FeuilleTempsController::class, 'store'])->name('feuilles-temps.store');
        Route::post('{projet}/feuilles-temps/{feuille}/approuver', [\App\Http\Controllers\Projet\FeuilleTempsController::class, 'approuver'])->name('feuilles-temps.approuver');
        Route::post('{projet}/feuilles-temps/{feuille}/rejeter', [\App\Http\Controllers\Projet\FeuilleTempsController::class, 'rejeter'])->name('feuilles-temps.rejeter');
        Route::delete('{projet}/feuilles-temps/{feuille}', [\App\Http\Controllers\Projet\FeuilleTempsController::class, 'destroy'])->name('feuilles-temps.destroy');

        // Livrables
        Route::get('{projet}/livrables', [\App\Http\Controllers\Projet\LivrableController::class, 'index'])->name('livrables.index');
        Route::post('{projet}/livrables', [\App\Http\Controllers\Projet\LivrableController::class, 'store'])->name('livrables.store');
        Route::put('{projet}/livrables/{livrable}', [\App\Http\Controllers\Projet\LivrableController::class, 'update'])->name('livrables.update');
        Route::delete('{projet}/livrables/{livrable}', [\App\Http\Controllers\Projet\LivrableController::class, 'destroy'])->name('livrables.destroy');

        // Coûts
        Route::get('{projet}/couts', [\App\Http\Controllers\Projet\CoutController::class, 'index'])->name('couts.index');
        Route::post('{projet}/couts', [\App\Http\Controllers\Projet\CoutController::class, 'store'])->name('couts.store');
        Route::put('{projet}/couts/{cout}', [\App\Http\Controllers\Projet\CoutController::class, 'update'])->name('couts.update');
        Route::delete('{projet}/couts/{cout}', [\App\Http\Controllers\Projet\CoutController::class, 'destroy'])->name('couts.destroy');

        // EVM
        Route::get('{projet}/evm', [\App\Http\Controllers\Projet\EvmController::class, 'index'])->name('evm.index');
        Route::post('{projet}/evm', [\App\Http\Controllers\Projet\EvmController::class, 'store'])->name('evm.store');
        Route::delete('{projet}/evm/{evm}', [\App\Http\Controllers\Projet\EvmController::class, 'destroy'])->name('evm.destroy');

        // Risques
        Route::get('{projet}/risques', [\App\Http\Controllers\Projet\RisqueController::class, 'index'])->name('risques.index');
        Route::post('{projet}/risques', [\App\Http\Controllers\Projet\RisqueController::class, 'store'])->name('risques.store');
        Route::put('{projet}/risques/{risque}', [\App\Http\Controllers\Projet\RisqueController::class, 'update'])->name('risques.update');
        Route::delete('{projet}/risques/{risque}', [\App\Http\Controllers\Projet\RisqueController::class, 'destroy'])->name('risques.destroy');

        // Problèmes
        Route::get('{projet}/problemes', [\App\Http\Controllers\Projet\ProblemeController::class, 'index'])->name('problemes.index');
        Route::post('{projet}/problemes', [\App\Http\Controllers\Projet\ProblemeController::class, 'store'])->name('problemes.store');
        Route::put('{projet}/problemes/{probleme}', [\App\Http\Controllers\Projet\ProblemeController::class, 'update'])->name('problemes.update');
        Route::delete('{projet}/problemes/{probleme}', [\App\Http\Controllers\Projet\ProblemeController::class, 'destroy'])->name('problemes.destroy');

        // Changements
        Route::get('{projet}/changements', [\App\Http\Controllers\Projet\ChangementController::class, 'index'])->name('changements.index');
        Route::post('{projet}/changements', [\App\Http\Controllers\Projet\ChangementController::class, 'store'])->name('changements.store');
        Route::post('{projet}/changements/{changement}/decider', [\App\Http\Controllers\Projet\ChangementController::class, 'decider'])->name('changements.decider');
        Route::delete('{projet}/changements/{changement}', [\App\Http\Controllers\Projet\ChangementController::class, 'destroy'])->name('changements.destroy');

        // Parties prenantes
        Route::get('{projet}/parties-prenantes', [\App\Http\Controllers\Projet\PartiePrenanteController::class, 'index'])->name('parties-prenantes.index');
        Route::post('{projet}/parties-prenantes', [\App\Http\Controllers\Projet\PartiePrenanteController::class, 'store'])->name('parties-prenantes.store');
        Route::put('{projet}/parties-prenantes/{partie}', [\App\Http\Controllers\Projet\PartiePrenanteController::class, 'update'])->name('parties-prenantes.update');
        Route::delete('{projet}/parties-prenantes/{partie}', [\App\Http\Controllers\Projet\PartiePrenanteController::class, 'destroy'])->name('parties-prenantes.destroy');

        // Leçons apprises
        Route::get('{projet}/lecons', [\App\Http\Controllers\Projet\LeconController::class, 'index'])->name('lecons.index');
        Route::post('{projet}/lecons', [\App\Http\Controllers\Projet\LeconController::class, 'store'])->name('lecons.store');
        Route::put('{projet}/lecons/{lecon}', [\App\Http\Controllers\Projet\LeconController::class, 'update'])->name('lecons.update');
        Route::delete('{projet}/lecons/{lecon}', [\App\Http\Controllers\Projet\LeconController::class, 'destroy'])->name('lecons.destroy');

        // Demandes de modification / suppression
        Route::get('demandes-modification', [\App\Http\Controllers\Projet\DemandeModificationController::class, 'index'])->name('demandes.index');
        Route::post('demandes/{type}/{id}/modification', [\App\Http\Controllers\Projet\DemandeModificationController::class, 'store'])->name('demandes.modification');
        Route::post('demandes/{type}/{id}/suppression', [\App\Http\Controllers\Projet\DemandeModificationController::class, 'suppression'])->name('demandes.suppression');
        Route::post('demandes/{demande}/approuver', [\App\Http\Controllers\Projet\DemandeModificationController::class, 'approuver'])->name('demandes.approuver');
        Route::post('demandes/{demande}/rejeter', [\App\Http\Controllers\Projet\DemandeModificationController::class, 'rejeter'])->name('demandes.rejeter');

        // Validation de clôture (polymorphique : projet, phase, jalon, tache)
        Route::post('cloture/{type}/{id}/valideurs', [\App\Http\Controllers\Projet\ClotureController::class, 'sauverValideurs'])->name('cloture.valideurs');
        Route::post('cloture/{type}/{id}/soumettre', [\App\Http\Controllers\Projet\ClotureController::class, 'soumettre'])->name('cloture.soumettre');
        Route::post('cloture/{type}/{id}/approuver', [\App\Http\Controllers\Projet\ClotureController::class, 'approuver'])->name('cloture.approuver');
        Route::post('cloture/{type}/{id}/rejeter', [\App\Http\Controllers\Projet\ClotureController::class, 'rejeter'])->name('cloture.rejeter');
        Route::post('cloture/{type}/{id}/revisions', [\App\Http\Controllers\Projet\ClotureController::class, 'revisions'])->name('cloture.revisions');
        Route::get('cloture/{type}/{id}/historique', [\App\Http\Controllers\Projet\ClotureController::class, 'historique'])->name('cloture.historique');
    });

    // ===== INTRANET =====
    Route::prefix('intranet')->name('intranet.')->group(function () {
        // Vœux d'anniversaire — accessible à tout utilisateur authentifié
        Route::post('anniversaires/{employee}/wish', [\App\Http\Controllers\Intranet\BirthdayWishController::class, 'store'])
            ->name('anniversaires.wish');
        // Contenu du drawer réseau social (lazy-loaded)
        Route::get('social/drawer', [\App\Http\Controllers\Intranet\SocialFeedController::class, 'drawer'])
            ->name('social.drawer');

        Route::resource('annonces', AnnonceController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:annonce')
            ->middlewareFor(['create', 'store'], 'permission:create:annonce')
            ->middlewareFor(['edit', 'update'],  'permission:update:annonce')
            ->middlewareFor('destroy',           'permission:delete:annonce');

        Route::delete('annonces/{annonce}/pieces-jointes/{piece}', [AnnonceController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:annonce')
            ->name('annonces.pieces-jointes.destroy');

        Route::resource('news', NewsController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:news')
            ->middlewareFor(['create', 'store'], 'permission:create:news')
            ->middlewareFor(['edit', 'update'],  'permission:update:news')
            ->middlewareFor('destroy',           'permission:delete:news');

        Route::delete('news/{news}/pieces-jointes/{piece}', [NewsController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:news')
            ->name('news.pieces-jointes.destroy');

        // ─── ÉVÉNEMENTS & CALENDRIER ───
        Route::get('calendrier', [EvenementController::class, 'calendrier'])
            ->middleware('permission:read:evenement')
            ->name('calendrier');
        Route::get('calendrier/feed', [EvenementController::class, 'feed'])
            ->middleware('permission:read:evenement')
            ->name('calendrier.feed');

        Route::resource('evenements', EvenementController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:evenement')
            ->middlewareFor(['create', 'store'], 'permission:create:evenement')
            ->middlewareFor(['edit', 'update'],  'permission:update:evenement')
            ->middlewareFor('destroy',           'permission:delete:evenement');

        Route::post('evenements/{evenement}/rsvp', [EvenementController::class, 'rsvp'])
            ->middleware('permission:read:evenement')
            ->name('evenements.rsvp');

        Route::get('evenements-search-contacts', [EvenementController::class, 'searchContacts'])
            ->middleware('permission:read:evenement')
            ->name('evenements.search-contacts');

        Route::delete('evenements/{evenement}/pieces-jointes/{piece}', [EvenementController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:evenement')
            ->name('evenements.pieces-jointes.destroy');

        // ─── CRM ───────────────────────────────────────────
        Route::resource('contacts', ContactController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:contact')
            ->middlewareFor(['create', 'store'], 'permission:create:contact')
            ->middlewareFor(['edit', 'update'],  'permission:update:contact')
            ->middlewareFor('destroy',           'permission:delete:contact');
        Route::post('contacts/{contact}/favori', [ContactController::class, 'toggleFavori'])
            ->middleware('permission:update:contact')
            ->name('contacts.favori');
        Route::delete('contacts/{contact}/pieces-jointes/{piece}', [ContactController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:contact')
            ->name('contacts.pieces-jointes.destroy');

        // ── Référentiel : types de documents + matrice d'exigence ──
        // (DOIT être avant Route::resource pour ne pas être capturé par `organisations/{organisation}`)
        Route::prefix('organisations/types-documents')->name('organisations.types-documents.')->group(function () {
            Route::get('/',                  [\App\Http\Controllers\Intranet\TypeDocumentController::class, 'index'])
                ->middleware('permission:read:organisation_crm')->name('index');
            Route::post('/',                 [\App\Http\Controllers\Intranet\TypeDocumentController::class, 'store'])
                ->middleware('permission:create:organisation_crm')->name('store');
            Route::put('{type}',             [\App\Http\Controllers\Intranet\TypeDocumentController::class, 'update'])
                ->middleware('permission:update:organisation_crm')->name('update');
            Route::delete('{type}',          [\App\Http\Controllers\Intranet\TypeDocumentController::class, 'destroy'])
                ->middleware('permission:delete:organisation_crm')->name('destroy');
            Route::post('matrice',           [\App\Http\Controllers\Intranet\TypeDocumentController::class, 'saveMatrice'])
                ->middleware('permission:update:organisation_crm')->name('matrice.save');
        });

        Route::resource('organisations', ContactOrganisationController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:organisation_crm')
            ->middlewareFor(['create', 'store'], 'permission:create:organisation_crm')
            ->middlewareFor(['edit', 'update'],  'permission:update:organisation_crm')
            ->middlewareFor('destroy',           'permission:delete:organisation_crm');
        Route::delete('organisations/{organisation}/pieces-jointes/{piece}', [ContactOrganisationController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:organisation_crm')
            ->name('organisations.pieces-jointes.destroy');

        // ── Documents à fournir par organisation ──
        Route::post('organisations/{organisation}/documents', [\App\Http\Controllers\Intranet\OrganisationDocumentController::class, 'store'])
            ->middleware('permission:update:organisation_crm')
            ->name('organisations.documents.store');
        Route::delete('organisations/{organisation}/documents/{document}', [\App\Http\Controllers\Intranet\OrganisationDocumentController::class, 'destroy'])
            ->middleware('permission:update:organisation_crm')
            ->name('organisations.documents.destroy');

        Route::get('pipeline', [OpportuniteController::class, 'pipeline'])
            ->middleware('permission:read:opportunite')
            ->name('pipeline');
        Route::post('opportunites/{opportunite}/move', [OpportuniteController::class, 'moveStage'])
            ->middleware('permission:update:opportunite')
            ->name('opportunites.move');

        Route::resource('opportunites', OpportuniteController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:opportunite')
            ->middlewareFor(['create', 'store'], 'permission:create:opportunite')
            ->middlewareFor(['edit', 'update'],  'permission:update:opportunite')
            ->middlewareFor('destroy',           'permission:delete:opportunite');
        Route::delete('opportunites/{opportunite}/pieces-jointes/{piece}', [OpportuniteController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:opportunite')
            ->name('opportunites.pieces-jointes.destroy');

        // ─── COURRIERS ─────────────────────────────────────
        Route::resource('courriers', CourrierController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:courrier')
            ->middlewareFor(['create', 'store'], 'permission:create:courrier')
            ->middlewareFor(['edit', 'update'],  'permission:update:courrier')
            ->middlewareFor('destroy',           'permission:delete:courrier');

        Route::post('courriers/{courrier}/assigner',     [CourrierController::class, 'assigner'])
            ->middleware('permission:assign:courrier')->name('courriers.assigner');
        Route::post('courriers/{courrier}/traiter',      [CourrierController::class, 'traiter'])
            ->middleware('permission:process:courrier')->name('courriers.traiter');
        Route::post('courriers/{courrier}/rouvrir',      [CourrierController::class, 'rouvrir'])
            ->middleware('permission:process:courrier')->name('courriers.rouvrir');
        Route::post('courriers/{courrier}/accuser-reception', [CourrierController::class, 'accuserReception'])
            ->middleware('permission:update:courrier')->name('courriers.accuser-reception');
        Route::post('courriers/{courrier}/notation',     [CourrierController::class, 'ajouterNotation'])
            ->middleware('permission:read:courrier')->name('courriers.notation');
        Route::post('courriers/{courrier}/pieces-jointes', [CourrierController::class, 'ajouterPiecesJointes'])
            ->middleware('permission:read:courrier')->name('courriers.pieces-jointes.store');
        Route::delete('courriers/{courrier}/pieces-jointes/{piece}', [CourrierController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:courrier')->name('courriers.pieces-jointes.destroy');

        // ─── RESSOURCES (GED) ──────────────────────────────
        Route::resource('ressources', RessourceController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:ressource')
            ->middlewareFor(['create', 'store'], 'permission:create:ressource')
            ->middlewareFor(['edit', 'update'],  'permission:update:ressource')
            ->middlewareFor('destroy',           'permission:delete:ressource');
        Route::get('ressources/{ressource}/telecharger', [RessourceController::class, 'download'])
            ->middleware('permission:read:ressource')->name('ressources.download');
        Route::patch('ressources/{ressource}/deplacer', [RessourceController::class, 'deplacer'])
            ->middleware('permission:update:ressource')->name('ressources.deplacer');
        Route::delete('ressources/{ressource}/pieces-jointes/{piece}', [RessourceController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:ressource')->name('ressources.pieces-jointes.destroy');

        // ─── MÉDIATHÈQUE ───────────────────────────────────
        Route::resource('mediatheque', MediathequeController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:media')
            ->middlewareFor(['create', 'store'], 'permission:create:media')
            ->middlewareFor(['edit', 'update'],  'permission:update:media')
            ->middlewareFor('destroy',           'permission:delete:media');
        Route::get('mediatheque/{mediatheque}/telecharger', [MediathequeController::class, 'download'])
            ->middleware('permission:read:media')->name('mediatheque.download');
        Route::post('mediatheque-upload-multiple', [MediathequeController::class, 'storeMultiple'])
            ->middleware('permission:create:media')->name('mediatheque.upload-multiple');

        // Albums médiathèque
        Route::post('mediatheque-albums', [\App\Http\Controllers\Intranet\MediaAlbumController::class, 'store'])
            ->middleware('permission:create:media')->name('mediatheque.albums.store');
        Route::put('mediatheque-albums/{album}', [\App\Http\Controllers\Intranet\MediaAlbumController::class, 'update'])
            ->middleware('permission:update:media')->name('mediatheque.albums.update');
        Route::delete('mediatheque-albums/{album}', [\App\Http\Controllers\Intranet\MediaAlbumController::class, 'destroy'])
            ->middleware('permission:delete:media')->name('mediatheque.albums.destroy');

        // ─── ARCHIVES ──────────────────────────────────────
        Route::resource('archives', \App\Http\Controllers\Intranet\ArchiveController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:archive')
            ->middlewareFor(['create', 'store'], 'permission:create:archive')
            ->middlewareFor(['edit', 'update'],  'permission:update:archive')
            ->middlewareFor('destroy',           'permission:delete:archive');
        Route::get('archives/{archive}/telecharger', [\App\Http\Controllers\Intranet\ArchiveController::class, 'download'])
            ->middleware('permission:read:archive')->name('archives.download');
        Route::delete('archives/{archive}/pieces-jointes/{piece}', [\App\Http\Controllers\Intranet\ArchiveController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:archive')->name('archives.pieces-jointes.destroy');

        // Dossiers d'archives
        Route::post('archives-dossiers', [\App\Http\Controllers\Intranet\ArchiveController::class, 'storeDossier'])
            ->middleware('permission:create:archive')->name('archives.dossiers.store');
        Route::put('archives-dossiers/{dossier}', [\App\Http\Controllers\Intranet\ArchiveController::class, 'updateDossier'])
            ->middleware('permission:update:archive')->name('archives.dossiers.update');
        Route::delete('archives-dossiers/{dossier}', [\App\Http\Controllers\Intranet\ArchiveController::class, 'destroyDossier'])
            ->middleware('permission:delete:archive')->name('archives.dossiers.destroy');

        // ─── TEMPLATES ─────────────────────────────────────
        Route::resource('templates', \App\Http\Controllers\Intranet\TemplateController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:template')
            ->middlewareFor(['create', 'store'], 'permission:create:template')
            ->middlewareFor(['edit', 'update'],  'permission:update:template')
            ->middlewareFor('destroy',           'permission:delete:template');
        Route::get('templates/{template}/telecharger', [\App\Http\Controllers\Intranet\TemplateController::class, 'download'])
            ->middleware('permission:read:template')->name('templates.download');
        Route::get('templates/{template}/utiliser', [\App\Http\Controllers\Intranet\TemplateController::class, 'utiliser'])
            ->middleware('permission:read:template')->name('templates.utiliser');
        Route::post('templates/{template}/generer', [\App\Http\Controllers\Intranet\TemplateController::class, 'generer'])
            ->middleware('permission:read:template')->name('templates.generer');

        // ─── RAPPORTS & CR ─────────────────────────────────
        Route::resource('rapports', \App\Http\Controllers\Intranet\RapportController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:rapport')
            ->middlewareFor(['create', 'store'], 'permission:create:rapport')
            ->middlewareFor(['edit', 'update'],  'permission:update:rapport')
            ->middlewareFor('destroy',           'permission:delete:rapport');
        Route::delete('rapports/{rapport}/pieces-jointes/{piece}', [\App\Http\Controllers\Intranet\RapportController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:rapport')->name('rapports.pieces-jointes.destroy');

        // Workflow validation rapports
        Route::post('rapports/{rapport}/soumettre', [\App\Http\Controllers\Intranet\RapportController::class, 'soumettre'])
            ->middleware('permission:update:rapport')->name('rapports.soumettre');
        Route::post('rapports/{rapport}/approuver', [\App\Http\Controllers\Intranet\RapportController::class, 'approuver'])
            ->middleware('permission:validate:rapport')->name('rapports.approuver');
        Route::post('rapports/{rapport}/rejeter', [\App\Http\Controllers\Intranet\RapportController::class, 'rejeter'])
            ->middleware('permission:validate:rapport')->name('rapports.rejeter');
        Route::post('rapports/{rapport}/revisions', [\App\Http\Controllers\Intranet\RapportController::class, 'demanderRevisions'])
            ->middleware('permission:validate:rapport')->name('rapports.revisions');

        // ─── TÂCHES ────────────────────────────────────────
        // Endpoint AJAX : phases + jalons d'un projet (form tâche dynamique)
        Route::get('taches/projets/{projet}/phases-jalons', [\App\Http\Controllers\Intranet\TacheController::class, 'phasesJalonsProjet'])
            ->middleware('permission:read:tache_intranet')->name('taches.phases-jalons');

        Route::resource('taches', \App\Http\Controllers\Intranet\TacheController::class)
            ->parameters(['taches' => 'tache'])
            ->middlewareFor(['index', 'show'],   'permission:read:tache_intranet')
            ->middlewareFor(['create', 'store'], 'permission:create:tache_intranet')
            ->middlewareFor(['edit', 'update'],  'permission:update:tache_intranet')
            ->middlewareFor('destroy',           'permission:delete:tache_intranet');
        Route::delete('taches/{tache}/pieces-jointes/{piece}', [\App\Http\Controllers\Intranet\TacheController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:tache_intranet')->name('taches.pieces-jointes.destroy');

        // Workflow validation tâches
        Route::post('taches/{tache}/soumettre', [\App\Http\Controllers\Intranet\TacheController::class, 'soumettre'])
            ->middleware('permission:update:tache_intranet')->name('taches.soumettre');
        Route::post('taches/{tache}/approuver', [\App\Http\Controllers\Intranet\TacheController::class, 'approuver'])
            ->middleware('permission:validate:tache_intranet')->name('taches.approuver');
        Route::post('taches/{tache}/rejeter', [\App\Http\Controllers\Intranet\TacheController::class, 'rejeter'])
            ->middleware('permission:validate:tache_intranet')->name('taches.rejeter');
        Route::post('taches/{tache}/revisions', [\App\Http\Controllers\Intranet\TacheController::class, 'demanderRevisions'])
            ->middleware('permission:validate:tache_intranet')->name('taches.revisions');
        Route::post('taches/{tache}/evaluer', [\App\Http\Controllers\Intranet\TacheController::class, 'evaluer'])
            ->middleware('permission:validate:tache_intranet')->name('taches.evaluer');

        // ─── PROJETS (vue basique intranet) ────────────────
        Route::resource('projets', \App\Http\Controllers\Intranet\ProjetController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:projet_intranet')
            ->middlewareFor(['create', 'store'], 'permission:create:projet_intranet')
            ->middlewareFor(['edit', 'update'],  'permission:update:projet_intranet')
            ->middlewareFor('destroy',           'permission:delete:projet_intranet');

        // ─── WIKI ──────────────────────────────────────────
        Route::resource('wiki', \App\Http\Controllers\Intranet\WikiController::class)
            ->middlewareFor(['index', 'show'],   'permission:read:wiki')
            ->middlewareFor(['create', 'store'], 'permission:create:wiki')
            ->middlewareFor(['edit', 'update'],  'permission:update:wiki')
            ->middlewareFor('destroy',           'permission:delete:wiki');
        Route::delete('wiki/{wiki}/pieces-jointes/{piece}', [\App\Http\Controllers\Intranet\WikiController::class, 'destroyPieceJointe'])
            ->middleware('permission:update:wiki')->name('wiki.pieces-jointes.destroy');
        Route::post('wiki-categories', [\App\Http\Controllers\Intranet\WikiController::class, 'storeCategorie'])
            ->middleware('permission:create:wiki')->name('wiki.categories.store');
        Route::delete('wiki-categories/{categorie}', [\App\Http\Controllers\Intranet\WikiController::class, 'destroyCategorie'])
            ->middleware('permission:delete:wiki')->name('wiki.categories.destroy');

        // ─── ANNUAIRE ─────────────────────────────────────
        Route::prefix('annuaire')->name('annuaire.')->group(function () {
            // Collaborateurs (lecture seule)
            Route::get('collaborateurs', [\App\Http\Controllers\Intranet\Annuaire\CollaborateurController::class, 'index'])->name('collaborateurs.index');
            Route::get('collaborateurs/{collaborateur}', [\App\Http\Controllers\Intranet\Annuaire\CollaborateurController::class, 'show'])->name('collaborateurs.show');

            // Services
            Route::get('services', [\App\Http\Controllers\Intranet\Annuaire\ServiceController::class, 'index'])->name('services.index');
            Route::post('services', [\App\Http\Controllers\Intranet\Annuaire\ServiceController::class, 'store'])->name('services.store');
            Route::get('services/{service}', [\App\Http\Controllers\Intranet\Annuaire\ServiceController::class, 'show'])->name('services.show');
            Route::put('services/{service}', [\App\Http\Controllers\Intranet\Annuaire\ServiceController::class, 'update'])->name('services.update');
            Route::delete('services/{service}', [\App\Http\Controllers\Intranet\Annuaire\ServiceController::class, 'destroy'])->name('services.destroy');
            Route::post('services/{service}/membres', [\App\Http\Controllers\Intranet\Annuaire\ServiceController::class, 'attacherMembre'])->name('services.membres.attach');
            Route::delete('services/{service}/membres/{user}', [\App\Http\Controllers\Intranet\Annuaire\ServiceController::class, 'detacherMembre'])->name('services.membres.detach');

            // Équipes (basées sur Groupes)
            Route::get('equipes', [\App\Http\Controllers\Intranet\Annuaire\EquipeController::class, 'index'])->name('equipes.index');
            Route::post('equipes', [\App\Http\Controllers\Intranet\Annuaire\EquipeController::class, 'store'])->name('equipes.store');
            Route::get('equipes/{equipe}', [\App\Http\Controllers\Intranet\Annuaire\EquipeController::class, 'show'])->name('equipes.show');
            Route::put('equipes/{equipe}', [\App\Http\Controllers\Intranet\Annuaire\EquipeController::class, 'update'])->name('equipes.update');
            Route::delete('equipes/{equipe}', [\App\Http\Controllers\Intranet\Annuaire\EquipeController::class, 'destroy'])->name('equipes.destroy');
            Route::post('equipes/{equipe}/membres', [\App\Http\Controllers\Intranet\Annuaire\EquipeController::class, 'attacherMembre'])->name('equipes.membres.attach');
            Route::delete('equipes/{equipe}/membres/{user}', [\App\Http\Controllers\Intranet\Annuaire\EquipeController::class, 'detacherMembre'])->name('equipes.membres.detach');
        });
    });

    // ===== OBJECTIFS & KPI =====
    Route::prefix('objectifs')->name('objectifs.')
        ->middleware('permission:read:objectif|read:kpi|read:evaluation|read:plan_action')
        ->group(function () {
        Route::get('/', [\App\Http\Controllers\Objectif\DashboardController::class, 'index'])->name('dashboard');

        // Objectifs (stratégiques + opérationnels via type=)
        Route::get('objectifs', [\App\Http\Controllers\Objectif\ObjectifController::class, 'index'])
            ->middleware('permission:read:objectif')->name('objectifs.index');
        Route::post('objectifs', [\App\Http\Controllers\Objectif\ObjectifController::class, 'store'])
            ->middleware('permission:create:objectif')->name('objectifs.store');
        Route::get('objectifs/{objectif}', [\App\Http\Controllers\Objectif\ObjectifController::class, 'show'])
            ->middleware('permission:read:objectif')->name('objectifs.show');
        Route::put('objectifs/{objectif}', [\App\Http\Controllers\Objectif\ObjectifController::class, 'update'])
            ->middleware('permission:update:objectif')->name('objectifs.update');
        Route::delete('objectifs/{objectif}', [\App\Http\Controllers\Objectif\ObjectifController::class, 'destroy'])
            ->middleware('permission:delete:objectif')->name('objectifs.destroy');

        // KPI
        Route::get('kpi', [\App\Http\Controllers\Objectif\KpiController::class, 'index'])
            ->middleware('permission:read:kpi')->name('kpi.index');
        Route::post('kpi', [\App\Http\Controllers\Objectif\KpiController::class, 'store'])
            ->middleware('permission:create:kpi')->name('kpi.store');
        Route::get('kpi/{kpi}', [\App\Http\Controllers\Objectif\KpiController::class, 'show'])
            ->middleware('permission:read:kpi')->name('kpi.show');
        Route::put('kpi/{kpi}', [\App\Http\Controllers\Objectif\KpiController::class, 'update'])
            ->middleware('permission:update:kpi')->name('kpi.update');
        Route::delete('kpi/{kpi}', [\App\Http\Controllers\Objectif\KpiController::class, 'destroy'])
            ->middleware('permission:delete:kpi')->name('kpi.destroy');
        Route::post('kpi/{kpi}/valeurs', [\App\Http\Controllers\Objectif\KpiController::class, 'saisirValeur'])
            ->middleware('permission:update:kpi')->name('kpi.valeurs.store');

        // Plans d'action
        Route::get('plans-action', [\App\Http\Controllers\Objectif\PlanActionController::class, 'index'])
            ->middleware('permission:read:plan_action')->name('plans-action.index');
        Route::get('plans-action/{plan}', [\App\Http\Controllers\Objectif\PlanActionController::class, 'show'])
            ->middleware('permission:read:plan_action')->name('plans-action.show');
        Route::post('plans-action', [\App\Http\Controllers\Objectif\PlanActionController::class, 'store'])
            ->middleware('permission:create:plan_action')->name('plans-action.store');
        Route::put('plans-action/{plan}', [\App\Http\Controllers\Objectif\PlanActionController::class, 'update'])
            ->middleware('permission:update:plan_action')->name('plans-action.update');
        Route::delete('plans-action/{plan}', [\App\Http\Controllers\Objectif\PlanActionController::class, 'destroy'])
            ->middleware('permission:delete:plan_action')->name('plans-action.destroy');

        // Évaluations
        Route::get('evaluations', [\App\Http\Controllers\Objectif\EvaluationController::class, 'index'])
            ->middleware('permission:read:evaluation')->name('evaluations.index');
        Route::post('evaluations', [\App\Http\Controllers\Objectif\EvaluationController::class, 'store'])
            ->middleware('permission:create:evaluation')->name('evaluations.store');
        Route::get('evaluations/{evaluation}', [\App\Http\Controllers\Objectif\EvaluationController::class, 'show'])
            ->middleware('permission:read:evaluation')->name('evaluations.show');
        Route::put('evaluations/{evaluation}', [\App\Http\Controllers\Objectif\EvaluationController::class, 'update'])
            ->middleware('permission:update:evaluation')->name('evaluations.update');
        Route::delete('evaluations/{evaluation}', [\App\Http\Controllers\Objectif\EvaluationController::class, 'destroy'])
            ->middleware('permission:delete:evaluation')->name('evaluations.destroy');
    });

    // ===== ADMINISTRATION / PARAMÉTRAGE =====
    Route::prefix('admin')->name('admin.')->middleware('role:super-admin|admin')->group(function () {

        // ── Utilisateurs (CRUD + rôles + reset password + activation) ─────
        $uc = \App\Http\Controllers\Admin\UserController::class;
        Route::get   ('users',                 [$uc, 'index'])->name('users.index');
        Route::post  ('users',                 [$uc, 'store'])->name('users.store');
        Route::put   ('users/{user}',          [$uc, 'update'])->name('users.update');
        Route::delete('users/{user}',          [$uc, 'destroy'])->name('users.destroy');
        Route::post  ('users/{user}/toggle',   [$uc, 'toggleActif'])->name('users.toggle');
        Route::post  ('users/{user}/reset',    [$uc, 'resetPassword'])->name('users.reset');

        // ── Rôles + matrice de permissions ────────────────────────────────
        $rc = \App\Http\Controllers\Admin\RoleController::class;
        Route::get   ('roles',                       [$rc, 'index'])->name('roles.index');
        Route::get   ('roles/{role}',                [$rc, 'show'])->name('roles.show');
        Route::post  ('roles',                       [$rc, 'store'])->name('roles.store');
        Route::put   ('roles/{role}',                [$rc, 'update'])->name('roles.update');
        Route::delete('roles/{role}',                [$rc, 'destroy'])->name('roles.destroy');
        Route::put   ('roles/{role}/permissions',    [$rc, 'syncPermissions'])->name('roles.permissions.sync');

        // ── Permissions (lecture seule) ───────────────────────────────────
        Route::get('permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->name('permissions.index');

        // ── Configuration système (key/value) ─────────────────────────────
        Route::get ('config',  [\App\Http\Controllers\Admin\ConfigController::class, 'index'])->name('config.index');
        Route::put ('config',  [\App\Http\Controllers\Admin\ConfigController::class, 'update'])->name('config.update');

        // ── Référentiels intranet (statuts, priorités) ────────────────────
        $sc = \App\Http\Controllers\Admin\StatutController::class;
        Route::get   ('statuts',           [$sc, 'index'])->name('statuts.index');
        Route::post  ('statuts',           [$sc, 'store'])->name('statuts.store');
        Route::put   ('statuts/{statut}',  [$sc, 'update'])->name('statuts.update');
        Route::delete('statuts/{statut}',  [$sc, 'destroy'])->name('statuts.destroy');

        $pc = \App\Http\Controllers\Admin\PrioriteController::class;
        Route::get   ('priorites',              [$pc, 'index'])->name('priorites.index');
        Route::post  ('priorites',              [$pc, 'store'])->name('priorites.store');
        Route::put   ('priorites/{priorite}',   [$pc, 'update'])->name('priorites.update');
        Route::delete('priorites/{priorite}',   [$pc, 'destroy'])->name('priorites.destroy');

        // Référentiels RH (types de contrat, postes, départements)
        // Endpoint JSON (plus spécifique, déclaré en premier)
        Route::get('referentiel-rh/{type}/options', [\App\Http\Controllers\Admin\ReferentielRhController::class, 'options'])
            ->where('type', 'types-contrat|postes|departements|types-evenement|niveaux-qualification|nationalites|groupes-rubriques|pays-loc|provinces|departements-admin|prefectures|sous-prefectures|communes|arrondissements|quartiers|cantons|regroupements-village|villages')
            ->name('referentiel-rh.options');
        Route::get('referentiel-rh/{type}', [\App\Http\Controllers\Admin\ReferentielRhController::class, 'index'])
            ->where('type', 'types-contrat|postes|departements|types-evenement|niveaux-qualification|nationalites|groupes-rubriques|pays-loc|provinces|departements-admin|prefectures|sous-prefectures|communes|arrondissements|quartiers|cantons|regroupements-village|villages')
            ->name('referentiel-rh.index');
        Route::post('referentiel-rh/{type}', [\App\Http\Controllers\Admin\ReferentielRhController::class, 'store'])
            ->where('type', 'types-contrat|postes|departements|types-evenement|niveaux-qualification|nationalites|groupes-rubriques|pays-loc|provinces|departements-admin|prefectures|sous-prefectures|communes|arrondissements|quartiers|cantons|regroupements-village|villages')
            ->name('referentiel-rh.store');
        Route::put('referentiel-rh/{type}/{id}', [\App\Http\Controllers\Admin\ReferentielRhController::class, 'update'])
            ->where('type', 'types-contrat|postes|departements|types-evenement|niveaux-qualification|nationalites|groupes-rubriques|pays-loc|provinces|departements-admin|prefectures|sous-prefectures|communes|arrondissements|quartiers|cantons|regroupements-village|villages')
            ->name('referentiel-rh.update');
        Route::delete('referentiel-rh/{type}/{id}', [\App\Http\Controllers\Admin\ReferentielRhController::class, 'destroy'])
            ->where('type', 'types-contrat|postes|departements|types-evenement|niveaux-qualification|nationalites|groupes-rubriques|pays-loc|provinces|departements-admin|prefectures|sous-prefectures|communes|arrondissements|quartiers|cantons|regroupements-village|villages')
            ->name('referentiel-rh.destroy');

        // Rôles projet
        Route::get('roles-projet', [\App\Http\Controllers\Admin\RoleProjetController::class, 'index'])->name('roles-projet.index');
        Route::post('roles-projet', [\App\Http\Controllers\Admin\RoleProjetController::class, 'store'])->name('roles-projet.store');
        Route::put('roles-projet/{role}', [\App\Http\Controllers\Admin\RoleProjetController::class, 'update'])->name('roles-projet.update');
        Route::delete('roles-projet/{role}', [\App\Http\Controllers\Admin\RoleProjetController::class, 'destroy'])->name('roles-projet.destroy');
        Route::post('roles-projet/reorder', [\App\Http\Controllers\Admin\RoleProjetController::class, 'reorder'])->name('roles-projet.reorder');

        // Vitrine — gestion du contenu du site public
        Route::prefix('vitrine')->name('vitrine.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VitrineController::class, 'index'])->name('index');

            Route::post('slides',                [\App\Http\Controllers\Admin\VitrineController::class, 'storeSlide'])->name('slides.store');
            Route::put('slides/{slide}',         [\App\Http\Controllers\Admin\VitrineController::class, 'updateSlide'])->name('slides.update');
            Route::delete('slides/{slide}',      [\App\Http\Controllers\Admin\VitrineController::class, 'destroySlide'])->name('slides.destroy');

            Route::post('modules',               [\App\Http\Controllers\Admin\VitrineController::class, 'storeModule'])->name('modules.store');
            Route::put('modules/{module}',       [\App\Http\Controllers\Admin\VitrineController::class, 'updateModule'])->name('modules.update');
            Route::delete('modules/{module}',    [\App\Http\Controllers\Admin\VitrineController::class, 'destroyModule'])->name('modules.destroy');

            Route::post('captures',              [\App\Http\Controllers\Admin\VitrineController::class, 'storeCapture'])->name('captures.store');
            Route::put('captures/{capture}',     [\App\Http\Controllers\Admin\VitrineController::class, 'updateCapture'])->name('captures.update');
            Route::delete('captures/{capture}',  [\App\Http\Controllers\Admin\VitrineController::class, 'destroyCapture'])->name('captures.destroy');

            Route::post('atouts',                [\App\Http\Controllers\Admin\VitrineController::class, 'storeAtout'])->name('atouts.store');
            Route::put('atouts/{atout}',         [\App\Http\Controllers\Admin\VitrineController::class, 'updateAtout'])->name('atouts.update');
            Route::delete('atouts/{atout}',      [\App\Http\Controllers\Admin\VitrineController::class, 'destroyAtout'])->name('atouts.destroy');

            Route::put('settings',               [\App\Http\Controllers\Admin\VitrineController::class, 'updateSettings'])->name('settings.update');
            Route::post('reorder/{type}',        [\App\Http\Controllers\Admin\VitrineController::class, 'reorder'])->name('reorder');
        });
    });
});
