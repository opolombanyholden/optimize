<?php

use App\Models\CampagnePaie;
use App\Models\Employee;
use App\Models\Paie;
use App\Services\Rh\CfonbExporter;

beforeEach(function () {
    seedRoles();
});

function makeCampagneAvecBulletins(int $nbBulletins, int $netUnitaire = 500_000): CampagnePaie
{
    $campagne = CampagnePaie::create([
        'code'         => 'CFONB-TEST-' . random_int(1000, 9999),
        'libelle'      => 'Test CFONB',
        'annee'        => 2026,
        'mois'         => 5,
        'date_debut'   => '2026-05-01',
        'date_fin'     => '2026-05-31',
        'date_paiement_prevue' => '2026-06-05',
        'periodicite'  => 'mensuelle',
        'statut'       => 2,
        'simulation'   => false,
    ]);

    for ($i = 1; $i <= $nbBulletins; $i++) {
        $emp = Employee::create([
            'noms'         => 'Test' . $i,
            'prenoms'      => 'CFONB',
            'matricule'    => 'CFB-' . $i . '-' . random_int(100, 999),
            'email'        => 'cfonb' . $i . '@test.local',
            'iban'         => 'FR7630006000011234567890189',
            'salaire_base' => $netUnitaire,
            'type_contrat' => 'CDI',
            'date_embauche' => now()->subYear(),
            'statut'       => 1,
        ]);
        Paie::create([
            'campagne_paie_id' => $campagne->id,
            'employee_id'      => $emp->id,
            'label'            => 'Bulletin',
            'numero_bulletin'  => 'B-2026-05-CFB-' . $i . '-' . random_int(100, 999),
            'debut'            => '2026-05-01',
            'fin'              => '2026-05-31',
            'salaire_base'     => $netUnitaire,
            'brut'             => $netUnitaire,
            'net_a_payer'      => $netUnitaire,
            'statut'           => 1,
        ]);
    }

    return $campagne;
}

// Helper : split sans manger les espaces de fin (trim() casserait la dernière ligne CFONB).
function splitCfonb(string $contenu): array
{
    return preg_split('/\r\n/', rtrim($contenu, "\r\n"));
}

it('exporter CFONB produit des lignes de 160 caractères', function () {
    $campagne = makeCampagneAvecBulletins(3);
    $exporter = new CfonbExporter();
    $contenu = $exporter->exporter($campagne, [
        'raison_sociale' => 'YUBILE TECHNOLOGIE',
        'num_emetteur'   => '000001',
        'rib'            => '0000100002000000000123456',
    ]);

    $lignes = splitCfonb($contenu);
    expect($lignes)->toHaveCount(5); // 1 entête + 3 virements + 1 total
    foreach ($lignes as $i => $l) {
        expect(strlen($l))->toBe(160, "Ligne $i doit faire 160 caractères, fait " . strlen($l));
    }
});

it('exporter CFONB respecte les codes d\'enregistrement (03/06/08)', function () {
    $campagne = makeCampagneAvecBulletins(2);
    $exporter = new CfonbExporter();
    $contenu = $exporter->exporter($campagne, [
        'raison_sociale' => 'TEST',
        'num_emetteur'   => '000001',
        'rib'            => '0000100002000000000123456',
    ]);
    $lignes = splitCfonb($contenu);

    expect(substr($lignes[0], 0, 2))->toBe('03'); // En-tête
    expect(substr($lignes[1], 0, 2))->toBe('06'); // Virement 1
    expect(substr($lignes[2], 0, 2))->toBe('06'); // Virement 2
    expect(substr($lignes[3], 0, 2))->toBe('08'); // Total
});

it('le total CFONB correspond à la somme des virements (en centimes)', function () {
    $campagne = makeCampagneAvecBulletins(3, 100_000); // 3 × 100.000 = 300.000 XAF = 30.000.000 centimes
    $exporter = new CfonbExporter();
    $contenu = $exporter->exporter($campagne, [
        'raison_sociale' => 'T',
        'num_emetteur'   => '000001',
        'rib'            => '0000100002000000000123456',
    ]);
    $lignes = splitCfonb($contenu);
    // Le total est la DERNIÈRE ligne (code 08)
    $totalLine = end($lignes);
    expect(substr($totalLine, 0, 2))->toBe('08');
    // Position 140-152 = montant total sur 13 caractères
    $totalCentimes = (int) substr($totalLine, 139, 13);
    expect($totalCentimes)->toBe(30_000_000);
});

it('exporter CFONB est purement ASCII (caractères latins normalisés)', function () {
    $campagne = makeCampagneAvecBulletins(1, 500_000);
    // Crée un employé avec un nom accentué
    Employee::where('matricule', 'like', 'CFB-%')->update(['noms' => 'Mbéngué', 'prenoms' => 'François']);
    $exporter = new CfonbExporter();
    $contenu = $exporter->exporter($campagne, [
        'raison_sociale' => 'YUBILE GABON & Cie',
        'num_emetteur'   => '000001',
        'rib'            => '0000100002000000000123456',
    ]);
    // Vérification ASCII pur
    expect(preg_match('/[^\x20-\x7E\r\n]/', $contenu))->toBe(0);
});

it('téléchargement HTTP du fichier CFONB-160', function () {
    actingAsSuperAdmin();
    $campagne = makeCampagneAvecBulletins(2);

    $resp = $this->get("/rh/campagnes-paie/{$campagne->id}/ov.cfonb");
    $resp->assertOk();
    expect($resp->headers->get('Content-Type'))->toContain('text/plain');
    expect($resp->headers->get('Content-Disposition'))->toContain('.txt');
    // Le contenu doit avoir au moins 4 lignes de 160 chars (en-tête + 2 virements + total)
    $body = $resp->getContent();
    $lines = preg_split('/\r\n/', trim($body));
    expect(count($lines))->toBe(4);
});
