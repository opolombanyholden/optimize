<?php

namespace App\Services\Rh;

use App\Models\Employee;
use App\Models\Paie;
use App\Models\Rubrique;
use Illuminate\Support\Facades\DB;

/**
 * Moteur de calcul de paie à rubriques paramétrables.
 *
 * Variables disponibles dans les formules :
 *   salaire_base, heures_sup, primes, indemnites, brut, net_imposable, jours_travailles
 *
 * Convention :
 *   - rubriques type='gain'       => entrent dans le brut
 *   - rubriques type='cotisation' => salariale par défaut, patronale si code commence par 'PAT_'
 *   - rubriques type='retenue'    => déduites du net_imposable (les codes contenant 'IRPP' sont identifiés comme impôt)
 */
class PaieCalculator
{
    private array $variables = [];
    private array $lignesGains = [];
    private array $lignesRetenues = [];
    private array $lignesCotSalariales = [];
    private array $lignesCotPatronales = [];
    private ExpressionEvaluator $expr;

    public function __construct()
    {
        $this->expr = new ExpressionEvaluator();
    }

    public function calculer(array $inputs): array
    {
        $employee = Employee::findOrFail($inputs['employee_id']);

        // Si heures_sup non fourni, on agrège automatiquement depuis les pointages validés
        // de la période [debut, fin] pour cet employé (statut=1 ou 2).
        $heuresSup = $inputs['heures_sup'] ?? null;
        if ($heuresSup === null && !empty($inputs['debut']) && !empty($inputs['fin'])) {
            $heuresSup = \App\Models\Pointage::totalHeuresSup(
                $employee->id, $inputs['debut'], $inputs['fin']
            );
        }

        $this->variables = [
            'salaire_base'     => (float) ($inputs['salaire_base'] ?? $employee->salaire_base ?? 0),
            'heures_sup'       => (float) ($heuresSup ?? 0),
            'primes'           => (float) ($inputs['primes'] ?? 0),
            'indemnites'       => (float) ($inputs['indemnites'] ?? 0),
            'avances'          => (float) ($inputs['avances'] ?? 0),
            'retenues_manuel'  => (float) ($inputs['retenues_manuelles'] ?? 0),
            'jours_travailles' => (float) ($inputs['jours_travailles'] ?? 30),
        ];

        $this->lignesGains = [];
        $this->lignesRetenues = [];
        $this->lignesCotSalariales = [];
        $this->lignesCotPatronales = [];

        $rubriques = Rubrique::where('statut', 1)
            ->orderBy('type')
            ->orderBy('ordre_affichage')
            ->get();

        // 1) GAINS
        foreach ($rubriques->where('type', 'gain') as $rubrique) {
            $montant = $this->resoudreMontant($rubrique);
            if ($montant != 0) {
                $this->lignesGains[] = $this->formerLigne($rubrique, $montant);
            }
        }

        $brut = $this->variables['salaire_base']
              + $this->variables['primes']
              + $this->variables['indemnites']
              + $this->variables['heures_sup']
              + array_sum(array_column($this->lignesGains, 'montant'));
        $this->variables['brut'] = round($brut, 2);

        // 2) COTISATIONS (salariales + patronales)
        foreach ($rubriques->where('type', 'cotisation') as $rubrique) {
            $montant = $this->resoudreMontant($rubrique);
            if ($montant != 0) {
                $ligne = $this->formerLigne($rubrique, $montant);
                if (str_starts_with($rubrique->code, 'PAT_')) {
                    $this->lignesCotPatronales[] = $ligne;
                } else {
                    $this->lignesCotSalariales[] = $ligne;
                }
            }
        }
        $totalCotSal = array_sum(array_column($this->lignesCotSalariales, 'montant'));
        $totalCotPat = array_sum(array_column($this->lignesCotPatronales, 'montant'));

        $netImposable = $brut - $totalCotSal;
        $this->variables['net_imposable'] = round($netImposable, 2);

        // 3) IRPP et autres RETENUES
        $totalIRPP = 0;
        foreach ($rubriques->where('type', 'retenue') as $rubrique) {
            $montant = $this->resoudreMontant($rubrique);
            if ($montant != 0) {
                $ligne = $this->formerLigne($rubrique, $montant);
                $this->lignesRetenues[] = $ligne;
                if (str_contains(strtoupper($rubrique->code), 'IRPP')
                    || str_contains(strtoupper($rubrique->libelle), 'IRPP')) {
                    $totalIRPP += $montant;
                }
            }
        }
        $totalAutresRetenues = array_sum(array_column($this->lignesRetenues, 'montant')) - $totalIRPP;

        // 4) Net à payer
        $netAPayer = $netImposable - $totalIRPP - $totalAutresRetenues
                   - $this->variables['avances']
                   - $this->variables['retenues_manuel'];

        // Cumuls annuels (somme des bulletins de la même année validés/payés pour cet employé)
        $annee = \Carbon\Carbon::parse($inputs['debut'])->year;
        $cumuls = \App\Models\Paie::where('employee_id', $employee->id)
            ->whereYear('debut', $annee)
            ->whereIn('statut', [1, 2])
            ->selectRaw('
                COALESCE(SUM(brut),0) as c_brut,
                COALESCE(SUM(net_imposable),0) as c_net_imp,
                COALESCE(SUM(cotisations_salariales),0) as c_cot_sal,
                COALESCE(SUM(cotisations_patronales),0) as c_cot_pat,
                COALESCE(SUM(irpp),0) as c_irpp,
                COALESCE(SUM(net_a_payer),0) as c_net_payer
            ')->first();

        $numero = $this->genererNumeroBulletin($employee, $inputs['debut']);

        return [
            'bulletin' => [
                'employee_id'                  => $employee->id,
                'label'                        => $inputs['label'] ?? ("Paie " . ($inputs['debut'] ?? '')),
                'numero_bulletin'              => $numero,
                'debut'                        => $inputs['debut'],
                'fin'                          => $inputs['fin'],
                'salaire_base'                 => $this->variables['salaire_base'],
                'primes'                       => $this->variables['primes'],
                'indemnites'                   => $this->variables['indemnites'],
                'heures_sup'                   => $this->variables['heures_sup'],
                'brut'                         => round($brut, 2),
                'cotisations_salariales'       => round($totalCotSal, 2),
                'cotisations_patronales'       => round($totalCotPat, 2),
                'irpp'                         => round($totalIRPP, 2),
                'net_imposable'                => round($netImposable, 2),
                'avances'                      => $this->variables['avances'],
                'retenues'                     => round($totalAutresRetenues + $this->variables['retenues_manuel'], 2),
                'net_a_payer'                  => round($netAPayer, 2),
                'statut'                       => 0,
                // Champs ANPI
                'part_impots'                  => (float) ($employee->parts_fiscales ?? 1),
                'mode_reglement'               => $inputs['mode_reglement'] ?? 'virement',
                'compte_bancaire'              => $inputs['compte_bancaire'] ?? $employee->iban,
                'cumul_annuel_brut'            => round($cumuls->c_brut + $brut, 2),
                'cumul_annuel_net_imposable'   => round($cumuls->c_net_imp + $netImposable, 2),
                'cumul_annuel_cotisations_sal' => round($cumuls->c_cot_sal + $totalCotSal, 2),
                'cumul_annuel_cotisations_pat' => round($cumuls->c_cot_pat + $totalCotPat, 2),
                'cumul_annuel_irpp'            => round($cumuls->c_irpp + $totalIRPP, 2),
                'cumul_annuel_net_a_payer'     => round($cumuls->c_net_payer + $netAPayer, 2),
                'brut_conges_mois'             => round($brut, 2),
                'brut_conges_cumul'            => round($cumuls->c_brut + $brut, 2),
                'nb_jrs_acquis_mois'           => 2.0,  // Convention Gabon : 2 jours/mois
                'nb_jrs_acquis_annee'          => 24.0,
                'reste_a_prendre'              => 0.0,
                'acquis_n_moins_1'             => 0.0,
                // Snapshot identité pour réimpression
                'snapshot_employe'             => [
                    'noms'             => $employee->noms,
                    'prenoms'          => $employee->prenoms,
                    'matricule'        => $employee->matricule,
                    'poste'            => $employee->poste,
                    'matricule_cnss'   => $employee->matricule_cnss,
                    'matricule_cnamgs' => $employee->matricule_cnamgs,
                    'iban'             => $employee->iban,
                    'date_embauche'    => $employee->date_embauche?->toDateString(),
                    'type_contrat'     => $employee->type_contrat,
                    'parts_fiscales'   => (float) ($employee->parts_fiscales ?? 1),
                ],
            ],
            'lignes' => array_merge(
                $this->lignesGains,
                $this->lignesCotSalariales,
                $this->lignesCotPatronales,
                $this->lignesRetenues,
            ),
        ];
    }

    /**
     * Numéro de bulletin auto : B-YYYY-MM-{matricule}[-N].
     * Si un bulletin existe déjà avec ce numéro (ex. simulation puis réel, ou re-génération),
     * un suffixe -2, -3, … est appliqué pour respecter la contrainte d'unicité.
     */
    private function genererNumeroBulletin(Employee $emp, string $debut): string
    {
        $d = \Carbon\Carbon::parse($debut);
        $suffix = $emp->matricule ?: ('E' . $emp->id);
        $base = sprintf('B-%04d-%02d-%s', $d->year, $d->month, $suffix);

        if (!Paie::where('numero_bulletin', $base)->exists()) {
            return $base;
        }
        for ($i = 2; $i < 1000; $i++) {
            $candidat = $base . '-' . $i;
            if (!Paie::where('numero_bulletin', $candidat)->exists()) {
                return $candidat;
            }
        }
        throw new \RuntimeException("Impossible de générer un numéro de bulletin unique pour {$base}");
    }

    public function persister(array $resultat): Paie
    {
        return DB::transaction(function () use ($resultat) {
            $paie = Paie::create($resultat['bulletin']);
            foreach ($resultat['lignes'] as $ligne) {
                $paie->rubriques()->attach($ligne['rubrique_id'], [
                    'base'      => $ligne['base'],
                    'taux'      => $ligne['taux'],
                    'montant'   => $ligne['montant'],
                    'imposable' => $ligne['imposable'],
                    'cotisable' => $ligne['cotisable'],
                ]);
            }
            return $paie;
        });
    }

    private function resoudreMontant(Rubrique $r): float
    {
        $base = $this->resoudreBase($r);
        return match ($r->base_calcul) {
            'fixe'        => (float) ($r->montant_fixe ?? 0),
            'pourcentage' => round($base * (float) $r->taux / 100, 2),
            'formule'     => $r->formule ? $this->resoudreFormule($r->formule) : 0.0,
            default       => 0.0,
        };
    }

    private function resoudreBase(Rubrique $r): float
    {
        if (in_array($r->type, ['cotisation', 'retenue'])) {
            $base = $this->variables['brut'] ?? $this->variables['salaire_base'];
            // Plafond : si la rubrique a un valeur2 > 0, on plafonne la base.
            // Convention ANPI : valeur2 stocke le plafond mensuel (ex : 1.500.000 CNSS, 2.500.000 CNAMGS).
            $plafond = (float) ($r->valeur2 ?? 0);
            if ($plafond > 0 && $base > $plafond) {
                return $plafond;
            }
            return $base;
        }
        return $this->variables['salaire_base'];
    }

    private function resoudreFormule(string $formule): float
    {
        // Remplace les variables nommées par leurs valeurs avant tokenisation
        $expr = $formule;
        foreach ($this->variables as $name => $value) {
            $expr = preg_replace('/\b' . preg_quote($name, '/') . '\b/', (string) $value, $expr);
        }
        return $this->expr->compute($expr);
    }

    private function formerLigne(Rubrique $r, float $montant): array
    {
        return [
            'rubrique_id' => $r->id,
            'rubrique'    => $r->libelle,
            'code'        => $r->code,
            'type'        => $r->type,
            'base'        => $this->resoudreBase($r),
            'taux'        => (float) ($r->taux ?? 0),
            'montant'     => $montant,
            'imposable'   => (bool) $r->imposable,
            'cotisable'   => (bool) $r->cotisable,
        ];
    }
}
