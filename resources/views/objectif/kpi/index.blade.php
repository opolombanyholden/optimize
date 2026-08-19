@extends('layouts.app')
@section('title', 'KPI — Indicateurs')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('objectifs.dashboard') }}">Objectifs & KPI</a></li>
    <li class="breadcrumb-item active">Tableau de KPI</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #7C3AED;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#7C3AED,#5B21B6);"><i class="fas fa-chart-line"></i></span>
                Indicateurs (KPI)
            </h1>
            <p class="page-subtitle">{{ $kpis->count() }} KPI suivis</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#filtresKpi">
                <i class="fas fa-filter me-1"></i> Filtres
                @php
                    $filtresActifs = collect(['q','objectif_id','periodicite','tendance','etat'])
                        ->filter(fn($k) => request($k) !== null && request($k) !== '')->count();
                @endphp
                @if($filtresActifs > 0)<span class="badge bg-danger ms-1">{{ $filtresActifs }}</span>@endif
            </button>
            <button class="btn btn-intranet" style="background:linear-gradient(135deg,#7C3AED,#5B21B6);" data-bs-toggle="modal" data-bs-target="#modalKpi">
                <i class="fas fa-plus me-2"></i> Nouveau KPI
            </button>
        </div>
    </div>

    {{-- Panneau filtres --}}
    <div class="collapse mt-3 {{ $filtresActifs > 0 ? 'show' : '' }}" id="filtresKpi">
        <div class="contact-detail-card">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Recherche</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Titre, description…" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Objectif</label>
                    <select name="objectif_id" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        @foreach($objectifs as $o)<option value="{{ $o->id }}" @selected(request('objectif_id') == $o->id)>{{ \Illuminate\Support\Str::limit($o->titre, 30) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Périodicité</label>
                    <select name="periodicite" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach(['quotidien','hebdo','mensuel','trimestriel','annuel'] as $p)<option value="{{ $p }}" @selected(request('periodicite') === $p)>{{ ucfirst($p) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tendance</label>
                    <select name="tendance" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        <option value="hausse" @selected(request('tendance')==='hausse')>↑ Hausse</option>
                        <option value="stable" @selected(request('tendance')==='stable')>→ Stable</option>
                        <option value="baisse" @selected(request('tendance')==='baisse')>↓ Baisse</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">État d'atteinte</label>
                    <select name="etat" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="au_dessus" @selected(request('etat')==='au_dessus')>Au-dessus de la cible</option>
                        <option value="en_alerte" @selected(request('etat')==='en_alerte')>En alerte (&lt; 50%)</option>
                        <option value="sans_cible" @selected(request('etat')==='sans_cible')>Sans cible définie</option>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('objectifs.kpi.index') }}" class="btn btn-sm btn-light"><i class="fas fa-times me-1"></i> Réinitialiser</a>
                    <button class="btn btn-sm btn-dark"><i class="fas fa-search me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #7C3AED;"><div class="stat-label">Total</div><div class="stat-value" style="color:#7C3AED;">{{ $stats['total'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #16A34A;"><div class="stat-label">Au-dessus de la cible</div><div class="stat-value" style="color:#16A34A;">{{ $stats['au_dessus'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #DC2626;"><div class="stat-label">En alerte (&lt; 50%)</div><div class="stat-value" style="color:#DC2626;">{{ $stats['en_alerte'] }}</div></div></div>
        <div class="col-md-3"><div class="courrier-stats" style="border-left:4px solid #94A3B8;"><div class="stat-label">Sans cible définie</div><div class="stat-value" style="color:#94A3B8;">{{ $stats['sans_cible'] }}</div></div></div>
    </div>

    @if($kpis->count())
    <div class="row g-3">
        @foreach($kpis as $k)
        @php
            $progressColor = ! $k->valeur_cible ? '#94A3B8' : ($k->progression >= 90 ? '#16A34A' : ($k->progression >= 50 ? '#0891B2' : '#DC2626'));
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="form-card h-100" style="border-left:4px solid {{ $progressColor }};">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <a href="{{ route('objectifs.kpi.show', $k) }}" style="text-decoration:none;color:inherit;flex:1;">
                        <strong style="font-size:.88rem;color:#0F172A;">{{ $k->titre }}</strong>
                    </a>
                    <div class="dropdown">
                        <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                            <li><a class="dropdown-item" href="{{ route('objectifs.kpi.show', $k) }}"><i class="fas fa-chart-column"></i> Voir &amp; saisir</a></li>
                            <li><button class="dropdown-item" onclick='editKpi(@json($k))'><i class="fas fa-pen-to-square"></i> Modifier</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="{{ route('objectifs.kpi.destroy', $k) }}" method="POST" onsubmit="return confirm('Supprimer ce KPI ?');">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </form></li>
                        </ul>
                    </div>
                </div>

                @if($k->objectif)
                <div style="font-size:.7rem;color:#7C3AED;margin-bottom:.3rem;">
                    <i class="fas fa-bullseye me-1"></i> {{ $k->objectif->titre }}
                </div>
                @endif

                <div class="d-flex align-items-baseline gap-2 mb-2">
                    <div style="font-size:1.6rem;font-weight:800;color:{{ $progressColor }};">{{ rtrim(rtrim($k->valeur_actuelle, '0'), '.') }}</div>
                    @if($k->unite)<div style="font-size:.85rem;color:#64748B;">{{ $k->unite }}</div>@endif
                    @if($k->valeur_cible)
                    <div style="font-size:.7rem;color:#94A3B8;margin-left:auto;">/ cible {{ rtrim(rtrim($k->valeur_cible, '0'), '.') }}</div>
                    @endif
                </div>

                @if($k->valeur_cible)
                <div class="tache-progress-bar mb-1">
                    <div class="tache-progress-fill" style="width:{{ min($k->progression, 100) }}%;background:{{ $progressColor }};"></div>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.7rem;color:#64748B;">
                    <span><strong>{{ $k->progression }}%</strong> de la cible</span>
                    <span>
                        @if($k->tendance === 'hausse')<i class="fas fa-arrow-trend-up text-success"></i> Hausse
                        @elseif($k->tendance === 'baisse')<i class="fas fa-arrow-trend-down text-danger"></i> Baisse
                        @else<i class="fas fa-minus text-muted"></i> Stable @endif
                    </span>
                </div>
                @endif

                @if($k->valeurs->count())
                <div style="font-size:.65rem;color:#94A3B8;margin-top:.5rem;">
                    {{ $k->valeurs->count() }} mesure(s) · Dernière : {{ $k->valeurs->first()?->date_mesure?->format('d/m/Y') }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F5F3FF;"><i class="fas fa-chart-line" style="color:#7C3AED;"></i></div>
        <h3>Aucun KPI</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#7C3AED,#5B21B6);" data-bs-toggle="modal" data-bs-target="#modalKpi">
            <i class="fas fa-plus me-2"></i> Créer le premier
        </button>
    </div>
    @endif
</div>

{{-- Modale --}}
<div class="modal fade" id="modalKpi" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="kpiForm" method="POST" class="modal-content" action="{{ route('objectifs.kpi.store') }}">
            @csrf
            <input type="hidden" name="_method" id="kpiMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="kpiModalTitle">Nouveau KPI</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="kpiTitre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="kpiDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Objectif rattaché</label>
                        <select name="objectif_id" id="kpiObjectif" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($objectifs as $o)<option value="{{ $o->id }}">{{ $o->titre }}</option>@endforeach
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Périodicité</label>
                        <select name="periodicite" id="kpiPeriode" class="form-select">
                            <option value="quotidien">Quotidien</option>
                            <option value="hebdo">Hebdomadaire</option>
                            <option value="mensuel" selected>Mensuel</option>
                            <option value="trimestriel">Trimestriel</option>
                            <option value="annuel">Annuel</option>
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Valeur actuelle</label>
                        <input type="number" step="0.01" name="valeur_actuelle" id="kpiActuelle" class="form-control" value="0"></div>
                    <div class="col-md-4"><label class="form-label">Valeur cible</label>
                        <input type="number" step="0.01" name="valeur_cible" id="kpiCible" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Unité</label>
                        <input type="text" name="unite" id="kpiUnite" class="form-control" placeholder="%, F, jours, nb..."></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#7C3AED,#5B21B6);"><span id="kpiSubmitText">Créer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editKpi(k) {
    document.getElementById('kpiModalTitle').textContent = 'Modifier le KPI';
    document.getElementById('kpiTitre').value = k.titre || '';
    document.getElementById('kpiDesc').value = k.description || '';
    document.getElementById('kpiObjectif').value = k.objectif_id || '';
    document.getElementById('kpiPeriode').value = k.periodicite || 'mensuel';
    document.getElementById('kpiActuelle').value = k.valeur_actuelle || 0;
    document.getElementById('kpiCible').value = k.valeur_cible || '';
    document.getElementById('kpiUnite').value = k.unite || '';
    document.getElementById('kpiMethod').value = 'PUT';
    document.getElementById('kpiSubmitText').textContent = 'Enregistrer';
    document.getElementById('kpiForm').action = '/objectifs/kpi/' + k.id;
    new bootstrap.Modal(document.getElementById('modalKpi')).show();
}

document.getElementById('modalKpi')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('kpiModalTitle').textContent = 'Nouveau KPI';
    ['kpiTitre','kpiDesc','kpiCible','kpiUnite'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('kpiActuelle').value = '0';
    document.getElementById('kpiObjectif').value = '';
    document.getElementById('kpiPeriode').value = 'mensuel';
    document.getElementById('kpiMethod').value = 'POST';
    document.getElementById('kpiSubmitText').textContent = 'Créer';
    document.getElementById('kpiForm').action = '{{ route("objectifs.kpi.store") }}';
});
</script>
@endpush
@endsection
