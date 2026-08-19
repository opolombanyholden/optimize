<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Balance — {{ $exercice->libelle ?? $exercice->exercice }}</title>
    <style>
        @page { margin: 14mm; size: A4; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1e293b; margin: 0; }
        .header { border-bottom: 2pt solid #4F46E5; padding-bottom: 6pt; margin-bottom: 10pt; }
        h1 { font-size: 16pt; margin: 0; color: #4F46E5; letter-spacing: 1pt; }
        .meta { font-size: 9pt; color: #475569; margin-top: 4pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 8pt; }
        th { background: #4F46E5; color: #fff; padding: 5pt 4pt; font-size: 8pt; text-align: left; }
        td { border-bottom: 0.5pt solid #e2e8f0; padding: 4pt 5pt; font-size: 8.5pt; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        tr.totaux td { background: #1e293b; color: #fff; font-weight: bold; font-size: 9.5pt; padding: 6pt 5pt; }
        .badge-D { background: #fef2f2; color: #b91c1c; padding: 1pt 5pt; border-radius: 2pt; font-weight: bold; font-size: 7.5pt; }
        .badge-C { background: #f0fdf4; color: #15803d; padding: 1pt 5pt; border-radius: 2pt; font-weight: bold; font-size: 7.5pt; }
        .legal { font-size: 7pt; color: #64748b; text-align: center; margin-top: 8pt; }
        .summary { margin-top: 12pt; padding: 8pt; background: #F1F5F9; border-radius: 4pt; font-size: 9pt; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BALANCE DES COMPTES</h1>
        <div class="meta">
            Exercice <strong>{{ $exercice->libelle ?? $exercice->exercice }}</strong> ·
            <strong>{{ count($balance) }} compte(s)</strong> mouvementés
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Compte</th>
                <th style="width: 38%;">Libellé</th>
                <th class="num" style="width: 8%;">Nb écr.</th>
                <th class="num" style="width: 14%;">Σ Débits</th>
                <th class="num" style="width: 14%;">Σ Crédits</th>
                <th class="num" style="width: 14%;">Solde</th>
            </tr>
        </thead>
        <tbody>
            @foreach($balance as $b)
                <tr>
                    <td><code>{{ $b['code'] }}</code></td>
                    <td>{{ \Illuminate\Support\Str::limit($b['libelle'], 50) }}</td>
                    <td class="num">{{ $b['nb'] }}</td>
                    <td class="num">{{ number_format($b['debit'], 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($b['credit'], 0, ',', ' ') }}</td>
                    <td class="num">
                        <span class="badge-{{ $b['sens_solde'] }}">{{ number_format(abs($b['solde']), 0, ',', ' ') }}</span>
                    </td>
                </tr>
            @endforeach
            <tr class="totaux">
                <td colspan="3">TOTAUX</td>
                <td class="num">{{ number_format($totaux['debit'], 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['credit'], 0, ',', ' ') }}</td>
                <td class="num">
                    @php $delta = $totaux['debit'] - $totaux['credit']; @endphp
                    @if(abs($delta) < 0.01)
                        ✓ Équilibrée
                    @else
                        Écart : {{ number_format(abs($delta), 2, ',', ' ') }}
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <strong>Lecture :</strong> Un solde <span class="badge-D">D</span> (débiteur) signifie que les débits dépassent les crédits sur le compte (typique des charges, immobilisations, trésorerie active).
        Un solde <span class="badge-C">C</span> (créditeur) signifie l'inverse (typique des dettes, capitaux, recettes).
    </div>

    <div class="legal">Document généré le {{ now()->translatedFormat('d M Y H:i') }} — OPTIMIZE ERP</div>
</body>
</html>
