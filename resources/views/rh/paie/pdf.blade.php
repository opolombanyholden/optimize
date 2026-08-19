@php
    $emp = $paie->employee;
    $fmt = fn($n) => is_null($n) ? '' : number_format((float)$n, 0, ',', ' ');
    $lignes = $paie->rubriques()->with('groupe')->orderBy('rubriques.ordre_affichage')->get();
    $lignesByGroupe = $lignes->groupBy(fn($r) => $r->groupe?->libelle ?? 'Cotisations & précomptes');
    $totalCotSal   = (float) $paie->cotisations_salariales;
    $totalCotPat   = (float) $paie->cotisations_patronales;
    $anciennete    = $emp?->date_embauche ? \Carbon\Carbon::parse($emp->date_embauche)->diff(now()) : null;
    $ancienneteLib = $anciennete ? sprintf('%d an(s) %d mois', $anciennete->y, $anciennete->m) : '—';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bulletin {{ $paie->numero_bulletin ?? $paie->label }}</title>
    <style>
        @page { margin: 14mm 12mm; size: A4; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 9pt; margin: 0; padding: 0; }
        h1, h2, h3, h4 { margin: 0; padding: 0; }
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 2pt 4pt; vertical-align: top; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .center { text-align: center; }
        .header { width: 100%; border-bottom: 2pt solid #1e293b; padding-bottom: 6pt; }
        .header td { vertical-align: top; }
        .employer-name { font-size: 14pt; font-weight: bold; letter-spacing: 1pt; }
        .agency, .meta { font-size: 7pt; color: #475569; line-height: 1.3; }
        .meta { margin-top: 4pt; }
        .title-box { text-align: center; font-size: 13pt; font-weight: bold; letter-spacing: 2pt; }
        .title-box .period { font-size: 8pt; font-weight: normal; color: #475569; letter-spacing: 0; margin-top: 2pt; display: block; }
        .title-box .numero { font-size: 8pt; font-weight: 600; color: #475569; letter-spacing: 0; display: block; }
        .employee-block { font-size: 8pt; line-height: 1.4; }
        .employee-name { font-size: 10pt; font-weight: bold; }
        .employee-block table { width: 100%; }
        .employee-block td:first-child { color: #64748b; width: 42%; }
        .grid { width: 100%; margin-top: 8pt; }
        .grid th { background: #f1f5f9; border: 0.5pt solid #cbd5e1; padding: 4pt 3pt; font-size: 7.5pt; font-weight: bold; color: #334155; text-align: center; }
        .grid td { border: 0.5pt solid #e2e8f0; padding: 2.5pt 4pt; font-size: 8pt; }
        .grid tr.group-row td { background: #eff6ff; font-weight: bold; color: #1e3a8a; font-size: 8pt; text-transform: uppercase; padding: 3pt 5pt; letter-spacing: 0.5pt; }
        .grid tr.subtotal td { background: #fffbeb; font-weight: bold; color: #92400e; }
        .grid tr.total td { background: #1e293b; color: #fff; font-weight: bold; font-size: 9pt; }
        .code { text-align: center; color: #64748b; font-weight: 600; }
        .foot { width: 100%; margin-top: 6pt; border-top: 1.5pt solid #1e293b; }
        .foot td { vertical-align: top; padding: 6pt 4pt; }
        .cumul-section { font-size: 7.5pt; }
        .cumul-section strong { font-size: 8pt; color: #1e293b; }
        .cumul-section table { width: 100%; margin-top: 3pt; }
        .cumul-section th { background: #f8fafc; padding: 1.5pt 3pt; text-align: right; color: #64748b; font-size: 7pt; }
        .cumul-section td { padding: 1.5pt 3pt; text-align: right; font-size: 7.5pt; }
        .visa-box { border: 0.5pt solid #cbd5e1; height: 70pt; padding: 3pt; text-align: center; font-size: 7pt; color: #64748b; }
        .net-banner { background: #059669; color: #fff; padding: 6pt 10pt; font-size: 11pt; font-weight: bold; text-align: right; margin-top: 4pt; }
        .net-banner .amount { font-size: 14pt; font-weight: bold; }
        .net-banner .label { font-size: 7pt; }
        .legal-note { font-size: 6.5pt; color: #64748b; padding: 4pt; text-align: center; margin-top: 3pt; }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <table class="header">
        <tr>
            <td style="width: 35%; padding-right: 6pt; border-right: 0.5pt solid #cbd5e1;">
                <div class="employer-name">OPTIMIZE ERP</div>
                <div class="agency">Yubile Technologie · Libreville, Gabon</div>
                <div class="meta">
                    BP : 3403 Libreville · Tél : 01-76-48-48<br>
                    Matricule CNSS : 001-0185785-0<br>
                    Matricule CNAMGS : 1015-0001646<br>
                    Site : www.yubile.com
                </div>
            </td>
            <td style="width: 30%; padding: 0 6pt; border-right: 0.5pt solid #cbd5e1;" class="title-box">
                BULLETIN DE PAIE
                <span class="period">Période du {{ $paie->debut?->format('d/m/y') }} au {{ $paie->fin?->format('d/m/y') }}</span>
                @if($paie->numero_bulletin)
                    <span class="numero">N° {{ $paie->numero_bulletin }}</span>
                @endif
            </td>
            <td style="width: 35%; padding-left: 6pt;" class="employee-block">
                <div class="employee-name">
                    {{ $emp?->sexe === 'F' ? 'Mme' : 'M' }} {{ $emp?->noms }} {{ $emp?->prenoms }}
                </div>
                <table>
                    <tr><td>Matricule</td><td><strong>{{ $emp?->matricule ?? '—' }}</strong></td></tr>
                    <tr><td>Fonction</td><td>{{ $emp?->poste ?? '—' }}</td></tr>
                    <tr><td>Matricule CNSS</td><td>{{ $emp?->matricule_cnss ?? '—' }}</td></tr>
                    <tr><td>Matricule CNAMGS</td><td>{{ $emp?->matricule_cnamgs ?? '—' }}</td></tr>
                    <tr><td>R.I.B.</td><td>{{ $paie->compte_bancaire ?? $emp?->iban ?? '—' }}</td></tr>
                    <tr><td>Contrat</td><td>{{ $emp?->type_contrat ?? '—' }}</td></tr>
                    <tr><td>Embauche</td><td>{{ $emp?->date_embauche?->format('d/m/y') ?? '—' }}</td></tr>
                    <tr><td>Ancienneté</td><td>{{ $ancienneteLib }}</td></tr>
                    <tr><td>Sit. fam.</td><td>{{ $emp?->situation_matrimoniale ?? '—' }} · {{ $emp?->nombre_enfants ?? 0 }} enf.</td></tr>
                    <tr><td>Nb parts</td><td><strong>{{ number_format($paie->part_impots ?? $emp?->parts_fiscales ?? 1, 2, ',', ' ') }}</strong></td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- TABLEAU PRINCIPAL --}}
    <table class="grid">
        <thead>
            <tr>
                <th style="width: 8%;">N°</th>
                <th style="width: 32%; text-align: left;">Désignation</th>
                <th style="width: 8%;">Nb.</th>
                <th style="width: 12%;">Base</th>
                <th style="width: 7%;">Taux</th>
                <th style="width: 11%;">Gain</th>
                <th style="width: 11%;">Retenue</th>
                <th style="width: 11%;">Pat.</th>
            </tr>
        </thead>
        <tbody>
            @if($paie->salaire_base > 0)
                <tr class="group-row"><td colspan="8">Rémunérations de base</td></tr>
                <tr>
                    <td class="code">1000</td>
                    <td>Salaire de base</td>
                    <td class="num">30,00</td>
                    <td class="num">{{ $fmt($paie->salaire_base) }}</td>
                    <td class="num">—</td>
                    <td class="num"><strong>{{ $fmt($paie->salaire_base) }}</strong></td>
                    <td></td><td></td>
                </tr>
                @if($paie->primes > 0)
                    <tr><td class="code">1500</td><td>Primes</td><td></td><td></td><td class="num">—</td>
                        <td class="num">{{ $fmt($paie->primes) }}</td><td></td><td></td></tr>
                @endif
                @if($paie->heures_sup > 0)
                    <tr><td class="code">1700</td><td>Heures supplémentaires</td><td></td><td></td><td class="num">—</td>
                        <td class="num">{{ $fmt($paie->heures_sup) }}</td><td></td><td></td></tr>
                @endif
                @if($paie->indemnites > 0)
                    <tr><td class="code">5000</td><td>Indemnités diverses</td><td></td><td></td><td class="num">—</td>
                        <td class="num">{{ $fmt($paie->indemnites) }}</td><td></td><td></td></tr>
                @endif
            @endif

            @foreach($lignesByGroupe as $groupeLib => $rubGroupe)
                <tr class="group-row"><td colspan="8">{{ $groupeLib }}</td></tr>
                @foreach($rubGroupe as $r)
                    @php
                        $base = (float) $r->pivot->base;
                        $taux = (float) $r->pivot->taux;
                        $montant = (float) $r->pivot->montant;
                        $isGain = $r->type === 'gain';
                        $isPatronale = str_starts_with($r->code, 'PAT_');
                    @endphp
                    <tr>
                        <td class="code">{{ str_replace('PAT_', '', $r->code) }}</td>
                        <td>{{ $r->libelle }}</td>
                        <td></td>
                        <td class="num">{{ $fmt($base) }}</td>
                        <td class="num">{{ $taux > 0 && !$isPatronale ? number_format($taux, 2, ',', ' ') : '—' }}</td>
                        <td class="num">{{ $isGain ? $fmt($montant) : '' }}</td>
                        <td class="num">{{ !$isGain && !$isPatronale ? $fmt($montant) : '' }}</td>
                        <td class="num">{{ $isPatronale ? $fmt($montant) : '' }}</td>
                    </tr>
                @endforeach
            @endforeach

            <tr class="subtotal">
                <td colspan="2"><strong>Total Brut</strong></td>
                <td></td><td></td><td></td>
                <td class="num"><strong>{{ $fmt($paie->brut) }}</strong></td>
                <td></td><td></td>
            </tr>
            <tr class="subtotal">
                <td colspan="2"><strong>Total Cotisations & Retenues</strong></td>
                <td></td><td></td><td></td><td></td>
                <td class="num"><strong>{{ $fmt($totalCotSal + (float)$paie->irpp + (float)$paie->retenues + (float)$paie->avances) }}</strong></td>
                <td class="num"><strong>{{ $fmt($totalCotPat) }}</strong></td>
            </tr>
            <tr class="total">
                <td colspan="5"><strong>NET À PAYER</strong></td>
                <td colspan="3" class="num"><strong>{{ $fmt($paie->net_a_payer) }} XAF</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- PIED : CUMULS + VISAS --}}
    <table class="foot">
        <tr>
            <td style="width: 60%;" class="cumul-section">
                <strong>Cumuls</strong>
                <table>
                    <thead><tr><th></th><th>Période</th><th>Année (cumul)</th></tr></thead>
                    <tbody>
                        <tr><th>Salaire brut</th><td>{{ $fmt($paie->brut) }}</td><td>{{ $fmt($paie->cumul_annuel_brut) }}</td></tr>
                        <tr><th>Net imposable</th><td>{{ $fmt($paie->net_imposable) }}</td><td>{{ $fmt($paie->cumul_annuel_net_imposable) }}</td></tr>
                        <tr><th>Charges salariales</th><td>{{ $fmt($totalCotSal + (float)$paie->irpp) }}</td><td>{{ $fmt($paie->cumul_annuel_cotisations_sal + $paie->cumul_annuel_irpp) }}</td></tr>
                        <tr><th>Charges patronales</th><td>{{ $fmt($totalCotPat) }}</td><td>{{ $fmt($paie->cumul_annuel_cotisations_pat) }}</td></tr>
                        <tr><th>Brut congés</th><td>{{ $fmt($paie->brut_conges_mois) }}</td><td>{{ $fmt($paie->brut_conges_cumul) }}</td></tr>
                        <tr><th>Nb jrs acquis</th><td>{{ number_format($paie->nb_jrs_acquis_mois ?? 0, 2, ',', ' ') }}</td><td>{{ number_format($paie->nb_jrs_acquis_annee ?? 0, 2, ',', ' ') }}</td></tr>
                    </tbody>
                </table>
            </td>
            <td style="width: 40%; padding-left: 8pt;">
                <table>
                    <tr>
                        <td style="width: 50%; padding-right: 3pt;"><div class="visa-box">Visa de l'employeur</div></td>
                        <td style="width: 50%; padding-left: 3pt;"><div class="visa-box">Visa de l'employé</div></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- BANNIÈRE NET --}}
    <div class="net-banner">
        <span class="label">NET À PAYER · {{ $paie->debut?->translatedFormat('F Y') }}</span>
        &nbsp;&nbsp;<span class="amount">{{ $fmt($paie->net_a_payer) }} XAF</span>
    </div>

    <div class="legal-note">
        Pour vous aider à faire valoir vos droits, conservez ce bulletin de paie sans limitation de durée.
    </div>

</body>
</html>
