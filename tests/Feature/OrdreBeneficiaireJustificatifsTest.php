<?php

use App\Models\BudgetLigne;
use App\Models\Exercice;
use App\Models\Finance\Ordre;
use App\Models\Finance\OrdreModele;
use App\Models\Intranet\Contact;
use App\Models\Intranet\ContactOrganisation;
use App\Models\Ligne;
use App\Models\Titre;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedRoles();
    (new \Database\Seeders\OrdreModelesSeeder())->run();
    $this->auteur = User::factory()->create();

    $t = Titre::create(['imputation' => 'T-BJ', 'libelle' => 'BJ', 'type_ligne' => 'depense', 'id_user' => $this->auteur->id]);
    $this->ligne = Ligne::create(['id_titre' => $t->id, 'id_codeanalytique' => 61001, 'libelle' => 'L BJ', 'nature' => 'depense', 'id_user' => $this->auteur->id]);

    Exercice::where('statut', Exercice::STATUT_EN_EXECUTION)->update(['statut' => Exercice::STATUT_CLOTURE]);
    $this->exercice = Exercice::create([
        'exercice' => 'EX-BJ-' . random_int(100, 999), 'libelle' => 'BJ',
        'statut' => Exercice::STATUT_EN_EXECUTION, 'id_user' => $this->auteur->id,
    ]);
    $this->bl = BudgetLigne::create([
        'id_budgetligne' => 'BL-BJ', 'id_exercicebudgetaire' => $this->exercice->id,
        'id_codeanalytique' => $this->ligne->id, 'id_famillecodeanalytique' => $t->id,
        'dotation_etat' => 1_000_000, 'isvalide' => 1, 'id_user' => $this->auteur->id,
    ]);
    $this->modele = OrdreModele::where('code', 'ordonnance_paiement')->first();
});

function payloadOrdreBJ(array $extra = []): array {
    return array_merge([
        'modele_id'       => test()->modele->id,
        'exercice_id'     => test()->exercice->id,
        'budget_ligne_id' => test()->bl->id,
        'donnees'         => [
            'nature_depense' => 'Test bénéf', 'imputation_budget' => '0',
            'beneficiaire_raison' => 'X', 'ref_date' => '2027-01-01',
            'montant' => 50_000, 'mode_reglement' => 'virement',
        ],
    ], $extra);
}

// ═════════ Bénéficiaire ═════════

it('bénéficiaire externe : infos JSON persistées, aucune FK', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'beneficiaire_source' => 'externe',
        'beneficiaire_infos'  => [
            'nom'       => 'Kabou & Fils SARL',
            'entite'    => 'Direction commerciale',
            'telephone' => '+241 06 00 00 00',
            'email'     => 'contact@kabou.ga',
            'adresse'   => 'BP 123 Libreville',
            'nif'       => 'NIF-999',
        ],
    ]))->assertRedirect();

    $o = Ordre::latest()->first();
    expect($o->beneficiaire_source)->toBe('externe');
    expect($o->beneficiaire_ref_id)->toBeNull();
    expect($o->beneficiaire_infos_json['nom'])->toBe('Kabou & Fils SARL');
    expect($o->beneficiaire_infos_json['nif'])->toBe('NIF-999');
});

it('bénéficiaire utilisateur interne : FK stockée + snapshot infos', function () {
    actingAsSuperAdmin();
    $user = User::factory()->create(['name' => 'Doe', 'prenoms' => 'John', 'email' => 'jd@x.com']);

    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'beneficiaire_source' => 'user',
        'beneficiaire_ref_id' => $user->id,
    ]))->assertRedirect();

    $o = Ordre::latest()->first();
    expect($o->beneficiaire_source)->toBe('user');
    expect($o->beneficiaire_ref_id)->toBe($user->id);
    expect($o->beneficiaire_infos_json['nom'])->toContain('Doe');
    expect($o->beneficiaire_infos_json['email'])->toBe('jd@x.com');
});

it('bénéficiaire organisation : FK + snapshot des infos B2B', function () {
    actingAsSuperAdmin();
    $org = ContactOrganisation::create([
        'nom' => 'Alpha', 'raison_sociale' => 'Alpha SA', 'type' => 'fournisseur',
        'nif' => 'A-99', 'email' => 'a@alpha.ga', 'created_by' => $this->auteur->id,
    ]);

    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'beneficiaire_source' => 'organisation',
        'beneficiaire_ref_id' => $org->id,
    ]))->assertRedirect();

    $o = Ordre::latest()->first();
    expect($o->beneficiaire_source)->toBe('organisation');
    expect($o->beneficiaire_ref_id)->toBe($org->id);
    expect($o->beneficiaire_infos_json['nom'])->toBe('Alpha SA');
    expect($o->beneficiaire_infos_json['nif'])->toBe('A-99');
});

it('accessor beneficiaire_libelle résout depuis l\'entité liée', function () {
    $user = User::factory()->create(['name' => 'Nom', 'prenoms' => 'Prenom']);
    $o = Ordre::create([
        'modele_id' => $this->modele->id, 'numero_ordre' => 'BL-1',
        'exercice_id' => $this->exercice->id, 'budget_ligne_id' => $this->bl->id,
        'sens' => 'depense', 'montant' => 100, 'created_by' => $this->auteur->id,
        'beneficiaire_source' => 'user', 'beneficiaire_ref_id' => $user->id,
    ]);
    expect($o->beneficiaire_libelle)->toBe('Prenom Nom');
});

it('label UI change selon sens : Bénéficiaire pour dépense, Client pour recette', function () {
    $depense = new Ordre(['sens' => 'depense']);
    $recette = new Ordre(['sens' => 'recette']);
    expect($depense->beneficiaire_label_ui)->toBe('Bénéficiaire');
    expect($recette->beneficiaire_label_ui)->toBe('Client');
});

// ═════════ Justificatifs ═════════

it('upload : plusieurs justificatifs attachés à l\'ordre', function () {
    Storage::fake('public');
    actingAsSuperAdmin();

    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'justificatifs' => [
            UploadedFile::fake()->image('facture.png'),
            UploadedFile::fake()->create('recu.pdf', 500, 'application/pdf'),
        ],
    ]))->assertRedirect();

    $o = Ordre::latest()->first();
    expect($o->piecesJointes()->count())->toBe(2);
});

it('validation : refuse un fichier au format non autorisé', function () {
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'justificatifs' => [UploadedFile::fake()->create('malware.exe', 100)],
    ]))->assertSessionHasErrors('justificatifs.0');
});

it('suppression d\'un justificatif ne fonctionne que sur ordre brouillon', function () {
    Storage::fake('public');
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'justificatifs' => [UploadedFile::fake()->image('x.png')],
    ]));
    $o = Ordre::latest()->first();
    $pj = $o->piecesJointes()->first();

    $this->delete(route('finance.ordres.justificatifs.destroy', [$o, $pj]))
         ->assertSessionHas('success');
    expect($o->fresh()->piecesJointes()->count())->toBe(0);
});

it('suppression refusée pour un user standard sur ordre soumis', function () {
    Storage::fake('public');
    actingAsSuperAdmin();
    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'justificatifs' => [UploadedFile::fake()->image('x.png')],
    ]));
    $o = Ordre::latest()->first();
    $o->soumettre($this->auteur->id); // pas modifiable
    $pj = $o->piecesJointes()->first();

    // User standard non-super-admin
    $u = User::factory()->create();
    $u->givePermissionTo(['read:operation', 'update:operation']);
    $this->actingAs($u);

    $this->delete(route('finance.ordres.justificatifs.destroy', [$o, $pj]))
         ->assertSessionHas('error');
    expect($o->fresh()->piecesJointes()->count())->toBe(1);
});

// ═════════ Intégration GL ═════════

it('exécution : bénéficiaire recopié dans le Grand Livre', function () {
    actingAsSuperAdmin();
    // Compte requis pour l'exécution
    $compte = \App\Models\Compte::create([
        'code' => 'BJ-BQ', 'nom' => 'Compte BJ', 'type' => \App\Models\Compte::TYPE_BANQUE,
        'solde' => 1_000_000, 'solde_initial' => 1_000_000, 'devise' => 'XAF',
        'actif' => true, 'effacer' => 0,
    ]);
    $this->post(route('finance.ordres.store'), payloadOrdreBJ([
        'beneficiaire_source' => 'externe',
        'beneficiaire_infos'  => ['nom' => 'Externe SARL'],
        'compte_id'           => $compte->id,
    ]));
    $o = Ordre::latest()->first();
    $o->soumettre($this->auteur->id);
    $o->signer($this->auteur->id);
    $this->post(route('finance.ordres.executer', $o));

    $gl = \App\Models\GrandLivre::find($o->fresh()->grand_livre_id);
    expect($gl->beneficiaire)->toBe('Externe SARL');
    expect($gl->type_beneficiaire)->toBe('externe');
});
