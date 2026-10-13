<?php

namespace App\Http\Controllers\Concerns;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Lecture / écriture de fichiers tableur (CSV `;` ou XLSX) pour les imports / exports.
 */
trait ManipuleTableurs
{
    /**
     * Téléchargement CSV (BOM UTF-8, séparateur `;`) ou XLSX.
     * $parcourir reçoit un callback `fn(array $ligne)` à appeler pour chaque ligne à écrire.
     */
    protected function telechargerTableur(string $format, string $nom, string $titreFeuille, array $entetes, \Closure $parcourir): StreamedResponse
    {
        $nom .= '-'.now()->format('Ymd-His');

        if ($format === 'xlsx') {
            return response()->streamDownload(function () use ($titreFeuille, $entetes, $parcourir) {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle(mb_substr($titreFeuille, 0, 31));

                $derniere = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($entetes));
                $sheet->fromArray($entetes, null, 'A1');
                $sheet->getStyle("A1:{$derniere}1")->getFont()->setBold(true);
                $sheet->getStyle("A1:{$derniere}1")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E0E7FF');
                $sheet->freezePane('A2');

                $row = 2;
                $parcourir(function (array $ligne) use ($sheet, &$row) {
                    // Texte explicite : pas d'interprétation en formule, zéros de tête conservés
                    foreach (array_values($ligne) as $i => $val) {
                        $sheet->setCellValueExplicit([$i + 1, $row], (string) $val,
                            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    }
                    $row++;
                });

                for ($i = 1; $i <= count($entetes); $i++) {
                    $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
                }

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            }, $nom.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }

        return response()->streamDownload(function () use ($entetes, $parcourir) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel
            fputcsv($out, $entetes, ';');
            $parcourir(fn(array $ligne) => fputcsv($out, self::safeCsvRow($ligne), ';'));
            fclose($out);
        }, $nom.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Lit un fichier importé (CSV ou XLSX selon l'extension) en tableau de lignes, en-tête comprise. */
    protected function lireTableur(\Illuminate\Http\UploadedFile $file): ?array
    {
        return strtolower($file->getClientOriginalExtension()) === 'xlsx'
            ? $this->parseXlsx($file->getRealPath())
            : $this->parseCsv($file->getRealPath());
    }

    /** Parse un CSV (séparateur `;` ou `,` auto-détecté, BOM UTF-8 accepté). */
    protected function parseCsv(string $path): ?array
    {
        $handle = fopen($path, 'r');
        if (!$handle) return null;

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        $pos = ftell($handle);
        $premiere = (string) fgets($handle);
        fseek($handle, $pos);
        $sep = substr_count($premiere, ';') >= substr_count($premiere, ',') ? ';' : ',';

        $rows = [];
        while (($row = fgetcsv($handle, 0, $sep)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);
        return $rows ?: null;
    }

    /** Parse un XLSX en tableau de lignes (première feuille). */
    protected function parseXlsx(string $path): ?array
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
            $spreadsheet->disconnectWorksheets();
            return $rows ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Normalise une ligne lue : trim, valeurs non scalaires → null, et retire l'apostrophe
     * ajoutée par safeCsvRow à l'export. Retourne null pour une ligne vide.
     */
    protected static function normaliserLigne(array $header, array $row): ?array
    {
        if (count(array_filter($row, fn($v) => $v !== null && trim((string) $v) !== '')) === 0) return null;
        $data = array_combine($header, array_slice(array_pad($row, count($header), null), 0, count($header)));
        return array_map(function ($v) {
            if (!is_string($v) && !is_numeric($v)) return null;
            $v = trim((string) $v);
            return preg_match("/^'[=+\\-@]/", $v) ? substr($v, 1) : $v;
        }, $data);
    }

    /**
     * Neutralise les injections de formule CSV : préfixe d'une apostrophe toute cellule
     * commençant par un caractère interprété comme formule (=, +, -, @, TAB, CR).
     */
    protected static function safeCsvRow(array $row): array
    {
        return array_map(function ($value) {
            if (!is_string($value) || $value === '') return $value;
            return in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$value : $value;
        }, $row);
    }

    /** Statut texte (actif / inactif / 1 / 0 / oui / non) → int, null si non reconnu. */
    protected static function parseStatut(string $valeur): ?int
    {
        return match (mb_strtolower(trim($valeur))) {
            '1', 'actif', 'active', 'oui'                      => 1,
            '0', 'inactif', 'inactive', 'désactivé', 'desactive', 'non' => 0,
            default => null,
        };
    }
}
