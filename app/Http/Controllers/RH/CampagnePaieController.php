<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\CampagnePaie;
use App\Models\EchantillonPaie;
use App\Models\Employee;
use App\Models\Paie;
use App\Services\Rh\CfonbExporter;
use App\Services\Rh\PaieCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampagnePaieController extends Controller
{
    public function index(Request $request)
    {
        $campagnes = CampagnePaie::query()
            ->with(['createur', 'validateur'])
            ->when($request->annee, fn($q, $a) => $q->where('annee', $a))
            ->when($request->statut !== null && $request->statut !== '', fn($q) => $q->where('statut', $request->statut))
            ->when($request->simulation !== null && $request->simulation !== '', fn($q) => $q->where('simulation', (bool)$request->simulation))
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->paginate(20)
            ->withQueryString();

        return view('rh.campagnes-paie.index', compact('campagnes'));
    }

    public function create(Request $request)
    {
        $employees = Employee::where('statut', 1)->orderBy('noms')->get();
        $echantillons = EchantillonPaie::actif()->withCount('employes')->orderBy('libelle')->get();
        // Pré-sélection si on arrive depuis la fiche d'un échantillon
        $echantillonId = $request->integer('echantillon') ?: null;
        return view('rh.campagnes-paie.create', compact('employees', 'echantillons', 'echantillonId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'libelle'              => 'required|string|max:255',
            'annee'                => 'required|integer|min:2020|max:2100',
            'mois'                 => 'required|integer|min:1|max:12',
            'date_paiement_prevue' => 'nullable|date',
            'periodicite'          => 'required|in:mensuelle,quinzaine,hebdomadaire,exceptionnelle',
            'simulation'           => 'nullable|boolean',
            'commentaire'          => 'nullable|string',
            'mode_selection'       => 'required|in:tous_actifs,par_departement,par_echantillon,manuel',
            'departements'         => 'nullable|array',
            'departements.*'       => 'string',
            'echantillon_id'       => 'required_if:mode_selection,par_echantillon|nullable|exists:echantillons_paie,id',
            'employee_ids'         => 'nullable|array',
            'employee_ids.*'       => 'integer|exists:employees,id',
        ], [
            'echantillon_id.required_if' => 'Sélectionnez un échantillon.',
        ]);

        $debut = Carbon::create($data['annee'], $data['mois'], 1)->startOfMonth();
        $fin   = (clone $debut)->endOfMonth();
        $simulation = (bool) ($data['simulation'] ?? false);

        $code = sprintf('%s-%04d-%02d%s',
            $simulation ? 'SIM' : 'CP',
            $data['annee'], $data['mois'],
            $data['periodicite'] === 'mensuelle' ? '' : '-' . strtoupper(substr($data['periodicite'], 0, 3))
        );

        DB::transaction(function () use ($data, $debut, $fin, $simulation, $code) {
            $campagne = CampagnePaie::create([
                'code'                 => $code,
                'libelle'              => $data['libelle'],
                'annee'                => $data['annee'],
                'mois'                 => $data['mois'],
                'date_debut'           => $debut,
                'date_fin'             => $fin,
                'date_paiement_prevue' => $data['date_paiement_prevue'] ?? null,
                'periodicite'          => $data['periodicite'],
                'simulation'           => $simulation,
                'commentaire'          => $data['commentaire'] ?? null,
                'created_by'           => auth()->id(),
                'statut'               => 0,
            ]);

            // Sélection des employés
            $employes = match ($data['mode_selection']) {
                'tous_actifs'      => Employee::where('statut', 1)->pluck('id')->all(),
                'par_departement'  => Employee::where('statut', 1)
                                        ->whereIn('departement', $data['departements'] ?? [])
                                        ->pluck('id')->all(),
                'par_echantillon'  => EchantillonPaie::find($data['echantillon_id'])
                                        ?->employes()->where('employees.statut', 1)->pluck('employees.id')->all() ?? [],
                'manuel'           => $data['employee_ids'] ?? [],
            };

            if (!empty($employes)) {
                $campagne->employes()->attach($employes);
            }
        });

        return redirect()->route('rh.campagnes-paie.index')
            ->with('success', "Campagne $code créée avec " . count($employes ?? []) . " employé(s).");
    }

    public function show(CampagnePaie $campagnes_paie)
    {
        $campagnes_paie->load(['createur', 'validateur', 'cloturePar', 'employes', 'bulletins.employee']);
        return view('rh.campagnes-paie.show', ['campagne' => $campagnes_paie]);
    }

    /**
     * Édition partielle d'une campagne en brouillon.
     * Seuls les champs descriptifs (libellé, commentaire, date paiement prévue) sont modifiables.
     * Les champs structurants (année, mois, périodicité, simulation) ne le sont PAS : créer une nouvelle campagne.
     */
    public function edit(CampagnePaie $campagnes_paie)
    {
        abort_if(!$campagnes_paie->est_modifiable, 403,
            'Seules les campagnes en brouillon ou générées sont éditables.');
        return view('rh.campagnes-paie.edit', ['campagne' => $campagnes_paie]);
    }

    public function update(Request $request, CampagnePaie $campagnes_paie)
    {
        abort_if(!$campagnes_paie->est_modifiable, 403,
            'Cette campagne n\'est plus modifiable.');

        $data = $request->validate([
            'libelle'              => 'required|string|max:255',
            'date_paiement_prevue' => 'nullable|date',
            'commentaire'          => 'nullable|string|max:2000',
        ]);

        $campagnes_paie->update($data);
        return redirect()->route('rh.campagnes-paie.show', $campagnes_paie)
            ->with('success', 'Campagne mise à jour.');
    }

    /**
     * Génère les bulletins pour tous les employés sélectionnés.
     * Idempotent : un employé déjà ayant un bulletin sur cette campagne est ignoré.
     */
    public function generer(CampagnePaie $campagnes_paie, PaieCalculator $calc)
    {
        if (!in_array($campagnes_paie->statut, [0, 1])) {
            return back()->with('error', 'La campagne n\'est plus modifiable.');
        }

        $nbCrees = 0;
        $nbIgnores = 0;
        $errors = [];

        DB::transaction(function () use ($campagnes_paie, $calc, &$nbCrees, &$nbIgnores, &$errors) {
            $existingEmpIds = Paie::where('campagne_paie_id', $campagnes_paie->id)
                ->pluck('employee_id')->all();

            foreach ($campagnes_paie->employes as $emp) {
                if (in_array($emp->id, $existingEmpIds)) {
                    $nbIgnores++;
                    continue;
                }
                if ($emp->pivot->statut == 2) continue; // exclu manuellement

                try {
                    $inputs = [
                        'employee_id'         => $emp->id,
                        'debut'               => $campagnes_paie->date_debut->toDateString(),
                        'fin'                 => $campagnes_paie->date_fin->toDateString(),
                        'salaire_base'        => $emp->salaire_base,
                        'primes'              => (float) ($emp->pivot->primes_override ?? 0),
                        'indemnites'          => (float) ($emp->pivot->indemnites_override ?? 0),
                        'heures_sup'          => (float) ($emp->pivot->heures_sup_override ?? 0),
                        'avances'             => (float) ($emp->pivot->avances_override ?? 0),
                        'retenues_manuelles'  => (float) ($emp->pivot->retenues_override ?? 0),
                    ];
                    $resultat = $calc->calculer($inputs);
                    // Le numéro de bulletin du calculator est OK ; on rattache à la campagne
                    $resultat['bulletin']['campagne_paie_id'] = $campagnes_paie->id;
                    $resultat['bulletin']['label'] = sprintf('%s — %s %s',
                        $campagnes_paie->libelle, $emp->noms, $emp->prenoms);

                    $calc->persister($resultat);
                    $emp->pivot->statut = 1;
                    $emp->pivot->save();
                    $nbCrees++;
                } catch (\Throwable $e) {
                    $errors[] = "{$emp->noms} {$emp->prenoms} : " . $e->getMessage();
                }
            }

            // Mise à jour des KPI campagne
            $stats = Paie::where('campagne_paie_id', $campagnes_paie->id)
                ->selectRaw('COUNT(*) as nb, COALESCE(SUM(brut),0) as brut,
                             COALESCE(SUM(net_a_payer),0) as net,
                             COALESCE(SUM(cotisations_salariales),0) as cot_sal,
                             COALESCE(SUM(cotisations_patronales),0) as cot_pat')
                ->first();

            $campagnes_paie->update([
                'statut'                => 1,
                'nombre_bulletins'      => $stats->nb,
                'masse_brute'           => $stats->brut,
                'masse_nette'           => $stats->net,
                'total_cotisations_sal' => $stats->cot_sal,
                'total_cotisations_pat' => $stats->cot_pat,
            ]);
        });

        $msg = "$nbCrees bulletin(s) généré(s)";
        if ($nbIgnores) $msg .= ", $nbIgnores ignoré(s) (déjà générés)";
        if ($errors) $msg .= ". Erreurs : " . implode(' | ', array_slice($errors, 0, 3));

        return redirect()->route('rh.campagnes-paie.show', $campagnes_paie)->with(count($errors) > 0 ? 'error' : 'success', $msg);
    }

    /**
     * Valide la campagne : passe tous les bulletins en statut=1 (validé).
     */
    public function valider(CampagnePaie $campagnes_paie, \App\Services\Finance\PaieEcritureService $ecritureService)
    {
        if ($campagnes_paie->statut !== 1) {
            return back()->with('error', 'La campagne doit être en statut "Générée" pour être validée.');
        }
        $simulation = (bool) $campagnes_paie->simulation;

        $msg = '';
        DB::transaction(function () use ($campagnes_paie) {
            Paie::where('campagne_paie_id', $campagnes_paie->id)
                ->where('statut', 0)
                ->update(['statut' => 1]);
            $campagnes_paie->update([
                'statut'      => 2,
                'validee_par' => auth()->id(),
                'validee_at'  => now(),
            ]);
        });

        // Génération automatique des écritures comptables OHADA — uniquement pour les campagnes réelles.
        // Les simulations ne touchent pas la compta.
        if (!$simulation) {
            try {
                $resultat = $ecritureService->genererPourCampagne($campagnes_paie, auth()->id());
                $msg = ' ' . ($resultat['message'] ?? '');
                if (!($resultat['equilibre'] ?? true)) {
                    $msg .= ' ⚠️ Écart d\'équilibre comptable de ' . number_format($resultat['delta'], 2, ',', ' ') . ' XAF.';
                }
            } catch (\Throwable $e) {
                // On ne bloque PAS la validation paie si la compta plante (ex : pas d'exercice en cours).
                // L'admin Finance pourra régénérer manuellement plus tard.
                \Illuminate\Support\Facades\Log::error('Échec génération écritures paie', [
                    'campagne_id' => $campagnes_paie->id,
                    'error'       => $e->getMessage(),
                ]);
                $msg = ' ⚠️ Écritures comptables non générées : ' . $e->getMessage();
            }
        }

        return back()->with('success', 'Campagne validée. ' . $campagnes_paie->nombre_bulletins . ' bulletin(s) prêt(s) au paiement.' . $msg);
    }

    /**
     * Clôture la campagne (verrouillage).
     */
    public function cloturer(CampagnePaie $campagnes_paie)
    {
        if (!$campagnes_paie->est_cloturable) {
            return back()->with('error', 'La campagne doit être payée pour être clôturée.');
        }
        $campagnes_paie->update([
            'statut'       => 4,
            'cloturee_par' => auth()->id(),
            'cloturee_at'  => now(),
        ]);
        return back()->with('success', 'Campagne clôturée et verrouillée.');
    }

    /**
     * Génère l'ordre de virement (CSV bancaire universel) pour la campagne.
     * Format : Bénéficiaire;IBAN;Banque;Montant;Référence;Mode
     */
    public function ovCsv(CampagnePaie $campagnes_paie): StreamedResponse
    {
        $bulletins = $this->bulletinsPourVirement($campagnes_paie);
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', 'OV_' . $campagnes_paie->code) . '.csv';

        return response()->streamDownload(function () use ($campagnes_paie, $bulletins) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8

            // En-tête méta (commentée par # pour faciliter import)
            fputcsv($out, ['# Campagne', $campagnes_paie->code, $campagnes_paie->libelle], ';');
            fputcsv($out, ['# Période', $campagnes_paie->date_debut->format('d/m/Y'), $campagnes_paie->date_fin->format('d/m/Y')], ';');
            fputcsv($out, ['# Date paiement prévue', $campagnes_paie->date_paiement_prevue?->format('d/m/Y') ?? '—'], ';');
            fputcsv($out, [], ';');
            // Header
            fputcsv($out, [
                'Bénéficiaire', 'IBAN', 'Banque',
                'Montant', 'Devise', 'Référence', 'Mode',
                'Matricule', 'CNSS',
            ], ';');

            $total = 0;
            foreach ($bulletins as $b) {
                $emp = $b->employee;
                $beneficiaire = trim(($emp->noms ?? '') . ' ' . ($emp->prenoms ?? ''));
                $montant = (float) $b->net_a_payer - (float) $b->payements()->where('statut', 1)->sum('montant');
                if ($montant <= 0) continue;
                fputcsv($out, [
                    $beneficiaire,
                    $b->compte_bancaire ?? $emp->iban ?? '',
                    $emp->banque ?? '',
                    number_format($montant, 2, '.', ''),
                    'XAF',
                    $b->numero_bulletin ?? ('PAIE-' . $b->id),
                    $b->mode_reglement ?? 'virement',
                    $emp->matricule ?? '',
                    $emp->matricule_cnss ?? '',
                ], ';');
                $total += $montant;
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['# TOTAL', number_format($total, 2, '.', ''), 'XAF'], ';');
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * PDF récapitulatif OV avec totaux et zones de signature.
     */
    public function ovPdf(CampagnePaie $campagnes_paie)
    {
        $bulletins = $this->bulletinsPourVirement($campagnes_paie);
        $lignes = $bulletins->map(function ($b) {
            $reste = (float) $b->net_a_payer - (float) $b->payements()->where('statut', 1)->sum('montant');
            return [
                'beneficiaire' => trim(($b->employee->noms ?? '') . ' ' . ($b->employee->prenoms ?? '')),
                'matricule'    => $b->employee->matricule ?? '—',
                'iban'         => $b->compte_bancaire ?? $b->employee->iban ?? '—',
                'banque'       => $b->employee->banque ?? '—',
                'mode'         => $b->mode_reglement ?? 'virement',
                'reference'    => $b->numero_bulletin ?? ('PAIE-' . $b->id),
                'montant'      => round($reste, 2),
            ];
        })->filter(fn($l) => $l['montant'] > 0)->values();

        $total = $lignes->sum('montant');
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', 'OV_' . $campagnes_paie->code) . '.pdf';

        $pdf = Pdf::loadView('rh.campagnes-paie.ov-pdf', [
            'campagne' => $campagnes_paie,
            'lignes'   => $lignes,
            'total'    => $total,
        ])->setPaper('A4', 'portrait')->setOptions(['defaultFont' => 'DejaVu Sans']);

        return $pdf->stream($filename);
    }

    private function bulletinsPourVirement(CampagnePaie $campagne)
    {
        return Paie::with('employee')
            ->where('campagne_paie_id', $campagne->id)
            ->whereIn('statut', [1, 2])
            ->orderBy('employee_id')
            ->get();
    }

    /**
     * Export CFONB-160 (norme bancaire française AFB).
     * Le fichier généré est un .txt en lignes ASCII fixes de 160 caractères,
     * directement importable par la plupart des plateformes bancaires françaises
     * et certaines banques africaines (BICIG, Ecobank, UGB via passerelle CFONB).
     */
    public function ovCfonb(CampagnePaie $campagnes_paie, CfonbExporter $exporter)
    {
        // Métadonnées émetteur — à adapter par organisation (idéalement via app_config)
        $emetteur = [
            'raison_sociale' => 'YUBILE TECHNOLOGIE',
            'num_emetteur'   => '000001',
            'rib'            => '0000000000000000000000', // RIB donneur d'ordre — placeholder
        ];

        $contenu = $exporter->exporter($campagnes_paie, $emetteur);
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', 'OV_CFONB_' . $campagnes_paie->code) . '.txt';

        return response($contenu, 200, [
            'Content-Type'        => 'text/plain; charset=ASCII',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function destroy(CampagnePaie $campagnes_paie)
    {
        if ($campagnes_paie->statut >= 2) {
            return back()->with('error', 'Une campagne validée ne peut pas être supprimée.');
        }
        // Suppression cascade des bulletins liés (les paiements sont protégés par leur propre logique)
        DB::transaction(function () use ($campagnes_paie) {
            Paie::where('campagne_paie_id', $campagnes_paie->id)->delete();
            $campagnes_paie->delete();
        });
        return redirect()->route('rh.campagnes-paie.index')->with('success', 'Campagne supprimée.');
    }
}
