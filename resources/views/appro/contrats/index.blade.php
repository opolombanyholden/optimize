@extends('layouts.app')

@section('title', 'Engagements fournisseurs')

@push('styles')
<style>
.ct-hero { display:grid; grid-template-columns:repeat(4,1fr); gap:.75rem; margin-bottom:1rem; }
@media(max-width:768px){ .ct-hero { grid-template-columns:repeat(2,1fr); } }
.ct-kpi { background:#fff; border:1px solid #E0DFDC; padding:1rem 1.15rem; }
.ct-kpi-lbl { font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; color:#666; font-weight:700; margin:0 0 .35rem; }
.ct-kpi-val { font-size:1.5rem; font-weight:800; color:#191919; margin:0; line-height:1; }
.ct-kpi.is-alert .ct-kpi-val { color:#DC2626; }
.ct-kpi.is-warn  .ct-kpi-val { color:#D97706; }
.ct-badge { display:inline-block; font-size:.65rem; text-transform:uppercase; font-weight:700; letter-spacing:.04em; padding:.15rem .5rem; }
.ct-badge.brouillon { background:#F1F5F9; color:#64748B; }
.ct-badge.actif { background:#DCFCE7; color:#137333; }
.ct-badge.expire { background:#FEE2E2; color:#DC2626; }
.ct-badge.resilie { background:#FEE2E2; color:#DC2626; }
.ct-badge.renouvele { background:#DBEAFE; color:#1E40AF; }
.ct-expir-soon { color:#D97706; font-weight:600; }
.ct-expir-past { color:#DC2626; font-weight:600; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-file-contract me-2" style="color:#0A66C2;"></i>Engagements fournisseurs</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Suivi des engagements pris avec les fournisseurs : contrats-cadres, prestations, abonnements et paiements récurrents.</p>
    </div>
    <a href="{{ route('appro.contrats.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Nouvel engagement</a>
</div>

<div class="ct-hero">
    <div class="ct-kpi"><p class="ct-kpi-lbl">Actifs</p><p class="ct-kpi-val">{{ $stats['actifs'] }}</p></div>
    <div class="ct-kpi {{ $stats['expiration_proche'] > 0 ? 'is-warn' : '' }}"><p class="ct-kpi-lbl">Expiration &lt; 60j</p><p class="ct-kpi-val">{{ $stats['expiration_proche'] }}</p></div>
    <div class="ct-kpi {{ $stats['expires'] > 0 ? 'is-alert' : '' }}"><p class="ct-kpi-lbl">Expirés</p><p class="ct-kpi-val">{{ $stats['expires'] }}</p></div>
    <div class="ct-kpi"><p class="ct-kpi-lbl">Montant TTC actif</p><p class="ct-kpi-val">{{ number_format($stats['montant_actif'], 0, ',', ' ') }}</p></div>
</div>

<form method="GET" class="card data-card mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Recherche référence/objet…"></div>
            <div class="col-md-2">
                <select name="statut" class="form-select form-select-sm">
                    <option value="">Tous statuts</option>
                    @foreach($statuts as $k => $l)<option value="{{ $k }}" @selected(request('statut') === $k)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    @foreach($types as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="fournisseur" class="form-select form-select-sm">
                    <option value="">Tous fournisseurs</option>
                    @foreach($fournisseurs as $f)<option value="{{ $f->id }}" @selected((int)request('fournisseur') === $f->id)>{{ $f->raison_sociale }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="expiration" class="form-select form-select-sm">
                    <option value="">— Expiration</option>
                    <option value="proche" @selected(request('expiration') === 'proche')>Bientôt (60j)</option>
                    <option value="expires" @selected(request('expiration') === 'expires')>Déjà expirés</option>
                </select>
            </div>
        </div>
        <div class="mt-2 d-flex gap-2">
            <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filtrer</button>
            <a href="{{ route('appro.contrats.index') }}" class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
        </div>
    </div>
</form>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Référence</th>
                    <th>Fournisseur</th>
                    <th>Objet</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th class="text-end">Montant TTC</th>
                    <th class="text-center">Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($contrats as $c)
                <tr>
                    <td><code>{{ $c->reference }}</code></td>
                    <td>{{ \Illuminate\Support\Str::limit($c->fournisseur->raison_sociale ?? '—', 28) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($c->objet, 40) }}</td>
                    <td><span class="badge bg-light text-dark">{{ $c->type_libelle }}</span></td>
                    <td style="font-size:.82rem;">
                        {{ $c->date_debut?->format('d/m/Y') }}
                        @if($c->date_fin)
                            → {{ $c->date_fin->format('d/m/Y') }}
                            @if($c->est_expire)<div class="ct-expir-past"><i class="fas fa-triangle-exclamation me-1"></i>Expiré</div>
                            @elseif($c->est_prochain_expiration)<div class="ct-expir-soon"><i class="fas fa-clock me-1"></i>{{ $c->jours_avant_expiration }}j restants</div>
                            @endif
                        @else
                            <span class="text-muted">→ indéterminée</span>
                        @endif
                    </td>
                    <td class="text-end">{{ $c->montant_ttc !== null ? number_format((float)$c->montant_ttc, 0, ',', ' ').' '.$c->devise : '—' }}</td>
                    <td class="text-center"><span class="ct-badge {{ $c->statut }}">{{ $c->statut_libelle }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('appro.contrats.show', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('appro.contrats.edit', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-file-contract d-block mb-2" style="font-size:1.5rem; opacity:.4;"></i>Aucun engagement enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $contrats->links() }}</div>
@endsection
