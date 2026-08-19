<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\Ligne;
use App\Models\Titre;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = User::factory()->create();

    $t = Titre::create(['imputation' => 'T-P', 'libelle' => 'P', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 31001, 'libelle' => 'LP', 'nature' => 'depense', 'id_user' => $this->auteur->id]);
    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-PR-' . random_int(100, 999), 'libelle' => 'PR',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-PR', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 500_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();
});

it('form create : contient le loader et l\'overlay de submit', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.ordres.create', ['modele' => $this->modele->id]));
    $r->assertOk();
    $r->assertSee('ordre-loading-overlay', false);
    $r->assertSee('ordre-submit-btn', false);
    $r->assertSee('justificatifs-preview', false);
});

it('form create : contient l\'input file multiple pour justificatifs', function () {
    actingAsSuperAdmin();
    $r = $this->get(route('finance.ordres.create', ['modele' => $this->modele->id]));
    $r->assertSee('justificatifs[]', false);
    $r->assertSee('multiple', false);
});

it('show : modale preview injectée dans la page', function () {
    Storage::fake('public');
    actingAsSuperAdmin();

    $r = $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modele->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees' => [
            'nature_depense' => 'X', 'imputation_budget' => '0',
            'beneficiaire_raison' => 'Y', 'ref_date' => '2027-01-01',
            'montant' => 100, 'mode_reglement' => 'numeraire',
        ],
        'justificatifs' => [UploadedFile::fake()->image('img.png')],
    ]);
    $r->assertRedirect();
    $ordre = Ordre::latest()->first();

    $show = $this->get(route('finance.ordres.show', $ordre));
    $show->assertOk();
    $show->assertSee('modal-preview-pj', false);
    $show->assertSee('preview-trigger', false);
    $show->assertSee('data-preview-loader', false);
});

it('show : bouton preview a bien les data-attributes url/kind/name', function () {
    Storage::fake('public');
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), [
        'modele_id'       => $this->modele->id,
        'exercice_id'     => $this->exercice->id,
        'budget_ligne_id' => $this->bl->id,
        'donnees' => [
            'nature_depense' => 'X', 'imputation_budget' => '0',
            'beneficiaire_raison' => 'Y', 'ref_date' => '2027-01-01',
            'montant' => 100, 'mode_reglement' => 'numeraire',
        ],
        'justificatifs' => [
            UploadedFile::fake()->image('photo.jpg'),
            UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ],
    ]);
    $ordre = Ordre::latest()->first();
    $r = $this->get(route('finance.ordres.show', $ordre));

    // Image → data-kind="image"
    $r->assertSee('data-kind="image"', false);
    // PDF → data-kind="pdf"
    $r->assertSee('data-kind="pdf"', false);
    // Nom du fichier passé en data
    $r->assertSee('data-name="photo.jpg"', false);
    $r->assertSee('data-name="doc.pdf"', false);
});
