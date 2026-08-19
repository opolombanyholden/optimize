<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $declaration->code }}</title>
    <style>
        @page { margin: 12mm 10mm; size: A4 landscape; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #1e293b; margin: 0; }
        .header { border-bottom: 2pt solid #1e293b; padding-bottom: 6pt; margin-bottom: 8pt; }
        .header-title { font-size: 14pt; font-weight: bold; text-align: center; letter-spacing: 2pt; }
        .header-sub { font-size: 9pt; text-align: center; color: #475569; margin-top: 2pt; }
        .meta { display: table; width: 100%; margin-top: 4pt; font-size: 8pt; }
        .meta-col { display: table-cell; width: 33%; padding: 0 5pt; }
        .meta-col strong { color: #1e293b; }
        table.lignes { width: 100%; border-collapse: collapse; margin-top: 6pt; }
        table.lignes th { background: #f1f5f9; border: 0.5pt solid #cbd5e1; padding: 3pt 4pt; text-align: left; font-size: 7.5pt; }
        table.lignes td { border: 0.5pt solid #e2e8f0; padding: 2.5pt 4pt; font-size: 7.5pt; }
        table.lignes td.num, table.lignes th.num { text-align: right; }
        table.lignes tfoot td { background: #1e293b; color: #fff; font-weight: bold; }
        .totaux { margin-top: 6pt; padding: 6pt; background: #fffbeb; border-left: 3pt solid #f59e0b; font-size: 9pt; }
        .totaux strong { font-size: 10pt; }
        .legal { font-size: 6.5pt; color: #64748b; text-align: center; margin-top: 8pt; padding-top: 6pt; border-top: 0.5pt solid #cbd5e1; }
    </style>
</head>
<body>
    @php $emp = $declaration->snapshot_employeur ?? []; @endphp

    <div class="header">
        <div class="header-title">DÉCLARATION {{ strtoupper($declaration->organisme_libelle) }}</div>
        <div class="header-sub">
            {{ $declaration->periode_libelle }} · Période : {{ $declaration->date_debut->format('d/m/Y') }} au {{ $declaration->date_fin->format('d/m/Y') }} · Code : <strong>{{ $declaration->code }}</strong>
        </div>
        <div class="meta">
            <div class="meta-col">
                <strong>Employeur :</strong> {{ $emp['raison_sociale'] ?? '—' }}<br>
                {{ $emp['adresse'] ?? '' }}<br>
                Tél : {{ $emp['telephone'] ?? '—' }}
            </div>
            <div class="meta-col">
                <strong>Matricule CNSS :</strong> {{ $emp['matricule_cnss'] ?? '—' }}<br>
                <strong>Matricule CNAMGS :</strong> {{ $emp['matricule_cnamgs'] ?? '—' }}
            </div>
            <div class="meta-col">
                <strong>Nombre d'employés :</strong> {{ $declaration->nombre_employes }}<br>
                <strong>Statut :</strong> {{ $declaration->statut_libelle }}<br>
                @if($declaration->reference_depot)
                    <strong>Réf. dépôt :</strong> {{ $declaration->reference_depot }}
                @endif
            </div>
        </div>
    </div>

    <table class="lignes">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom et prénoms</th>
                <th>Mat. organisme</th>
                <th>NIP</th>
                <th>Sexe</th>
                <th class="num">Jours</th>
                <th class="num">Brut</th>
                <th class="num">Brut plafonné</th>
                <th class="num">Cot. salariale</th>
                <th class="num">Cot. patronale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($declaration->lignes as $l)
                <tr>
                    <td>{{ $l->matricule_employeur ?? '—' }}</td>
                    <td>{{ $l->noms }} {{ $l->prenoms }}</td>
                    <td>{{ $l->matricule_organisme ?? '—' }}</td>
                    <td>{{ $l->nip ?? '—' }}</td>
                    <td>{{ $l->sexe ?? '—' }}</td>
                    <td class="num">{{ $l->nb_jours_travailles }}</td>
                    <td class="num">{{ number_format($l->brut, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($l->brut_plafonne, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($l->cot_salariale, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($l->cot_patronale, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">TOTAUX</td>
                <td class="num">—</td>
                <td class="num">{{ number_format($declaration->total_brut, 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($declaration->total_brut_plafonne, 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($declaration->total_cot_salariale, 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($declaration->total_cot_patronale, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="totaux">
        Total à reverser à <strong>{{ $declaration->organisme_libelle }}</strong> :
        <strong>{{ number_format($declaration->total_cot_salariale + $declaration->total_cot_patronale, 0, ',', ' ') }} XAF</strong>
        ({{ number_format($declaration->total_cot_salariale, 0, ',', ' ') }} salariale + {{ number_format($declaration->total_cot_patronale, 0, ',', ' ') }} patronale)
    </div>

    <div class="legal">
        Document généré le {{ now()->format('d/m/Y à H:i') }} — OPTIMIZE ERP
    </div>
</body>
</html>
