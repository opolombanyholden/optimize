@extends('layouts.app')

@section('title', 'Bulletin de paie — ' . ($paie->numero_bulletin ?? $paie->label))

@section('breadcrumb')
    <ol class="breadcrumb mb-0" style="font-size:.78rem;">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.paie.index') }}">Bulletins de paie</a></li>
        <li class="breadcrumb-item active">{{ $paie->numero_bulletin ?? $paie->label }}</li>
    </ol>
@endsection

@push('styles')
<style>
.bulletin-container { max-width: 1180px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; }
.bulletin-header { display: grid; grid-template-columns: 35% 30% 35%; border-bottom: 2px solid #1e293b; }
.bulletin-header > div { padding: .75rem 1rem; }
.bulletin-employer { border-right: 1px solid #cbd5e1; }
.bulletin-employer h3 { font-size: 1.4rem; margin: 0; font-weight: 800; letter-spacing: .03em; color: #1e293b; }
.bulletin-employer .agency { font-size: .65rem; color: #64748b; line-height: 1.3; margin-top: .25rem; }
.bulletin-employer .meta { font-size: .72rem; line-height: 1.5; margin-top: .5rem; }
.bulletin-title { text-align: center; font-size: 1.5rem; font-weight: 800; color: #1e293b; letter-spacing: .15em; padding: .5rem; border-right: 1px solid #cbd5e1; display: flex; flex-direction: column; justify-content: center; }
.bulletin-title .period { font-size: .8rem; font-weight: 500; color: #475569; letter-spacing: 0; margin-top: .25rem; }
.bulletin-employee { font-size: .75rem; line-height: 1.6; }
.bulletin-employee table { width: 100%; }
.bulletin-employee td { padding: .1rem .2rem; vertical-align: top; }
.bulletin-employee td:first-child { color: #64748b; font-weight: 500; }
.bulletin-employee .name { font-size: 1.05rem; font-weight: 700; color: #1e293b; }
.bulletin-grid { width: 100%; border-collapse: collapse; font-size: .72rem; }
.bulletin-grid th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: .35rem; text-align: center; font-weight: 600; font-size: .68rem; color: #334155; }
.bulletin-grid td { border: 1px solid #e2e8f0; padding: .25rem .4rem; vertical-align: middle; }
.bulletin-grid td.num { text-align: right; font-variant-numeric: tabular-nums; }
.bulletin-grid td.code { text-align: center; color: #64748b; font-weight: 600; }
.bulletin-grid tr.group-row td { background: #eff6ff; font-weight: 700; color: #1e3a8a; font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; padding: .35rem .5rem; }
.bulletin-grid tr.subtotal td { background: #fffbeb; font-weight: 700; color: #92400e; }
.bulletin-grid tr.total td { background: #1e293b; color: #fff; font-weight: 800; }
.bulletin-foot { display: grid; grid-template-columns: 1fr 1fr; border-top: 2px solid #1e293b; }
.bulletin-foot > div { padding: .75rem 1rem; font-size: .72rem; }
.bulletin-foot .cumul-section table { width: 100%; font-size: .72rem; }
.bulletin-foot .cumul-section th { background: #f8fafc; padding: .2rem; text-align: right; color: #64748b; }
.bulletin-foot .cumul-section td { padding: .2rem; text-align: right; font-variant-numeric: tabular-nums; }
.bulletin-foot .visas { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; border-left: 1px solid #cbd5e1; }
.bulletin-foot .visa-box { border: 1px solid #cbd5e1; min-height: 90px; padding: .35rem; text-align: center; font-size: .65rem; color: #64748b; }
.net-a-payer-banner { background: linear-gradient(135deg,#059669,#0891B2); color: white; padding: .85rem 1.25rem; font-size: 1.1rem; font-weight: 700; text-align: right; }
.net-a-payer-banner .amount { font-size: 1.8rem; font-weight: 800; }
@media print {
    .no-print { display: none !important; }
    .bulletin-container { border: none; max-width: none; }
}
</style>
@endpush

@section('content')
{{-- Barre d'actions (hors impression) --}}
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <a href="{{ route('rh.paie.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-light"><i class="fas fa-print me-1"></i> Imprimer</button>
        <a href="{{ route('rh.paie.pdf', $paie) }}" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> PDF</a>
        @if($paie->statut == 0)
            <form action="{{ route('rh.paie.valider', $paie) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Valider</button>
            </form>
        @endif
        <a href="{{ route('rh.paie.edit', $paie) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
    </div>
</div>

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

<div class="bulletin-container">

    {{-- ─── EN-TÊTE ─────────────────────────────── --}}
    <div class="bulletin-header">
        <div class="bulletin-employer">
            <h3>OPTIMIZE ERP</h3>
            <div class="agency">Yubile Technologie · Libreville, Gabon</div>
            <div class="meta">
                BP : 3403 Libreville · Tél : 01-76-48-48<br>
                Matricule CNSS : 001-0185785-0<br>
                Matricule CNAMGS : 1015-0001646<br>
                Site : www.yubile.com
            </div>
        </div>
        <div class="bulletin-title">
            BULLETIN DE PAIE
            <div class="period">
                Période du {{ $paie->debut?->format('d/m/y') }} au {{ $paie->fin?->format('d/m/y') }}
            </div>
            @if($paie->numero_bulletin)
                <div class="period" style="font-weight:600;">N° {{ $paie->numero_bulletin }}</div>
            @endif
        </div>
        <div class="bulletin-employee">
            <div class="name">
                {{ $emp?->sexe === 'F' ? 'Mme' : 'M' }} {{ $emp?->noms }} {{ $emp?->prenoms }}
            </div>
            <table>
                <tr><td>Matricule</td><td><strong>{{ $emp?->matricule ?? '—' }}</strong></td></tr>
                <tr><td>Fonction</td><td>{{ $emp?->poste ?? '—' }}</td></tr>
                <tr><td>Matricule CNSS</td><td>{{ $emp?->matricule_cnss ?? '—' }}</td></tr>
                <tr><td>Matricule CNAMGS</td><td>{{ $emp?->matricule_cnamgs ?? '—' }}</td></tr>
                <tr><td>R.I.B.</td><td>{{ $paie->compte_bancaire ?? $emp?->iban ?? '—' }}</td></tr>
                <tr><td>Nature du contrat</td><td>{{ $emp?->type_contrat ?? '—' }}</td></tr>
                <tr><td>Date d'embauche</td><td>{{ $emp?->date_embauche?->format('d/m/y') ?? '—' }}</td></tr>
                <tr><td>Ancienneté</td><td>{{ $ancienneteLib }}</td></tr>
                <tr><td>Sit. familiale</td><td>{{ $emp?->situation_matrimoniale ?? '—' }} · {{ $emp?->nombre_enfants ?? 0 }} enfant(s)</td></tr>
                <tr><td>Nb parts</td><td><strong>{{ number_format($paie->part_impots ?? $emp?->parts_fiscales ?? 1, 2, ',', ' ') }}</strong></td></tr>
            </table>
        </div>
    </div>

    {{-- ─── TABLEAU PRINCIPAL ─────────────────────────────── --}}
    <table class="bulletin-grid">
        <thead>
            <tr>
                <th style="width:50px;">N°</th>
                <th style="text-align:left;">Désignation</th>
                <th style="width:65px;">Nombre</th>
                <th style="width:90px;">Base</th>
                <th style="width:55px;">Taux</th>
                <th style="width:90px;">Gain</th>
                <th style="width:90px;">Retenue</th>
                <th style="width:55px;">Taux pat.</th>
                <th style="width:90px;">Part patronale</th>
            </tr>
        </thead>
        <tbody>
            @if($paie->salaire_base > 0)
                <tr class="group-row"><td colspan="9">Rémunérations de base</td></tr>
                <tr>
                    <td class="code">1000</td>
                    <td>Salaire de base</td>
                    <td class="num">30,00</td>
                    <td class="num">{{ $fmt($paie->salaire_base) }}</td>
                    <td class="num">—</td>
                    <td class="num"><strong>{{ $fmt($paie->salaire_base) }}</strong></td>
                    <td></td><td></td><td></td>
                </tr>
                @if($paie->primes > 0)
                <tr><td class="code">1500</td><td>Primes</td><td></td><td></td><td class="num">—</td>
                    <td class="num">{{ $fmt($paie->primes) }}</td><td></td><td></td><td></td></tr>
                @endif
                @if($paie->heures_sup > 0)
                <tr><td class="code">1700</td><td>Heures supplémentaires</td><td></td><td></td><td class="num">—</td>
                    <td class="num">{{ $fmt($paie->heures_sup) }}</td><td></td><td></td><td></td></tr>
                @endif
                @if($paie->indemnites > 0)
                <tr><td class="code">5000</td><td>Indemnités diverses</td><td></td><td></td><td class="num">—</td>
                    <td class="num">{{ $fmt($paie->indemnites) }}</td><td></td><td></td><td></td></tr>
                @endif
            @endif

            {{-- Rubriques calculées, groupées --}}
            @foreach($lignesByGroupe as $groupeLib => $rubGroupe)
                <tr class="group-row"><td colspan="9">{{ $groupeLib }}</td></tr>
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
                        <td>{{ $r->libelle }}@if($r->base_calcul_libelle) <small class="text-muted">({{ $r->base_calcul_libelle }})</small>@endif</td>
                        <td></td>
                        <td class="num">{{ $fmt($base) }}</td>
                        <td class="num">{{ $taux > 0 && !$isPatronale ? number_format($taux, 2, ',', ' ') : '—' }}</td>
                        <td class="num">{{ $isGain ? $fmt($montant) : '' }}</td>
                        <td class="num">{{ !$isGain && !$isPatronale ? $fmt($montant) : '' }}</td>
                        <td class="num">{{ $isPatronale && $taux > 0 ? number_format($taux, 2, ',', ' ') : '' }}</td>
                        <td class="num">{{ $isPatronale ? $fmt($montant) : '' }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Totaux --}}
            <tr class="subtotal">
                <td colspan="2"><strong>Total Brut</strong></td>
                <td></td><td></td><td></td>
                <td class="num"><strong>{{ $fmt($paie->brut) }}</strong></td>
                <td></td><td></td><td></td>
            </tr>
            <tr class="subtotal">
                <td colspan="2"><strong>Total Cotisations & Retenues</strong></td>
                <td></td><td></td><td></td><td></td>
                <td class="num"><strong>{{ $fmt($totalCotSal + (float)$paie->irpp + (float)$paie->retenues + (float)$paie->avances) }}</strong></td>
                <td></td>
                <td class="num"><strong>{{ $fmt($totalCotPat) }}</strong></td>
            </tr>
            <tr class="total">
                <td colspan="2"><strong>NET À PAYER</strong></td>
                <td></td><td></td><td></td>
                <td class="num" colspan="2"><strong>{{ $fmt($paie->net_a_payer) }} XAF</strong></td>
                <td></td><td></td>
            </tr>
        </tbody>
    </table>

    {{-- ─── PIED : CUMULS + VISAS ─────────────────────────────── --}}
    <div class="bulletin-foot">
        <div class="cumul-section">
            <strong style="font-size:.78rem;color:#1e293b;">Cumuls</strong>
            <table>
                <thead><tr><th></th><th>Période</th><th>Année (cumul)</th></tr></thead>
                <tbody>
                    <tr><th>Salaire brut</th><td>{{ $fmt($paie->brut) }}</td><td>{{ $fmt($paie->cumul_annuel_brut) }}</td></tr>
                    <tr><th>Net imposable</th><td>{{ $fmt($paie->net_imposable) }}</td><td>{{ $fmt($paie->cumul_annuel_net_imposable) }}</td></tr>
                    <tr><th>Charges salariales</th><td>{{ $fmt($totalCotSal + (float)$paie->irpp) }}</td><td>{{ $fmt($paie->cumul_annuel_cotisations_sal + $paie->cumul_annuel_irpp) }}</td></tr>
                    <tr><th>Charges patronales</th><td>{{ $fmt($totalCotPat) }}</td><td>{{ $fmt($paie->cumul_annuel_cotisations_pat) }}</td></tr>
                    <tr><th>Brut congés</th><td>{{ $fmt($paie->brut_conges_mois) }}</td><td>{{ $fmt($paie->brut_conges_cumul) }}</td></tr>
                    <tr><th>Nb jrs acquis</th><td>{{ number_format($paie->nb_jrs_acquis_mois ?? 0, 2, ',', ' ') }}</td><td>{{ number_format($paie->nb_jrs_acquis_annee ?? 0, 2, ',', ' ') }}</td></tr>
                    <tr><th>Reste à prendre / Acquis n-1</th><td>{{ number_format($paie->reste_a_prendre ?? 0, 2, ',', ' ') }}</td><td>{{ number_format($paie->acquis_n_moins_1 ?? 0, 2, ',', ' ') }}</td></tr>
                </tbody>
            </table>
        </div>
        <div class="visas">
            <div class="visa-box">Visa de l'employeur</div>
            <div class="visa-box">Visa de l'employé</div>
        </div>
    </div>

    {{-- ─── BANNIÈRE NET À PAYER ──────────────────── --}}
    <div class="net-a-payer-banner">
        <span style="font-size:.75rem;opacity:.9;">NET À PAYER · {{ $paie->debut?->translatedFormat('F Y') }}</span>
        <span class="amount ms-3">{{ $fmt($paie->net_a_payer) }} XAF</span>
    </div>

    <div style="font-size:.65rem;color:#64748b;padding:.5rem 1rem;text-align:center;">
        Pour vous aider à faire valoir vos droits, conservez ce bulletin de paie sans limitation de durée.
    </div>
</div>

{{-- Règlements (hors impression) --}}
@if($paie->payements()->count() > 0)
<div class="card data-card mt-4 no-print">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-money-bill-transfer me-2 text-success"></i> Règlements enregistrés</h6></div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Date</th><th>Mode</th><th>Référence</th><th class="text-end">Montant</th></tr></thead>
            <tbody>
            @foreach($paie->payements as $p)
                <tr>
                    <td>{{ $p->date_payement?->format('d/m/Y') }}</td>
                    <td><span class="badge bg-secondary">{{ $p->mode_libelle }}</span></td>
                    <td>{{ $p->reference_payement ?? '—' }}</td>
                    <td class="text-end fw-bold">{{ $fmt($p->montant) }} XAF</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
