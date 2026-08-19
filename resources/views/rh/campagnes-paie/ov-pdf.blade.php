<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>OV - {{ $campagne->code }}</title>
    <style>
        @page { margin: 14mm 12mm; size: A4; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1e293b; margin: 0; }
        .header { border-bottom: 2pt solid #1e293b; padding-bottom: 8pt; margin-bottom: 10pt; }
        .header-title { font-size: 16pt; font-weight: bold; text-align: center; letter-spacing: 2pt; }
        .header-sub { text-align: center; font-size: 9pt; color: #475569; margin-top: 3pt; }
        .meta { width: 100%; margin-top: 6pt; }
        .meta td { padding: 1pt 3pt; vertical-align: top; font-size: 8.5pt; }
        .meta strong { color: #1e293b; }
        table.lignes { width: 100%; border-collapse: collapse; margin-top: 8pt; }
        table.lignes th { background: #1e293b; color: #fff; border: 0.5pt solid #1e293b; padding: 4pt 5pt; font-size: 8pt; text-align: left; }
        table.lignes td { border: 0.5pt solid #e2e8f0; padding: 3pt 5pt; font-size: 8.5pt; }
        table.lignes td.num, table.lignes th.num { text-align: right; }
        table.lignes tr:nth-child(even) td { background: #f8fafc; }
        .total-row { background: #059669 !important; color: #fff; font-weight: bold; font-size: 10pt; }
        .total-row td { background: #059669 !important; color: #fff; }
        .signatures { margin-top: 25pt; width: 100%; }
        .signatures td { width: 50%; padding: 5pt; vertical-align: top; }
        .signature-box { border: 0.5pt solid #1e293b; height: 80pt; padding: 5pt; text-align: center; font-size: 8pt; color: #64748b; }
        .signature-box .label { display: block; margin-top: 4pt; font-weight: bold; color: #1e293b; }
        .legal { font-size: 7pt; color: #64748b; text-align: center; margin-top: 12pt; padding-top: 6pt; border-top: 0.5pt solid #cbd5e1; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-title">ORDRE DE VIREMENT BANCAIRE</div>
        <div class="header-sub">
            Campagne : <strong>{{ $campagne->code }}</strong> — {{ $campagne->libelle }}<br>
            Période : du {{ $campagne->date_debut->format('d/m/Y') }} au {{ $campagne->date_fin->format('d/m/Y') }}
            @if($campagne->date_paiement_prevue)
                · Date d'exécution prévue : <strong>{{ $campagne->date_paiement_prevue->format('d/m/Y') }}</strong>
            @endif
        </div>
    </div>

    <table class="meta">
        <tr>
            <td style="width:50%;">
                <strong>Donneur d'ordre :</strong> Yubile Technologie<br>
                BP 3403 Libreville · Tél : 01-76-48-48
            </td>
            <td style="width:50%;">
                <strong>Nombre d'opérations :</strong> {{ $lignes->count() }}<br>
                <strong>Devise :</strong> XAF
            </td>
        </tr>
    </table>

    <table class="lignes">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Bénéficiaire</th>
                <th style="width: 10%;">Matricule</th>
                <th style="width: 22%;">IBAN / RIB</th>
                <th style="width: 15%;">Banque</th>
                <th style="width: 12%;">Référence</th>
                <th class="num" style="width: 11%;">Montant (XAF)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lignes as $i => $l)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $l['beneficiaire'] }}</td>
                    <td>{{ $l['matricule'] }}</td>
                    <td>{{ $l['iban'] }}</td>
                    <td>{{ $l['banque'] }}</td>
                    <td>{{ $l['reference'] }}</td>
                    <td class="num">{{ number_format($l['montant'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center; color:#64748b; padding: 10pt;">Aucun bulletin éligible au virement.</td></tr>
            @endforelse
            <tr class="total-row">
                <td colspan="6"><strong>TOTAL À VIRER</strong></td>
                <td class="num"><strong>{{ number_format($total, 0, ',', ' ') }} XAF</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td><div class="signature-box"><span class="label">Date et signature du donneur d'ordre</span></div></td>
            <td><div class="signature-box"><span class="label">Visa banque</span></div></td>
        </tr>
    </table>

    <div class="legal">
        Document généré le {{ now()->format('d/m/Y à H:i') }} par OPTIMIZE ERP — Campagne {{ $campagne->code }}
    </div>

</body>
</html>
