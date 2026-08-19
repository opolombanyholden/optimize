<?php

namespace App\Services\Rh;

use App\Models\DeclarationSociale;
use App\Models\DeclarationSocialeLigne;
use App\Models\Employee;
use App\Models\Paie;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Agrège les bulletins de paie validés/payés sur une période pour produire
 * une déclaration sociale (CNSS, CNAMGS, FNH, CFP).
 *
 * Les cotisations sont relues depuis les lignes (gpaie_rubrique) pour ne prendre
 * que celles correspondant à l'organisme. Convention de code rubrique :
 *   - CNSS_S / CNSS_P
 *   - CNAMGS_S / CNAMGS_P
 *   - FNH   (patronale uniquement)
 *   - CFP   (patronale uniquement)
 */
class DeclarationSocialeBuilder
{
    /**
     * Calcule (sans persister) les lignes pour une période donnée.
     */
    public function calculer(string $typeOrganisme, int $annee, ?int $mois, ?int $trimestre): array
    {
        [$debut, $fin, $periodicite] = $this->resoudrePeriode($typeOrganisme, $annee, $mois, $trimestre);
        $config = DeclarationSociale::ORGANISMES[$typeOrganisme] ?? null;
        if (!$config) {
            throw new \InvalidArgumentException("Organisme inconnu : $typeOrganisme");
        }
        $plafond = (float) $config['plafond'];

        // Codes courts (libelle_court) à inclure pour cet organisme.
        // Convention seeder ANPI : libelle_court canonique = CNSS_S/CNSS_P/CNAMGS_S/CNAMGS_P/FNH/CFP.
        $codesSalariaux = [];
        $codesPatronaux = [];
        switch ($typeOrganisme) {
            case 'cnss':   $codesSalariaux = ['CNSS_S'];   $codesPatronaux = ['CNSS_P']; break;
            case 'cnamgs': $codesSalariaux = ['CNAMGS_S']; $codesPatronaux = ['CNAMGS_P']; break;
            case 'fnh':    $codesPatronaux = ['FNH']; break;
            case 'cfp':    $codesPatronaux = ['CFP']; break;
        }

        // Bulletins éligibles
        $bulletins = Paie::with(['employee', 'rubriques'])
            ->whereBetween('debut', [$debut, $fin])
            ->whereIn('statut', [1, 2])
            ->get();

        $parEmploye = $bulletins->groupBy('employee_id');
        $lignes = [];
        $totalBrut = $totalBrutPlaf = $totalCotSal = $totalCotPat = 0;

        foreach ($parEmploye as $empId => $bulls) {
            $emp = $bulls->first()->employee;
            if (!$emp) continue;

            $bullBrut = (float) $bulls->sum('brut');
            $bullPlaf = $plafond > 0 ? min($bullBrut, $plafond * $bulls->count()) : $bullBrut;
            $bullCotSal = 0;
            $bullCotPat = 0;

            foreach ($bulls as $b) {
                foreach ($b->rubriques as $r) {
                    if (in_array($r->libelle_court, $codesSalariaux, true)) {
                        $bullCotSal += (float) $r->pivot->montant;
                    } elseif (in_array($r->libelle_court, $codesPatronaux, true)) {
                        $bullCotPat += (float) $r->pivot->montant;
                    }
                }
            }

            $matriculeOrganisme = match ($typeOrganisme) {
                'cnss'   => $emp->matricule_cnss,
                'cnamgs' => $emp->matricule_cnamgs,
                default  => null,
            };

            $lignes[] = [
                'employee_id'         => $emp->id,
                'matricule_employeur' => $emp->matricule,
                'matricule_organisme' => $matriculeOrganisme,
                'nip'                 => $emp->nip ?? null,
                'noms'                => $emp->noms,
                'prenoms'             => $emp->prenoms,
                'date_naissance'      => $emp->date_naissance,
                'sexe'                => $emp->sexe,
                'nb_jours_travailles' => 30 * $bulls->count(),
                'brut'                => round($bullBrut, 2),
                'brut_plafonne'       => round($bullPlaf, 2),
                'cot_salariale'       => round($bullCotSal, 2),
                'cot_patronale'       => round($bullCotPat, 2),
                'bulletins_inclus'    => $bulls->pluck('id')->all(),
            ];

            $totalBrut += $bullBrut;
            $totalBrutPlaf += $bullPlaf;
            $totalCotSal += $bullCotSal;
            $totalCotPat += $bullCotPat;
        }

        return [
            'periode' => [
                'type_organisme' => $typeOrganisme,
                'annee'          => $annee,
                'mois'           => $mois,
                'trimestre'      => $trimestre,
                'periodicite'    => $periodicite,
                'date_debut'     => $debut->toDateString(),
                'date_fin'       => $fin->toDateString(),
            ],
            'totaux' => [
                'nombre_employes'      => count($lignes),
                'total_brut'           => round($totalBrut, 2),
                'total_brut_plafonne'  => round($totalBrutPlaf, 2),
                'total_cot_salariale'  => round($totalCotSal, 2),
                'total_cot_patronale'  => round($totalCotPat, 2),
            ],
            'lignes' => $lignes,
        ];
    }

    public function persister(array $resultat, ?int $userId = null): DeclarationSociale
    {
        $p = $resultat['periode'];
        $t = $resultat['totaux'];

        return DB::transaction(function () use ($p, $t, $resultat, $userId) {
            $code = $this->genererCode($p);
            $declaration = DeclarationSociale::create([
                'code'                  => $code,
                'type_organisme'        => $p['type_organisme'],
                'annee'                 => $p['annee'],
                'mois'                  => $p['mois'],
                'trimestre'             => $p['trimestre'],
                'periodicite'           => $p['periodicite'],
                'date_debut'            => $p['date_debut'],
                'date_fin'              => $p['date_fin'],
                'statut'                => 0,
                'nombre_employes'       => $t['nombre_employes'],
                'total_brut'            => $t['total_brut'],
                'total_brut_plafonne'   => $t['total_brut_plafonne'],
                'total_cot_salariale'   => $t['total_cot_salariale'],
                'total_cot_patronale'   => $t['total_cot_patronale'],
                'snapshot_employeur'    => $this->snapshotEmployeur(),
                'created_by'            => $userId,
            ]);

            foreach ($resultat['lignes'] as $ligne) {
                $ligne['declaration_sociale_id'] = $declaration->id;
                DeclarationSocialeLigne::create($ligne);
            }
            return $declaration->fresh('lignes');
        });
    }

    private function resoudrePeriode(string $typeOrganisme, int $annee, ?int $mois, ?int $trimestre): array
    {
        $config = DeclarationSociale::ORGANISMES[$typeOrganisme] ?? ['periodicite' => 'mensuelle'];
        $periodicite = $config['periodicite'];

        if ($periodicite === 'trimestrielle') {
            if (!$trimestre || $trimestre < 1 || $trimestre > 4) {
                throw new \InvalidArgumentException("Trimestre invalide pour $typeOrganisme");
            }
            $premierMois = (($trimestre - 1) * 3) + 1;
            $debut = Carbon::create($annee, $premierMois, 1)->startOfMonth();
            $fin   = (clone $debut)->addMonths(2)->endOfMonth();
        } else {
            if (!$mois || $mois < 1 || $mois > 12) {
                throw new \InvalidArgumentException("Mois invalide pour $typeOrganisme");
            }
            $debut = Carbon::create($annee, $mois, 1)->startOfMonth();
            $fin   = (clone $debut)->endOfMonth();
        }

        return [$debut, $fin, $periodicite];
    }

    private function genererCode(array $p): string
    {
        $orga = strtoupper($p['type_organisme']);
        if ($p['periodicite'] === 'trimestrielle') {
            return sprintf('%s-%04d-T%d', $orga, $p['annee'], $p['trimestre']);
        }
        return sprintf('%s-%04d-%02d', $orga, $p['annee'], $p['mois']);
    }

    private function snapshotEmployeur(): array
    {
        return [
            'raison_sociale'    => 'Yubile Technologie',
            'matricule_cnss'    => '001-0185785-0',
            'matricule_cnamgs'  => '1015-0001646',
            'adresse'           => 'BP 3403 Libreville',
            'telephone'         => '01-76-48-48',
        ];
    }
}
