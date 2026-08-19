<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Grand-livre</title>
    <style>
        @page { margin: 12mm; size: A4 landscape; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #1e293b; margin: 0; }
        .header { border-bottom: 2pt solid #4F46E5; padding-bottom: 6pt; margin-bottom: 10pt; }
        h1 { font-size: 14pt; margin: 0; color: #4F46E5; }
        .meta { font-size: 8.5pt; color: #475569; margin-top: 4pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 6pt; }
        th { background: #4F46E5; color: #fff; padding: 4pt 3pt; font-size: 7.5pt; text-align: left; }
        td { border-bottom: 0.3pt solid #e2e8f0; padding: 3pt 4pt; font-size: 7.5pt; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        tr.totaux td { background: #1e293b; color: #fff; font-weight: bold; font-size: 8.5pt; }
        .legal { font-size: 7pt; color: #64748b; text-align: center; margin-top: 8pt; }
        .badge-d { background: #fef2f2; color: #b91c1c; padding: 1pt 4pt; border-radius: 2pt; font-weight: bold; }
        .badge-c { background: #f0fdf4; color: #15803d; padding: 1pt 4pt; border-radius: 2pt; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>GRAND-LIVRE</h1>
        <div class="meta">
            @if($exercice) Exercice : <strong>{{ $exercice->libelle ?? $exercice->exercice }}</strong> · @endif
            @if($compte) Compte : <strong>{{ $compte->code }} - {{ $compte->nom }}</strong> · @endif
            @if(!empty($params['date_debut']) || !empty($params['date_fin']))
                Période :
                @if(!empty($params['date_debut'])) du {{ \Carbon\Carbon::parse($params['date_debut'])->format('d/m/Y') }} @endif
                @if(!empty($params['date_fin'])) au {{ \Carbon\Carbon::parse($params['date_fin'])->format('d/m/Y') }} @endif ·
            @endif
            <strong>{{ $ecritures->count() }} écriture(s)</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 7%;">Date</th>
                <th style="width: 8%;">N° pièce</th>
                <th style="width: 9%;">Compte</th>
                <th style="width: 33%;">Libellé</th>
                <th style="width: 8%;">Journal</th>
                <th class="num" style="width: 9%;">Débit</th>
                <th class="num" style="width: 9%;">Crédit</th>
                <th style="width: 9%;">Lot</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ecritures as $e)
                <tr>
                    <td>{{ $e->date_ecriture?->format('d/m/Y') }}</td>
                    <td>{{ $e->ref_piece ?? '—' }}</td>
                    <td><code>{{ $e->compte_general ?? '—' }}</code></td>
                    <td>{{ \Illuminate\Support\Str::limit($e->libelle ?? '—', 60) }}</td>
                    <td>{{ $e->journal ?? '—' }}</td>
                    <td class="num">
                        @if($e->sens === 'debit')
                            <span class="badge-d">{{ number_format((float) $e->montant_tc, 0, ',', ' ') }}</span>
                        @endif
                    </td>
                    <td class="num">
                        @if($e->sens === 'credit')
                            <span class="badge-c">{{ number_format((float) $e->montant_tc, 0, ',', ' ') }}</span>
                        @endif
                    </td>
                    <td><small>{{ \Illuminate\Support\Str::limit($e->num_lot ?? '—', 20) }}</small></td>
                </tr>
            @endforeach
            <tr class="totaux">
                <td colspan="5">TOTAUX</td>
                <td class="num">{{ number_format($totaux['debit'], 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($totaux['credit'], 0, ',', ' ') }}</td>
                <td>Solde {{ $totaux['solde'] >= 0 ? 'D' : 'C' }} : {{ number_format(abs($totaux['solde']), 0, ',', ' ') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="legal">Document généré le {{ now()->translatedFormat('d M Y H:i') }} — OPTIMIZE ERP</div>
</body>
</html>
