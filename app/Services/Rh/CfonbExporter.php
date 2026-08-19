<?php

namespace App\Services\Rh;

use App\Models\CampagnePaie;
use App\Models\Paie;
use Illuminate\Support\Str;

/**
 * Génération d'ordres de virement au format CFONB-160 (norme française AFB/CFONB).
 *
 * Structure d'un fichier CFONB-160 :
 *   • 1 enregistrement « en-tête émetteur » (code 03) — début du lot
 *   • N enregistrements « virement » (code 06) — un par bénéficiaire
 *   • 1 enregistrement « total » (code 08) — fin du lot
 *
 * Chaque ligne fait EXACTEMENT 160 caractères ASCII, en cadrage fixe, sans séparateur.
 * Montants en CENTIMES (ex : 1 234,56 € → "000000000123456" sur 12 chars cadré à droite).
 *
 * Cette implémentation suit la variante « virement domestique » (FR-XOF/XAF acceptés
 * par les banques utilisant le standard CFONB en Afrique francophone, notamment via
 * passerelles BICIG / Ecobank / UGB). Pour des virements internationaux SWIFT,
 * préférer pain.001 (ISO 20022) — non implémenté ici.
 *
 * Référence : Comité Français d'Organisation et de Normalisation Bancaires (CFONB)
 * « Cahier des charges Virement Inter-bancaire » v3.
 */
class CfonbExporter
{
    private const LINE_LEN = 160;

    /**
     * Construit le contenu CFONB-160 d'une campagne de paie validée.
     */
    public function exporter(CampagnePaie $campagne, array $emetteur): string
    {
        $bulletins = Paie::with('employee')
            ->where('campagne_paie_id', $campagne->id)
            ->whereIn('statut', [1, 2])
            ->get();

        $lignes = [];
        $totalCentimes = 0;
        $dateExec = $campagne->date_paiement_prevue ?: $campagne->date_fin ?: now();

        // ── En-tête (code 03) ─────────────────────────────
        $lignes[] = $this->ligneEnTete($emetteur, $dateExec, $campagne->code);

        // ── Détails (code 06) ─────────────────────────────
        $rang = 1;
        foreach ($bulletins as $b) {
            $emp = $b->employee;
            if (!$emp) continue;
            $reste = (float) $b->net_a_payer - (float) $b->payements()->where('statut', 1)->sum('montant');
            if ($reste <= 0) continue;

            $centimes = (int) round($reste * 100);
            $lignes[] = $this->ligneVirement(
                rang:        $rang++,
                ribBenef:    $this->parseRib($b->compte_bancaire ?? $emp->iban ?? ''),
                beneficiaire: trim(($emp->noms ?? '') . ' ' . ($emp->prenoms ?? '')),
                montantCentimes: $centimes,
                reference:   $b->numero_bulletin ?? ('PAIE-' . $b->id),
                emetteur:    $emetteur,
            );
            $totalCentimes += $centimes;
        }

        // ── Total (code 08) ───────────────────────────────
        $lignes[] = $this->ligneTotal($emetteur, $totalCentimes);

        // Fichier final : lignes séparées par CRLF (norme bancaire)
        return implode("\r\n", $lignes) . "\r\n";
    }

    private function ligneEnTete(array $emetteur, $dateExec, string $reference): string
    {
        $rib = $this->parseRib($emetteur['rib'] ?? '');
        $line = '03';                                              // 1-2   code enregistrement
        $line .= '02';                                             // 3-4   code opération (02 = virement)
        $line .= $this->pad($emetteur['num_emetteur'] ?? '000000', 6, '0', STR_PAD_LEFT); // 5-10
        $line .= $this->pad(Str::ascii($reference), 16);           // 11-26 référence
        $line .= str_repeat(' ', 8);                               // 27-34 réservé
        $line .= $this->pad('', 10);                                // 35-44 zone réservée banque
        $line .= $this->dateJJMMAA($dateExec);                     // 45-50 date émission JJMMAA
        $line .= $this->pad(Str::ascii($emetteur['raison_sociale'] ?? ''), 24); // 51-74 raison sociale
        $line .= $this->pad('', 25);                               // 75-99 référence donneur
        $line .= $this->pad('', 19);                               // 100-118 réservé
        $line .= $this->pad($rib['banque'], 5, '0', STR_PAD_LEFT); // 119-123 code banque
        $line .= $this->pad($rib['guichet'], 5, '0', STR_PAD_LEFT);// 124-128 code guichet
        $line .= $this->pad($rib['compte'], 11, '0', STR_PAD_LEFT);// 129-139 compte
        $line .= str_repeat('0', 13);                              // 140-152 zone montant (vide en entête)
        $line .= $this->pad('', 8);                                // 153-160 réservé

        return $this->normaliser($line);
    }

    private function ligneVirement(int $rang, array $ribBenef, string $beneficiaire, int $montantCentimes, string $reference, array $emetteur): string
    {
        $ribEmetteur = $this->parseRib($emetteur['rib'] ?? '');
        $line = '06';                                              // 1-2   code enregistrement
        $line .= '02';                                             // 3-4   code opération
        $line .= $this->pad($emetteur['num_emetteur'] ?? '000000', 6, '0', STR_PAD_LEFT); // 5-10
        $line .= $this->pad(Str::ascii($reference), 12);           // 11-22 référence opération
        $line .= $this->pad(Str::ascii($beneficiaire), 24);        // 23-46 nom bénéficiaire
        $line .= $this->pad('', 24);                               // 47-70 adresse 1
        $line .= $this->pad('', 24);                               // 71-94 adresse 2
        $line .= $this->pad($ribBenef['banque'], 5, '0', STR_PAD_LEFT);    // 95-99
        $line .= $this->pad($ribBenef['guichet'], 5, '0', STR_PAD_LEFT);   // 100-104
        $line .= $this->pad($ribBenef['compte'], 11, '0', STR_PAD_LEFT);   // 105-115
        $line .= $this->pad((string) $montantCentimes, 12, '0', STR_PAD_LEFT); // 116-127 montant centimes
        $line .= $this->pad(Str::ascii($reference), 31);           // 128-158 zone libre destinataire
        $line .= $this->pad('', 2);                                // 159-160 réservé

        return $this->normaliser($line);
    }

    private function ligneTotal(array $emetteur, int $totalCentimes): string
    {
        $rib = $this->parseRib($emetteur['rib'] ?? '');
        $line = '08';                                              // 1-2
        $line .= '02';                                             // 3-4
        $line .= $this->pad($emetteur['num_emetteur'] ?? '000000', 6, '0', STR_PAD_LEFT); // 5-10
        $line .= str_repeat(' ', 108);                             // 11-118 réservé
        $line .= $this->pad($rib['banque'], 5, '0', STR_PAD_LEFT); // 119-123
        $line .= $this->pad($rib['guichet'], 5, '0', STR_PAD_LEFT);// 124-128
        $line .= $this->pad($rib['compte'], 11, '0', STR_PAD_LEFT);// 129-139
        $line .= $this->pad((string) $totalCentimes, 13, '0', STR_PAD_LEFT); // 140-152
        $line .= str_repeat(' ', 8);                               // 153-160

        return $this->normaliser($line);
    }

    /**
     * Parse un RIB/IBAN en composantes banque/guichet/compte/clé.
     * Accepte des formats avec espaces ou tirets. Tolérant si format inattendu.
     */
    private function parseRib(string $rib): array
    {
        $clean = preg_replace('/[^0-9A-Z]/', '', strtoupper($rib));
        // IBAN FR : FRkk BBBBB GGGGG CCCCCCCCCCC KK (4 + 5 + 5 + 11 + 2)
        // RIB FR  : BBBBB GGGGG CCCCCCCCCCC KK   (5 + 5 + 11 + 2)
        // Compte XAF Gabon : généralement 23 chars, format BICIG/UGB/Ecobank
        if (str_starts_with($clean, 'FR') && strlen($clean) >= 27) {
            return [
                'banque'  => substr($clean, 4, 5),
                'guichet' => substr($clean, 9, 5),
                'compte'  => substr($clean, 14, 11),
                'cle'     => substr($clean, 25, 2),
            ];
        }
        // Fallback : on suppose un format positionnel cadrable
        return [
            'banque'  => substr($clean, 0, 5)  ?: '00000',
            'guichet' => substr($clean, 5, 5)  ?: '00000',
            'compte'  => substr($clean, 10, 11) ?: '00000000000',
            'cle'     => substr($clean, 21, 2) ?: '00',
        ];
    }

    private function dateJJMMAA($date): string
    {
        $c = \Carbon\Carbon::parse($date);
        return $c->format('dmy');
    }

    private function pad(string $value, int $length, string $fill = ' ', int $type = STR_PAD_RIGHT): string
    {
        // Tronque si trop long
        if (mb_strlen($value) > $length) {
            $value = mb_substr($value, 0, $length);
        }
        return str_pad($value, $length, $fill, $type);
    }

    /**
     * Force la ligne à exactement 160 caractères ASCII.
     */
    private function normaliser(string $line): string
    {
        // Conversion ASCII forcée
        $line = mb_convert_encoding($line, 'ASCII', 'UTF-8');
        if (strlen($line) < self::LINE_LEN) {
            $line .= str_repeat(' ', self::LINE_LEN - strlen($line));
        } elseif (strlen($line) > self::LINE_LEN) {
            $line = substr($line, 0, self::LINE_LEN);
        }
        return $line;
    }
}
