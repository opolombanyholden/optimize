<?php

namespace Database\Seeders;

use App\Models\Intranet\TypeDocument;
use App\Models\Intranet\TypeDocumentExigence;
use Illuminate\Database\Seeder;

/**
 * Référentiel des types de documents attendus par les organisations,
 * avec la matrice d'exigence par type d'organisation.
 *
 * Les entrées existantes sont conservées (upsert par `code`) ; seul les
 * exigences peuvent être ré-alignées avec la matrice ci-dessous.
 */
class TypesDocumentsSeeder extends Seeder
{
    /**
     * Documents standards. `avec_expiration` = true pour les pièces
     * à renouveler périodiquement (attestations fiscales, CNSS…).
     */
    private array $documents = [
        ['code' => 'nif',                  'libelle' => 'NIF (Numéro Identifiant Fiscal)', 'icone' => 'fa-hashtag',        'avec_expiration' => false, 'ordre' => 10],
        ['code' => 'rccm',                 'libelle' => 'RCCM',                            'icone' => 'fa-scale-balanced', 'avec_expiration' => false, 'ordre' => 20],
        ['code' => 'statuts',              'libelle' => 'Statuts de la société',           'icone' => 'fa-file-signature', 'avec_expiration' => false, 'ordre' => 30],
        ['code' => 'attestation_fiscale',  'libelle' => 'Attestation fiscale',             'icone' => 'fa-file-invoice',   'avec_expiration' => true,  'ordre' => 40],
        ['code' => 'attestation_cnss',     'libelle' => 'Attestation CNSS',                'icone' => 'fa-user-shield',    'avec_expiration' => true,  'ordre' => 50],
        ['code' => 'rib',                  'libelle' => 'RIB / relevé bancaire',           'icone' => 'fa-building-columns','avec_expiration' => false,'ordre' => 60],
        ['code' => 'kyc',                  'libelle' => 'Fiche KYC',                       'icone' => 'fa-id-card',        'avec_expiration' => false, 'ordre' => 70],
        ['code' => 'convention',           'libelle' => 'Convention / contrat',            'icone' => 'fa-file-contract',  'avec_expiration' => true,  'ordre' => 80],
        ['code' => 'decret_creation',      'libelle' => 'Décret / acte de création',       'icone' => 'fa-landmark',       'avec_expiration' => false, 'ordre' => 90],
        ['code' => 'attestation_non_faillite','libelle' => 'Attestation de non-faillite', 'icone' => 'fa-shield-halved',   'avec_expiration' => true,  'ordre' => 100],
    ];

    /**
     * Matrice d'exigence : par type d'organisation, la liste des codes exigés
     * et si obligatoires (true) ou facultatifs (false).
     */
    private array $matrice = [
        'client' => [
            'nif'                 => true,
            'rccm'                => false,
            'attestation_fiscale' => true,
            'rib'                 => false,
            'statuts'             => false,
        ],
        'fournisseur' => [
            'nif'                     => true,
            'rccm'                    => true,
            'statuts'                 => true,
            'attestation_fiscale'     => true,
            'attestation_cnss'        => true,
            'rib'                     => true,
            'attestation_non_faillite'=> false,
            'kyc'                     => false,
        ],
        'investisseur' => [
            'statuts'             => true,
            'nif'                 => false,
            'attestation_fiscale' => false,
            'kyc'                 => true,
        ],
        'administration' => [
            'decret_creation' => false,
        ],
        'partenaire' => [
            'convention' => true,
            'nif'        => false,
        ],
        'autre' => [],
    ];

    public function run(): void
    {
        $creees = 0;
        foreach ($this->documents as $data) {
            TypeDocument::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['actif' => true])
            );
            $creees++;
        }

        // Reset des exigences puis rebuild depuis la matrice (idempotent).
        TypeDocumentExigence::truncate();

        $exigencesCount = 0;
        foreach ($this->matrice as $typeOrg => $exigences) {
            foreach ($exigences as $codeDoc => $obligatoire) {
                $type = TypeDocument::where('code', $codeDoc)->first();
                if (!$type) continue;
                TypeDocumentExigence::create([
                    'type_document_id'  => $type->id,
                    'type_organisation' => $typeOrg,
                    'obligatoire'       => $obligatoire,
                ]);
                $exigencesCount++;
            }
        }

        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->command?->info("Types documents : {$creees} type(s), {$exigencesCount} exigence(s) matrice appliquées.");
        }
    }
}
