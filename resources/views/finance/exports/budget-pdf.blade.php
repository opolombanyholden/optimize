<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Budget — {{ $exercice->libelle ?? $exercice->exercice }}</title>
    <style>
        @page { margin: 12mm; size: A4 landscape; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1e293b; margin: 0; }
        .header { border-bottom: 2pt solid #4F46E5; padding-bottom: 6pt; margin-bottom: 10pt; }
        h1 { font-size: 14pt; margin: 0; color: #4F46E5; }
        .meta { font-size: 9pt; color: #475569; margin-top: 4pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 8pt; }
        th { background: #4F46E5; color: #fff; padding: 5pt 4pt; font-size: 8pt; text-align: left; }
        td { border-bottom: 0.5pt solid #e2e8f0; padding: 4pt 5pt; font-size: 8.5pt; }
        td.num, th.num { text-align: right; }
        tr.totaux td { background: #1e293b; color: #fff; font-weight: bold; font-size: 9.5pt; }
        .legal { font-size: 7pt; color: #64748b; text-align: center; margin-top: 8pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUDGET — {{ $exercice->libelle ?? $exercice->exercice }}</h1>
        <div class="meta">
            Exercice du {{ $exercice->datedebut ? \Carbon\Carbon::parse($exercice->datedebut)->format('d/m/Y') : '—' }}
            au {{ $exercice->datefin ? \Carbon\Carbon::parse($exercice->datefin)->format('d/m/Y') : '—' }} ·
            Statut : {{ ['Planifié', 'En cours', 'Clôturé'][$exercice->statut - 1] ?? '—' }} ·
            <strong>{{ count($lignes) }} ligne(s)</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 14%;">Code ligne</th>
                <th style="width: 38%;">Désignation</th>
                <th class="num" style="width: 12%;">Dotation</th>
                <th class="num" style="width: 12%;">Fonds propres</th>
                <th class="num" style="width: 12%;">Reports</th>
                <th class="num" style="width: 12%;">Engagé</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $l)
                <tr>
                    <td><code>{{ $l->id_budgetligne }}</code></td>
                    <td>{{ \Illuminate\Support\Str::limit($l->commentaire ?? '—', 80) }}</td>
                    <td class="num">{{ number_format((float)$l->dotation_etat, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format((float)$l->fonds_propres, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format((float)$l->reports_budgetaire + (float)$l->reports_tresorerie, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format((float)$l->engagement, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
            <tr class="totaux">
                <td colspan="2">TOTAUX</td>
                <td class="num">{{ number_format($totaux['budget'], 0, ',', ' ') }}</td>
                <td class="num">—</td>
                <td class="num">—</td>
                <td class="num">{{ number_format($totaux['engage'], 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    @php $taux = $totaux['budget'] > 0 ? round($totaux['engage'] / $totaux['budget'] * 100, 1) : 0; @endphp
    <div style="margin-top: 12pt; padding: 8pt; background: #F1F5F9; border-radius: 4pt;">
        <strong>Synthèse :</strong> Budget total <strong>{{ number_format($totaux['budget'], 0, ',', ' ') }} XAF</strong> ·
        Engagé <strong>{{ number_format($totaux['engage'], 0, ',', ' ') }} XAF ({{ $taux }}%)</strong> ·
        Disponible <strong>{{ number_format($totaux['disponible'], 0, ',', ' ') }} XAF</strong>
    </div>

    <div class="legal">Document généré le {{ now()->translatedFormat('d M Y H:i') }} — OPTIMIZE ERP</div>
</body>
</html>
