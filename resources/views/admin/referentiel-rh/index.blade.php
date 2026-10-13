@extends('layouts.app')

@section('title', $meta['libelle'])

@php
    $groupe   = $meta['group'] ?? 'Référentiels RH';
    $filtres  = request()->only('q', 'statut');
    $aFiltre  = request()->filled('q') || request()->filled('statut');
@endphp

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">RH &amp; Paiement</li>
    <li class="breadcrumb-item">{{ $groupe }}</li>
    <li class="breadcrumb-item active">{{ $meta['libelle'] }}</li>
</ol>
@endsection

@push('styles')
<style>
    .ref-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; background:#EEF2FF; color:#4F46E5; font-size:1.1rem; flex-shrink:0; }
    .ref-stat { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); padding:.85rem 1rem; display:flex; align-items:center; gap:.75rem; }
    .ref-stat .val { font-size:1.35rem; font-weight:700; color:#1E293B; line-height:1; }
    .ref-stat .lbl { font-size:.75rem; color:#64748B; }
    @media (max-width: 575.98px) { .ref-stat { padding:.65rem .75rem; } .ref-stat .stats-icon { display:none; } .ref-stat .val { font-size:1.15rem; } }
    .ref-code { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:.78rem; background:#F1F5F9; color:#334155; padding:.15rem .5rem; border-radius:4px; }
    .ref-table td { padding-top:.7rem; padding-bottom:.7rem; }
    .ref-table .desc { font-size:.75rem; color:#94A3B8; max-width:520px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    @media (max-width: 767.98px) { .ref-table .desc { max-width:45vw; } .ref-table .ps-3 { padding-left:.75rem !important; } }
    .ref-actions { white-space:nowrap; }
    .ref-actions .btn { width:30px; height:30px; padding:0; display:inline-flex; align-items:center; justify-content:center; }
</style>
@endpush

@section('content')
{{-- ══ En-tête ══ --}}
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="ref-icon"><i class="fas {{ $meta['icon'] }}"></i></div>
        <div>
            <h1>{{ $meta['libelle'] }}</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">{{ $groupe }} · référentiel utilisé dans les formulaires RH</p>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-file-export me-1"></i> Exporter</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('admin.referentiel-rh.export', [$meta['type']] + $filtres + ['format' => 'xlsx']) }}"><i class="fas fa-file-excel me-2 text-success"></i>Excel (XLSX)</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.referentiel-rh.export', [$meta['type']] + $filtres + ['format' => 'csv']) }}"><i class="fas fa-file-csv me-2 text-primary"></i>CSV</a></li>
                @if($aFiltre)
                <li><hr class="dropdown-divider"></li>
                <li><span class="dropdown-item-text text-muted" style="font-size:.72rem;">Les filtres actifs sont appliqués</span></li>
                @endif
            </ul>
        </div>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#refImportModal"><i class="fas fa-file-import me-1"></i> Importer</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#refModal" onclick="refOpenCreate()"><i class="fas fa-plus me-1"></i> Ajouter</button>
    </div>
</div>

{{-- ══ Erreurs d'import ══ --}}
@if(session('import_erreurs'))
<div class="alert alert-warning" style="font-size:.85rem;">
    <strong><i class="fas fa-triangle-exclamation me-1"></i>Lignes en erreur dans le fichier importé</strong>
    <ul class="mb-0 mt-2" style="font-size:.82rem;">
        @foreach(session('import_erreurs') as $err)
            <li>Ligne <strong>{{ $err['ligne'] }}</strong> : {{ implode(' · ', $err['motifs']) }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ══ Indicateurs ══ --}}
<div class="row g-2 g-sm-3 mb-3">
    <div class="col-4"><div class="ref-stat"><div class="stats-icon bg-primary-soft"><i class="fas fa-list-ul"></i></div><div><div class="val">{{ $stats['total'] }}</div><div class="lbl">Entrées</div></div></div></div>
    <div class="col-4"><div class="ref-stat"><div class="stats-icon bg-success-soft"><i class="fas fa-circle-check"></i></div><div><div class="val">{{ $stats['actifs'] }}</div><div class="lbl">Actives</div></div></div></div>
    <div class="col-4"><div class="ref-stat"><div class="stats-icon bg-danger-soft"><i class="fas fa-circle-pause"></i></div><div><div class="val">{{ $stats['inactifs'] }}</div><div class="lbl">Inactives</div></div></div></div>
</div>

{{-- ══ Liste ══ --}}
<div class="card data-card">
    <div class="card-header">
        <form method="GET" class="d-flex flex-wrap align-items-center gap-2">
            <div class="input-group" style="max-width:340px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control border-start-0" placeholder="Rechercher un code ou un libellé…">
            </div>
            <select name="statut" class="form-select" style="width:auto; min-width:175px;" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="1" @selected(request('statut') === '1')>Actifs</option>
                <option value="0" @selected(request('statut') === '0')>Inactifs</option>
            </select>
            <button class="btn btn-outline-primary"><i class="fas fa-filter me-1"></i>Filtrer</button>
            @if($aFiltre)
                <a href="{{ route('admin.referentiel-rh.index', $meta['type']) }}" class="btn btn-link text-muted text-decoration-none"><i class="fas fa-xmark me-1"></i>Réinitialiser</a>
            @endif
            <span class="ms-auto text-muted small">{{ $items->total() }} résultat(s)</span>
        </form>
    </div>

    @if($items->isEmpty())
        <div class="empty-state">
            <i class="fas {{ $aFiltre ? 'fa-magnifying-glass' : $meta['icon'] }}"></i>
            @if($aFiltre)
                <p class="mb-3">Aucune entrée ne correspond à ces filtres.</p>
                <a href="{{ route('admin.referentiel-rh.index', $meta['type']) }}" class="btn btn-sm btn-outline-secondary">Réinitialiser les filtres</a>
            @else
                <p class="mb-3">Ce référentiel est vide.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#refModal" onclick="refOpenCreate()"><i class="fas fa-plus me-1"></i>Ajouter une entrée</button>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#refImportModal"><i class="fas fa-file-import me-1"></i>Importer un fichier</button>
                </div>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 ref-table">
                <thead style="background:#F8FAFC;">
                    <tr>
                        <th class="ps-3" style="width:130px;">Code</th>
                        <th>Libellé</th>
                        <th class="text-center d-none d-md-table-cell" style="width:80px;">Ordre</th>
                        <th style="width:90px;">Statut</th>
                        <th class="text-end pe-3" style="width:100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($items as $it)
                    <tr>
                        <td class="ps-3"><span class="ref-code">{{ $it->code }}</span></td>
                        <td>
                            <div class="fw-semibold" style="color:#1E293B;">{{ $it->libelle }}</div>
                            @if($it->description)<div class="desc" title="{{ $it->description }}">{{ $it->description }}</div>@endif
                        </td>
                        <td class="text-center text-muted d-none d-md-table-cell">{{ $it->ordre }}</td>
                        <td>
                            @if($it->statut == 1)
                                <span class="badge-status badge-actif">Actif</span>
                            @else
                                <span class="badge-status badge-brouillon">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end pe-3 ref-actions">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#refModal"
                                    onclick='refOpenEdit(@json($it))' title="Modifier"><i class="fas fa-pen" style="font-size:.75rem;"></i></button>
                            <form action="{{ route('admin.referentiel-rh.destroy', [$meta['type'], $it->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer « {{ addslashes($it->libelle) }} » ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash" style="font-size:.75rem;"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">{{ $items->links() }}</div>
        @endif
    @endif
</div>

{{-- ══ Modale ajout / édition ══ --}}
<div class="modal fade" id="refModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="refForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="refMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refTitle">Ajouter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="refCode" class="form-control text-uppercase" maxlength="50" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="libelle" id="refLibelle" class="form-control" required>
                        </div>
                        <div class="col-12" style="margin-top:.25rem;">
                            <small class="form-text text-muted">Code unique (ex : CDI, DEV_BACK). Espaces convertis en underscores.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="refDescription" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Ordre d'affichage</label>
                            <input type="number" name="ordre" id="refOrdre" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Statut</label>
                            <select name="statut" id="refStatut" class="form-select">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ══ Modale import ══ --}}
<div class="modal fade" id="refImportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('admin.referentiel-rh.import', $meta['type']) }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-import me-2"></i>Importer — {{ $meta['libelle'] }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info d-flex justify-content-between align-items-start gap-2" style="font-size:.82rem;">
                    <div>
                        Colonnes : <code>code</code>*, <code>libelle</code>*, <code>description</code>, <code>ordre</code>, <code>statut</code> (actif / inactif).
                        <div class="mt-1">Les entrées sont identifiées par leur <strong>code</strong>. Un fichier exporté depuis cette page peut être réimporté tel quel ; les cellules vides ne modifient pas les valeurs existantes.</div>
                    </div>
                    <a href="{{ route('admin.referentiel-rh.import.template', $meta['type']) }}" class="btn btn-sm btn-outline-primary text-nowrap"><i class="fas fa-download me-1"></i>Modèle CSV</a>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fichier <span class="text-danger">*</span></label>
                    <input type="file" name="fichier" class="form-control" required accept=".csv,.xlsx,text/csv,text/plain,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                    <div class="form-text"><strong>CSV</strong> (séparateur <code>;</code> ou <code>,</code>, UTF-8) ou <strong>Excel XLSX</strong> (1<sup>re</sup> feuille). Max 5 Mo.</div>
                </div>
                <div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" name="mettre_a_jour" value="1" id="refImportMaj"><label class="form-check-label" for="refImportMaj">Mettre à jour les entrées existantes (même code)</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="ignore_erreurs" value="1" id="refImportIgnore"><label class="form-check-label" for="refImportIgnore">Ignorer les lignes en erreur (importer uniquement les lignes valides)</label></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary"><i class="fas fa-upload me-1"></i>Importer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const REF_STORE_URL  = "{{ route('admin.referentiel-rh.store', $meta['type']) }}";
const REF_UPDATE_TPL = "{{ route('admin.referentiel-rh.update', [$meta['type'], '__ID__']) }}";
const REF_SINGULIER  = @json($meta['singulier']);

function refOpenCreate() {
    document.getElementById('refTitle').textContent = 'Ajouter — ' + REF_SINGULIER;
    document.getElementById('refForm').action = REF_STORE_URL;
    document.getElementById('refMethod').value = 'POST';
    document.getElementById('refCode').value = '';
    document.getElementById('refLibelle').value = '';
    document.getElementById('refDescription').value = '';
    document.getElementById('refOrdre').value = 0;
    document.getElementById('refStatut').value = 1;
}
function refOpenEdit(item) {
    document.getElementById('refTitle').textContent = 'Modifier — ' + item.libelle;
    document.getElementById('refForm').action = REF_UPDATE_TPL.replace('__ID__', item.id);
    document.getElementById('refMethod').value = 'PUT';
    document.getElementById('refCode').value = item.code;
    document.getElementById('refLibelle').value = item.libelle;
    document.getElementById('refDescription').value = item.description || '';
    document.getElementById('refOrdre').value = item.ordre;
    document.getElementById('refStatut').value = item.statut;
}
</script>
@endpush
@endsection
