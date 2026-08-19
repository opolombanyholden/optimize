<?php

use App\Models\Intranet\ContactOrganisation;
use App\Models\Intranet\OrganisationDocument;
use App\Models\Intranet\TypeDocument;
use App\Models\Intranet\TypeDocumentExigence;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedRoles();
    $this->auteur = User::factory()->create();
});

function mkType(array $data): TypeDocument
{
    return TypeDocument::create(array_merge([
        'code' => 'doc-' . uniqid(),
        'libelle' => 'Document test',
        'actif' => true,
    ], $data));
}

function mkExigence(TypeDocument $type, string $typeOrg, bool $obligatoire): TypeDocumentExigence
{
    return TypeDocumentExigence::create([
        'type_document_id'  => $type->id,
        'type_organisation' => $typeOrg,
        'obligatoire'       => $obligatoire,
    ]);
}

function mkOrga2(array $data): ContactOrganisation
{
    return ContactOrganisation::create(array_merge([
        'created_by' => test()->auteur->id,
        'nom'        => 'Test Org',
    ], $data));
}

it('lists documents attendus filtered by organisation type', function () {
    $nif = mkType(['code' => 'nif-t', 'libelle' => 'NIF']);
    $rccm = mkType(['code' => 'rccm-t', 'libelle' => 'RCCM']);
    $kyc = mkType(['code' => 'kyc-t', 'libelle' => 'KYC']);

    mkExigence($nif, 'client', true);
    mkExigence($rccm, 'client', false);
    mkExigence($kyc, 'fournisseur', true); // pas visible pour client

    $orga = mkOrga2(['type' => 'client']);
    $attendus = $orga->documentsAttendus();

    expect($attendus)->toHaveCount(2);
    expect($attendus->pluck('type_document.code')->all())->toContain('nif-t', 'rccm-t');
    expect($attendus->pluck('type_document.code')->all())->not->toContain('kyc-t');
});

it('detects mandatory documents and computes completeness', function () {
    $nif = mkType(['code' => 'nif-2', 'libelle' => 'NIF']);
    $rccm = mkType(['code' => 'rccm-2', 'libelle' => 'RCCM']);
    mkExigence($nif, 'client', true);
    mkExigence($rccm, 'client', false);

    $orga = mkOrga2(['type' => 'client']);

    $c = $orga->completude_documents;
    expect($c['obligatoires_total'])->toBe(1);
    expect($c['obligatoires_fournis'])->toBe(0);
    expect($c['complet'])->toBeFalse();

    OrganisationDocument::create([
        'organisation_id'  => $orga->id,
        'type_document_id' => $nif->id,
        'fichier'          => 'x.pdf',
        'uploaded_by'      => $this->auteur->id,
    ]);
    $c = $orga->fresh()->completude_documents;
    expect($c['obligatoires_fournis'])->toBe(1);
    expect($c['complet'])->toBeTrue();
    expect((int) $c['pct'])->toBe(100);
});

it('uploads and deletes a document via HTTP', function () {
    Storage::fake('public');
    actingAsSuperAdmin();

    $type = mkType(['code' => 'up-1', 'libelle' => 'Upload test']);
    $orga = mkOrga2(['type' => 'client']);

    $r = $this->post(route('intranet.organisations.documents.store', $orga), [
        'type_document_id' => $type->id,
        'fichier'          => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
    ]);
    $r->assertRedirect();
    expect($orga->documents()->count())->toBe(1);

    $doc = $orga->documents()->first();
    Storage::disk('public')->assertExists($doc->fichier);

    $this->delete(route('intranet.organisations.documents.destroy', [$orga, $doc]))
        ->assertRedirect();
    expect($orga->fresh()->documents()->count())->toBe(0);
    Storage::disk('public')->assertMissing($doc->fichier);
});

it('renders the types-documents matrice screen', function () {
    actingAsSuperAdmin();
    mkType(['code' => 'mat-1', 'libelle' => 'Matrice type']);
    $r = $this->get(route('intranet.organisations.types-documents.index'));
    $r->assertOk();
    $r->assertSee('Matrice');
    $r->assertSee('Matrice type');
});

it('saves the matrice via POST', function () {
    actingAsSuperAdmin();
    $t = mkType(['code' => 'mat-2', 'libelle' => 'M2']);

    $this->post(route('intranet.organisations.types-documents.matrice.save'), [
        'matrice' => [$t->id => ['client' => 'obligatoire', 'fournisseur' => 'facultatif']],
    ])->assertRedirect();

    expect(TypeDocumentExigence::where('type_document_id', $t->id)->where('type_organisation', 'client')->first()?->obligatoire)->toBeTrue();
    expect(TypeDocumentExigence::where('type_document_id', $t->id)->where('type_organisation', 'fournisseur')->first()?->obligatoire)->toBeFalse();
});

it('detects expired documents', function () {
    $t = mkType(['code' => 'exp-1', 'libelle' => 'Exp', 'avec_expiration' => true]);
    $orga = mkOrga2(['type' => 'client']);
    $doc = OrganisationDocument::create([
        'organisation_id'  => $orga->id,
        'type_document_id' => $t->id,
        'fichier'          => 'x.pdf',
        'date_expiration'  => now()->subDay(),
        'uploaded_by'      => $this->auteur->id,
    ]);
    expect($doc->est_expire)->toBeTrue();
});
