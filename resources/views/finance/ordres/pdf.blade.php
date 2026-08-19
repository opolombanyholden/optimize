<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $ordre->numero_ordre }}</title>
    <style>
        @page { margin: 20mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #222; }
        h2 { text-align: center; font-size: 14pt; margin: 6pt 0; }
        h3 { text-align: center; font-size: 11pt; margin: 4pt 0; color: #444; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 8pt; margin-bottom: 10pt; }
        .small { font-size: 8pt; }
        .muted { color: #666; }
        .box { border: 1px solid #000; padding: 6pt; margin-bottom: 8pt; }
        .box-title { background: #f2f2f2; padding: 3pt 6pt; margin: -6pt -6pt 6pt -6pt; font-weight: bold; text-transform: uppercase; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8pt; }
        th, td { border: 1px solid #333; padding: 4pt 6pt; vertical-align: top; }
        th { background: #eee; font-size: 9pt; text-transform: uppercase; }
        td.num { text-align: right; }
        .total-block { text-align: center; padding: 10pt; background: #f8f8f8; border: 2px solid #000; margin: 10pt 0; }
        .total-amount { font-size: 16pt; font-weight: bold; }
        .signatures { width: 100%; margin-top: 30pt; }
        .signatures td { border: 1px solid #333; height: 100pt; vertical-align: top; padding: 6pt; text-align: center; }
        .sig-role { text-transform: uppercase; font-weight: bold; font-size: 9pt; margin-bottom: 4pt; }
        .sig-name { position: relative; top: 70pt; font-size: 9pt; font-style: italic; }
        .footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 7pt; color: #666; border-top: 1px solid #ccc; padding-top: 3pt; }
        .fields-grid { width: 100%; }
        .fields-grid td { border: 1px solid #333; padding: 4pt 6pt; }
        .fields-grid .label { background: #eee; font-size: 8pt; text-transform: uppercase; width: 30%; }
        .num-ordre { font-size: 13pt; font-weight: bold; }
        .intro { font-style: italic; font-size: 9pt; margin: 8pt 0; }
    </style>
</head>
<body>

<div class="header">
    @if($ordre->modele->entete_soustitre)
        <div class="small">{{ $ordre->modele->entete_soustitre }}</div>
    @endif
    <h2>{{ $ordre->modele->entete_titre ?? $ordre->modele->libelle }}</h2>
    <div class="num-ordre">N° {{ $ordre->numero_ordre }}</div>
    <div><strong><em>EXERCICE BUDGETAIRE : {{ $ordre->exercice?->libelle ?? $ordre->exercice?->exercice }}</em></strong></div>
</div>

@if($ordre->modele->phrase_intro)
    <p class="intro">{{ $ordre->modele->phrase_intro }}</p>
@endif

{{-- Ligne budgétaire --}}
@if($ordre->budgetLigne)
    <div class="box">
        <div class="box-title">Ligne budgétaire</div>
        <strong>{{ $ordre->budgetLigne->id_budgetligne }}</strong>
        @if($ordre->budgetLigne->ligne)
            — [{{ $ordre->budgetLigne->ligne->titre?->imputation }}]
            {{ $ordre->budgetLigne->ligne->libelle }}
        @endif
    </div>
@endif

{{-- Champs dynamiques du modèle --}}
@if($ordre->modele->champs->isNotEmpty())
    <table class="fields-grid">
        @foreach($ordre->modele->champs as $c)
            @php $val = $ordre->donnees_json[$c->code_champ] ?? '—'; @endphp
            <tr>
                <td class="label">{{ $c->label_personnalise }}</td>
                <td>
                    @if($c->type_saisie === 'number' && is_numeric($val))
                        {{ number_format((float) $val, 0, ',', ' ') }}
                    @elseif($c->type_saisie === 'date' && $val && $val !== '—')
                        {{ \Carbon\Carbon::parse($val)->format('d/m/Y') }}
                    @elseif($c->type_saisie === 'select')
                        {{ ($c->options_json ?? [])[$val] ?? $val }}
                    @else
                        {{ $val }}
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
@endif

{{-- Détails ventilés --}}
@if($ordre->details->isNotEmpty())
    <div class="box">
        <div class="box-title">Détail ventilé</div>
        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Libellé</th>
                    <th style="width:10%">Qté</th>
                    <th style="width:15%">Prix unit.</th>
                    <th style="width:15%">Montant</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ordre->details as $d)
                    <tr>
                        <td>{{ $d->rubrique?->libelle ?? '—' }}</td>
                        <td>{{ $d->libelle }}</td>
                        <td class="num">{{ number_format((float) $d->quantite, 2, ',', ' ') }}</td>
                        <td class="num">{{ number_format((float) $d->prix_unitaire, 0, ',', ' ') }}</td>
                        <td class="num"><strong>{{ number_format((float) $d->montant, 0, ',', ' ') }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- Total + phrase de conclusion --}}
<div class="total-block">
    <div class="small">{{ $ordre->modele->phrase_conclusion ?? 'MONTANT TOTAL :' }}</div>
    <div class="total-amount">{{ number_format((float) $ordre->montant, 0, ',', ' ') }} {{ $ordre->compte?->devise ?: 'XAF' }}</div>
    <div style="margin-top:6pt; font-style:italic; font-size:9pt;">
        <strong>Arrêté à la somme de :</strong> {{ $ordre->montant_en_lettres }}
    </div>
</div>

<p style="margin-top: 12pt;">Pour acquit de la somme ci-dessus</p>
<p>A ______________, le ____ / ____ / {{ $ordre->exercice?->exercice ?? date('Y') }}</p>

{{-- Signataires --}}
@if($ordre->modele->signataires->isNotEmpty())
    <table class="signatures">
        <tr>
        @foreach($ordre->modele->signataires as $s)
            <td>
                <div class="sig-role">{{ $s->role_libelle }}</div>
                @if($s->userParDefaut)
                    <div class="sig-name">{{ $s->userParDefaut->name }}</div>
                @endif
            </td>
        @endforeach
        </tr>
    </table>
@endif

<div class="footer">
    Généré le {{ now()->format('d/m/Y H:i') }}
    · Statut : {{ $ordre->statut_libelle }}
    @if($ordre->grand_livre_id)
        · Écriture GL #{{ $ordre->grand_livre_id }}
    @endif
</div>

</body>
</html>
