<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use App\Models\Exercice;
use App\Models\GrandLivre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrandLivreController extends Controller
{
    public function index(Request $request)
    {
        $ecritures = GrandLivre::query()
            ->with(['entite', 'compte', 'user'])
            ->when($request->exercice_id, fn($q, $id) => $q->where('id_exercicebudgetaire', $id))
            ->when($request->compte_id, fn($q, $id) => $q->where('compte_id', $id))
            ->when($request->ref_piece, fn($q, $ref) => $q->where('ref_piece', $ref))
            ->when($request->journal, fn($q, $j) => $q->where('journal', $j))
            ->when($request->date_debut, fn($q, $d) => $q->where('date_ecriture', '>=', $d))
            ->when($request->date_fin, fn($q, $d) => $q->where('date_ecriture', '<=', $d))
            ->when($request->search, fn($q, $s) => $q->where('libelle', 'like', "%{$s}%"))
            ->orderByDesc('date_ecriture')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Pour les filtres
        $exercices = Exercice::orderByDesc('id')->get();
        $comptes   = Compte::orderBy('id')->get();

        return view('finance.grand-livre.index', compact('ecritures', 'exercices', 'comptes'));
    }

    public function show(string $id)
    {
        $ecriture = GrandLivre::with(['details', 'exerciceBudgetaire', 'entite', 'compte', 'user'])->findOrFail($id);

        return view('finance.grand-livre.show', compact('ecriture'));
    }

    /**
     * Le grand-livre est en LECTURE SEULE pour la saisie unitaire.
     * Les écritures se génèrent via les ordres (§4.7 du guide) OU en bulk via import CSV.
     */
    public function create()  { abort(403, 'Saisie unitaire interdite — utilisez les ordres ou l\'import CSV.'); }
    public function store()   { abort(403, 'Saisie unitaire interdite — utilisez les ordres ou l\'import CSV.'); }
    public function edit()    { abort(403, 'Modification interdite — traçabilité comptable préservée.'); }
    public function update()  { abort(403, 'Modification interdite — traçabilité comptable préservée.'); }
    public function destroy() { abort(403, 'Suppression interdite — passez une écriture de sens inverse via les ordres.'); }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS CSV
    // ═══════════════════════════════════════════════════════════════

    /**
     * Neutralise les injections de formule CSV : préfixe d'une apostrophe toute cellule
     * commençant par un caractère interprété comme formule par Excel/Sheets/LibreOffice
     * (=, +, -, @, TAB, CR).
     */
    private static function stripFormulaInjection($value): mixed
    {
        if (!is_string($value) || $value === '') return $value;
        $first = $value[0];
        if (in_array($first, ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }
        return $value;
    }

    /** Applique stripFormulaInjection à toutes les valeurs string d'un tableau. */
    private static function safeCsvRow(array $row): array
    {
        return array_map([self::class, 'stripFormulaInjection'], $row);
    }

    // ═══════════════════════════════════════════════════════════════
    // EXPORT — dispatcher multi-format (csv | xlsx | pdf | docx)
    // ═══════════════════════════════════════════════════════════════
    private function buildExportQuery(Request $request)
    {
        return GrandLivre::query()
            ->with(['compte:id,label,nom,code'])
            ->when($request->exercice_id, fn($q, $id) => $q->where('id_exercicebudgetaire', $id))
            ->when($request->compte_id,   fn($q, $id) => $q->where('compte_id', $id))
            ->when($request->sens,        fn($q, $s)  => $q->where('sens', $s))
            ->when($request->date_debut,  fn($q, $d)  => $q->whereDate('date_ecriture', '>=', $d))
            ->when($request->date_fin,    fn($q, $d)  => $q->whereDate('date_ecriture', '<=', $d))
            ->orderBy('date_ecriture')->orderBy('id');
    }

    public function export(Request $request)
    {
        $format = strtolower((string) $request->query('format', 'csv'));
        return match ($format) {
            'xlsx'  => $this->exportXlsx($request),
            'pdf'   => redirect()->route('finance.exports.grand-livre-pdf', $request->query()),
            'docx'  => $this->exportDocx($request),
            default => $this->exportCsv($request), // csv
        };
    }

    private function exportCsv(Request $request): StreamedResponse
    {
        $q = $this->buildExportQuery($request);
        $filename = 'grand-livre-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($q) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($out, [
                'date_ecriture', 'libelle', 'sens', 'montant_tc',
                'compte_id', 'compte_code', 'compte_libelle',
                'id_exercicebudgetaire', 'description',
                'num_piece', 'journal', 'devise', 'beneficiaire', 'isvalide',
            ], ';');
            $q->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $e) {
                    fputcsv($out, self::safeCsvRow([
                        optional($e->date_ecriture)->format('Y-m-d'),
                        $e->libelle,
                        $e->sens,
                        $e->montant_tc,
                        $e->compte_id,
                        $e->compte?->code,
                        ($e->compte?->label ?? $e->compte?->nom),
                        $e->id_exercicebudgetaire,
                        $e->description,
                        $e->num_piece,
                        $e->journal,
                        $e->devise,
                        $e->beneficiaire,
                        $e->isvalide,
                    ]), ';');
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function exportXlsx(Request $request): StreamedResponse
    {
        $q = $this->buildExportQuery($request);
        $filename = 'grand-livre-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($q) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Grand-livre');

            // En-têtes
            $headers = ['Date', 'Libellé', 'Sens', 'Montant', 'Compte ID', 'Compte code', 'Compte libellé', 'Exercice ID', 'Description', 'N° pièce', 'Journal', 'Devise', 'Bénéficiaire', 'Validée'];
            $sheet->fromArray($headers, null, 'A1');
            $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true);
            $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E0E7FF');
            $sheet->freezePane('A2');

            $row = 2;
            $q->chunk(500, function ($rows) use ($sheet, &$row) {
                foreach ($rows as $e) {
                    $sheet->fromArray([
                        optional($e->date_ecriture)->format('Y-m-d'),
                        self::stripFormulaInjection($e->libelle),
                        $e->sens,
                        (float) $e->montant_tc,
                        $e->compte_id,
                        self::stripFormulaInjection($e->compte?->code),
                        self::stripFormulaInjection(($e->compte?->label ?? $e->compte?->nom)),
                        $e->id_exercicebudgetaire,
                        self::stripFormulaInjection($e->description),
                        self::stripFormulaInjection($e->num_piece),
                        self::stripFormulaInjection($e->journal),
                        self::stripFormulaInjection($e->devise),
                        self::stripFormulaInjection($e->beneficiaire),
                        $e->isvalide,
                    ], null, "A{$row}");
                    $row++;
                }
            });

            // Largeurs auto
            foreach (range('A', 'N') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
            // Format montant
            $sheet->getStyle('D2:D'.($row - 1))->getNumberFormat()->setFormatCode('#,##0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function exportDocx(Request $request): StreamedResponse
    {
        $q = $this->buildExportQuery($request);
        $filename = 'grand-livre-'.now()->format('Ymd-His').'.docx';

        return response()->streamDownload(function () use ($q) {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection(['orientation' => 'landscape']);

            $section->addTitle('Grand-livre — export du '.now()->format('d/m/Y H:i'), 1);
            $section->addTextBreak(1);

            $tableStyle = ['borderSize' => 6, 'borderColor' => 'CCCCCC', 'cellMargin' => 60];
            $phpWord->addTableStyle('gl', $tableStyle);
            $table = $section->addTable('gl');

            // Header row
            $table->addRow();
            foreach (['Date', 'Libellé', 'Sens', 'Montant', 'Compte', 'N° pièce', 'Journal'] as $h) {
                $cell = $table->addCell(null, ['bgColor' => 'E0E7FF']);
                $cell->addText($h, ['bold' => true, 'size' => 9]);
            }

            $q->chunk(500, function ($rows) use ($table) {
                foreach ($rows as $e) {
                    $table->addRow();
                    $table->addCell()->addText(optional($e->date_ecriture)->format('d/m/Y'), ['size' => 9]);
                    $table->addCell()->addText(self::stripFormulaInjection($e->libelle ?? ''), ['size' => 9]);
                    $table->addCell()->addText($e->sens, ['size' => 9, 'color' => $e->sens === 'debit' ? 'DC2626' : '059669']);
                    $table->addCell()->addText(number_format((float) $e->montant_tc, 0, ',', ' '), ['size' => 9, 'bold' => true]);
                    $table->addCell()->addText(self::stripFormulaInjection(($e->compte?->label ?? $e->compte?->nom) ?? '—'), ['size' => 9]);
                    $table->addCell()->addText(self::stripFormulaInjection($e->num_piece ?? '—'), ['size' => 9]);
                    $table->addCell()->addText(self::stripFormulaInjection($e->journal ?? '—'), ['size' => 9]);
                }
            });

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    // ═══════════════════════════════════════════════════════════════
    // IMPORT — CSV upload (bulk, super-admin/admin uniquement)
    // ═══════════════════════════════════════════════════════════════
    public function importForm()
    {
        $exercices = Exercice::orderByDesc('id')->get(['id', 'libelle', 'exercice']);
        return view('finance.grand-livre.import', compact('exercices'));
    }

    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'date_ecriture', 'libelle', 'sens', 'montant_tc',
                'compte_code', 'id_exercicebudgetaire',
                'description', 'num_piece', 'journal', 'devise', 'beneficiaire',
            ], ';');
            // 2 lignes exemples (sanitisées anti-injection)
            fputcsv($out, self::safeCsvRow(['2026-01-15', 'Paiement facture 12345', 'debit', '150000', 'C-4011', '1', 'Fournisseur ACME', 'FAC-12345', 'ACHATS', 'XAF', 'ACME SARL']), ';');
            fputcsv($out, self::safeCsvRow(['2026-01-16', 'Encaissement client', 'credit', '250000', 'C-5121', '1', 'Client SOTRAF', 'BANK-889', 'BANQUE', 'XAF', 'SOTRAF']), ';');
            fclose($out);
        }, 'grand-livre-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'fichier' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120'], // 5 Mo max
            'ignore_erreurs' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('fichier');
        $ext  = strtolower($file->getClientOriginalExtension());

        // Parse selon extension : XLSX via PhpSpreadsheet, CSV via fgetcsv natif
        $rows = $ext === 'xlsx'
            ? $this->parseXlsx($file->getRealPath())
            : $this->parseCsv($file->getRealPath());

        if ($rows === null) {
            return back()->with('error', 'Impossible de lire le fichier ou en-têtes invalides.');
        }

        $header = array_shift($rows);
        $header = array_map(fn($h) => strtolower(trim((string) $h)), $header);

        $required = ['date_ecriture', 'libelle', 'sens', 'montant_tc'];
        $missing = array_diff($required, $header);
        if (!empty($missing)) {
            return back()->with('error', 'Colonnes manquantes : '.implode(', ', $missing));
        }

        $ignore    = (bool) $request->input('ignore_erreurs');
        $userId    = $request->user()->id;
        $insertions = [];
        $erreurs   = [];
        $lineNum   = 1; // header = ligne 1

        // ── Caches de validation (FK strictes + périmètre autorisé) ──
        $comptesByCode = Compte::pluck('id', 'code')->toArray();
        $comptesById   = array_flip($comptesByCode); // pour valider un compte_id direct

        // Exercices autorisés à l'import : planifiés (1) ou en exécution (2) uniquement
        // (jamais sur un exercice clôturé/annulé — traçabilité comptable)
        $exercicesAutorises = Exercice::whereIn('statut', [1, 2])
            ->get(['id', 'datedebut', 'datefin'])
            ->keyBy('id');
        $exerciceIdsAutorises = $exercicesAutorises->keys()->all();

        foreach ($rows as $row) {
            $lineNum++;
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) continue; // ligne vide
            $data = array_combine($header, array_pad($row, count($header), null));

            // Sanitisation anti-formula injection sur tous les champs texte
            foreach ($data as $k => $v) {
                if (is_string($v)) $data[$k] = self::stripFormulaInjection($v);
            }

            $errs = [];
            $dateTs = strtotime((string) $data['date_ecriture']);
            if (!$dateTs) $errs[] = 'date invalide';
            if (empty(trim((string) $data['libelle']))) $errs[] = 'libellé vide';
            if (!in_array($data['sens'], ['debit', 'credit'], true)) $errs[] = 'sens doit être debit ou credit';
            if (!is_numeric($data['montant_tc']) || (float) $data['montant_tc'] <= 0) $errs[] = 'montant invalide';

            // Résolution compte AVEC validation stricte contre le référentiel
            $compteId = null;
            if (!empty($data['compte_id']) && is_numeric($data['compte_id'])) {
                $candidat = (int) $data['compte_id'];
                if (isset($comptesById[$candidat])) {
                    $compteId = $candidat;
                } else {
                    $errs[] = "compte_id {$candidat} introuvable dans le référentiel";
                }
            } elseif (!empty($data['compte_code']) && isset($comptesByCode[$data['compte_code']])) {
                $compteId = $comptesByCode[$data['compte_code']];
            } else {
                $errs[] = 'compte introuvable (fournir compte_id OU compte_code valide)';
            }

            // Validation exercice + cohérence date/période
            $exerciceId = null;
            if (!empty($data['id_exercicebudgetaire'])) {
                $exCandidat = (int) $data['id_exercicebudgetaire'];
                if (!in_array($exCandidat, $exerciceIdsAutorises, true)) {
                    $errs[] = "exercice {$exCandidat} inexistant ou clôturé/annulé";
                } else {
                    $exerciceId = $exCandidat;
                    // La date doit tomber dans la période de l'exercice
                    if ($dateTs) {
                        $ex = $exercicesAutorises[$exCandidat];
                        $dateIso = date('Y-m-d', $dateTs);
                        if ($ex->datedebut && $dateIso < $ex->datedebut->format('Y-m-d')) {
                            $errs[] = "date {$dateIso} antérieure au début de l'exercice (".$ex->datedebut->format('Y-m-d').')';
                        }
                        if ($ex->datefin && $dateIso > $ex->datefin->format('Y-m-d')) {
                            $errs[] = "date {$dateIso} postérieure à la fin de l'exercice (".$ex->datefin->format('Y-m-d').')';
                        }
                    }
                }
            }

            if (!empty($errs)) {
                $erreurs[] = ['ligne' => $lineNum, 'motifs' => $errs];
                if (!$ignore) continue;
            }

            $insertions[] = [
                'date_ecriture'         => date('Y-m-d', $dateTs ?: time()),
                'libelle'               => (string) $data['libelle'],
                'sens'                  => $data['sens'],
                'montant_tc'            => (float) $data['montant_tc'],
                'compte_id'             => $compteId,
                'id_exercicebudgetaire' => $exerciceId,
                'description'           => $data['description'] ?? null,
                'num_piece'             => $data['num_piece'] ?? null,
                'journal'               => $data['journal'] ?? null,
                'devise'                => $data['devise'] ?? 'XAF',
                'beneficiaire'          => $data['beneficiaire'] ?? null,
                'isvalide'              => 0, // toujours en brouillon après import — à valider ensuite
                'id_user'               => $userId,
                'created_at'            => now(),
                'updated_at'            => now(),
            ];
        }

        if (!empty($erreurs) && !$ignore) {
            return back()
                ->with('error', count($erreurs).' ligne(s) en erreur — corrigez le fichier ou cochez « Ignorer les erreurs » pour importer uniquement les lignes valides.')
                ->with('import_erreurs', array_slice($erreurs, 0, 20));
        }

        if (empty($insertions)) {
            return back()->with('error', 'Aucune ligne valide à importer.')->with('import_erreurs', array_slice($erreurs, 0, 20));
        }

        DB::transaction(function () use ($insertions) {
            foreach (array_chunk($insertions, 500) as $batch) {
                GrandLivre::insert($batch);
            }
        });

        return redirect()->route('finance.grand-livre.index')
            ->with('success', count($insertions).' écriture(s) importée(s) en brouillon.'.(count($erreurs) > 0 ? ' '.count($erreurs).' ligne(s) ignorée(s).' : ''));
    }

    /** Parse un CSV (séparateur `;`, BOM UTF-8 accepté) en tableau de lignes. */
    private function parseCsv(string $path): ?array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return null;

        // Skip BOM éventuel
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        return $rows ?: null;
    }

    /** Parse un XLSX en tableau de lignes (première feuille). */
    private function parseXlsx(string $path): ?array
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
            $spreadsheet->disconnectWorksheets();
            return $rows ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
