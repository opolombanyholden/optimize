@extends('layouts.app')
@section('title', 'Référentiel — Critères d\'évaluation')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item active">Critères d'évaluation</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-star text-muted me-2"></i> Critères d'évaluation des prestataires</h1>
        <p class="text-muted mb-0">Thèmes et critères paramétrables (poids, échelle).</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary" data-theme-add><i class="fas fa-folder-plus me-1"></i> Thème</button>
        <button class="btn btn-primary" data-critere-add><i class="fas fa-plus me-1"></i> Critère</button>
    </div>
</div>

@foreach($themes as $t)
    <div class="card data-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                {{ $t->libelle }}
                <small class="text-muted">({{ $t->criteres->count() }} critères)</small>
                @unless($t->actif)<span class="badge bg-secondary ms-2">Inactif</span>@endunless
            </h6>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary"
                        data-critere-add data-theme-id="{{ $t->id }}"
                        title="Ajouter un critère à ce thème">
                    <i class="fas fa-plus"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary"
                        data-theme-edit
                        data-id="{{ $t->id }}"
                        data-libelle="{{ $t->libelle }}"
                        data-description="{{ $t->description }}"
                        data-ordre="{{ $t->ordre }}"
                        data-actif="{{ $t->actif ? '1' : '0' }}"
                        title="Modifier le thème">
                    <i class="fas fa-pen"></i>
                </button>
                @if($t->criteres->isEmpty())
                    <form action="{{ route('referentiel.evaluations.themes.destroy', $t) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer le thème « {{ $t->libelle }} » ?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </form>
                @else
                    <button class="btn btn-outline-danger" disabled title="Supprimez d'abord les critères de ce thème."><i class="fas fa-lock"></i></button>
                @endif
            </div>
        </div>
        <div class="table-responsive"><table class="table table-sm mb-0">
            <thead class="table-light"><tr>
                <th>Critère</th>
                <th class="text-center">Échelle</th>
                <th class="text-center">Poids</th>
                <th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
                @forelse($t->criteres as $c)
                    <tr>
                        <td>
                            <strong>{{ $c->libelle }}</strong>
                            @unless($c->actif)<span class="badge bg-secondary ms-1">Inactif</span>@endunless
                            @if($c->description)<br><small class="text-muted">{{ $c->description }}</small>@endif
                        </td>
                        <td class="text-center">{{ $c->echelle_min }}–{{ $c->echelle_max }}</td>
                        <td class="text-center">{{ $c->poids }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary"
                                        data-critere-edit
                                        data-id="{{ $c->id }}"
                                        data-libelle="{{ $c->libelle }}"
                                        data-description="{{ $c->description }}"
                                        data-theme-id="{{ $c->theme_id }}"
                                        data-echelle-min="{{ $c->echelle_min }}"
                                        data-echelle-max="{{ $c->echelle_max }}"
                                        data-poids="{{ $c->poids }}"
                                        data-ordre="{{ $c->ordre }}"
                                        data-actif="{{ $c->actif ? '1' : '0' }}"
                                        title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('referentiel.evaluations.criteres.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer le critère « {{ $c->libelle }} » ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Aucun critère dans ce thème.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
@endforeach

@if($criteresSansTheme->isNotEmpty())
    <div class="card data-card mb-3">
        <div class="card-header"><h6 class="mb-0">Critères sans thème</h6></div>
        <div class="table-responsive"><table class="table table-sm mb-0">
            <tbody>
                @foreach($criteresSansTheme as $c)
                    <tr>
                        <td>{{ $c->libelle }} <span class="badge bg-secondary ms-2">{{ $c->echelle_min }}–{{ $c->echelle_max }}</span></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary"
                                        data-critere-edit
                                        data-id="{{ $c->id }}"
                                        data-libelle="{{ $c->libelle }}"
                                        data-description="{{ $c->description }}"
                                        data-theme-id=""
                                        data-echelle-min="{{ $c->echelle_min }}"
                                        data-echelle-max="{{ $c->echelle_max }}"
                                        data-poids="{{ $c->poids }}"
                                        data-ordre="{{ $c->ordre }}"
                                        data-actif="{{ $c->actif ? '1' : '0' }}">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form action="{{ route('referentiel.evaluations.criteres.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
@endif

{{-- ═════════ MODALE THÈME (create + edit) ═════════ --}}
<div class="modal fade" id="modal-theme" tabindex="-1"><div class="modal-dialog">
    <form method="POST" class="modal-content" id="form-theme">
        @csrf
        <input type="hidden" name="_method" value="POST" id="th-method">
        <div class="modal-header">
            <h5 class="modal-title" id="th-title">Nouveau thème</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="th-libelle" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ordre</label>
                    <input type="number" name="ordre" id="th-ordre" class="form-control" value="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="th-description" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
                <div class="col-12" id="th-actif-wrap" style="display:none;">
                    <div class="form-check">
                        <input type="hidden" name="actif" value="0">
                        <input type="checkbox" class="form-check-input" name="actif" id="th-actif" value="1">
                        <label for="th-actif" class="form-check-label">Thème actif</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-primary" id="th-submit">Créer</button>
        </div>
    </form>
</div></div>

{{-- ═════════ MODALE CRITÈRE (create + edit) ═════════ --}}
<div class="modal fade" id="modal-critere" tabindex="-1"><div class="modal-dialog">
    <form method="POST" class="modal-content" id="form-critere">
        @csrf
        <input type="hidden" name="_method" value="POST" id="cr-method">
        <div class="modal-header">
            <h5 class="modal-title" id="cr-title">Nouveau critère</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="libelle" id="cr-libelle" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Thème</label>
                <select name="theme_id" id="cr-theme" class="form-select">
                    <option value="">— (sans thème)</option>
                    @foreach($themes as $t)<option value="{{ $t->id }}">{{ $t->libelle }}</option>@endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" id="cr-description" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-md-3">
                <label class="form-label">Échelle min</label>
                <input type="number" name="echelle_min" id="cr-echelle-min" class="form-control" value="1" min="0" max="100" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Échelle max</label>
                <input type="number" name="echelle_max" id="cr-echelle-max" class="form-control" value="5" min="1" max="100" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Poids</label>
                <input type="number" step="0.01" name="poids" id="cr-poids" class="form-control" value="1.00" min="0.01" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ordre</label>
                <input type="number" name="ordre" id="cr-ordre" class="form-control" value="0">
            </div>
            <div class="col-12" id="cr-actif-wrap" style="display:none;">
                <div class="form-check">
                    <input type="hidden" name="actif" value="0">
                    <input type="checkbox" class="form-check-input" name="actif" id="cr-actif" value="1">
                    <label for="cr-actif" class="form-check-label">Critère actif</label>
                </div>
            </div>
        </div></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-primary" id="cr-submit">Créer</button>
        </div>
    </form>
</div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ═══════ Thème ═══════
    const themeStoreUrl = @json(route('referentiel.evaluations.themes.store'));
    const themeUpdateTpl = @json(route('referentiel.evaluations.themes.update', ['theme' => '__ID__']));
    const themeModal = new bootstrap.Modal(document.getElementById('modal-theme'));

    function themeReset() {
        document.getElementById('form-theme').reset();
        document.getElementById('th-ordre').value = '0';
        document.getElementById('th-actif-wrap').style.display = 'none';
        document.getElementById('th-actif').checked = true;
    }
    function themeOpenCreate() {
        themeReset();
        document.getElementById('form-theme').action = themeStoreUrl;
        document.getElementById('th-method').value = 'POST';
        document.getElementById('th-title').textContent = 'Nouveau thème';
        document.getElementById('th-submit').textContent = 'Créer';
    }
    function themeOpenEdit(data) {
        themeReset();
        document.getElementById('form-theme').action = themeUpdateTpl.replace('__ID__', String(data.id));
        document.getElementById('th-method').value = 'PUT';
        document.getElementById('th-title').textContent = 'Modifier — ' + data.libelle;
        document.getElementById('th-submit').textContent = 'Enregistrer';
        document.getElementById('th-libelle').value = data.libelle ?? '';
        document.getElementById('th-description').value = data.description ?? '';
        document.getElementById('th-ordre').value = data.ordre ?? '0';
        document.getElementById('th-actif-wrap').style.display = '';
        document.getElementById('th-actif').checked = !!data.actif;
    }
    document.querySelectorAll('[data-theme-add]').forEach(btn => {
        btn.addEventListener('click', () => { themeOpenCreate(); themeModal.show(); });
    });
    document.querySelectorAll('[data-theme-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            themeOpenEdit({
                id: btn.dataset.id,
                libelle: btn.dataset.libelle,
                description: btn.dataset.description,
                ordre: btn.dataset.ordre,
                actif: btn.dataset.actif === '1',
            });
            themeModal.show();
        });
    });

    // ═══════ Critère ═══════
    const critereStoreUrl = @json(route('referentiel.evaluations.criteres.store'));
    const critereUpdateTpl = @json(route('referentiel.evaluations.criteres.update', ['critere' => '__ID__']));
    const critereModal = new bootstrap.Modal(document.getElementById('modal-critere'));

    function critereReset() {
        document.getElementById('form-critere').reset();
        document.getElementById('cr-echelle-min').value = '1';
        document.getElementById('cr-echelle-max').value = '5';
        document.getElementById('cr-poids').value = '1.00';
        document.getElementById('cr-ordre').value = '0';
        document.getElementById('cr-actif-wrap').style.display = 'none';
        document.getElementById('cr-actif').checked = true;
    }
    function critereOpenCreate(themeId) {
        critereReset();
        document.getElementById('form-critere').action = critereStoreUrl;
        document.getElementById('cr-method').value = 'POST';
        document.getElementById('cr-title').textContent = 'Nouveau critère';
        document.getElementById('cr-submit').textContent = 'Créer';
        if (themeId) document.getElementById('cr-theme').value = String(themeId);
    }
    function critereOpenEdit(data) {
        critereReset();
        document.getElementById('form-critere').action = critereUpdateTpl.replace('__ID__', String(data.id));
        document.getElementById('cr-method').value = 'PUT';
        document.getElementById('cr-title').textContent = 'Modifier — ' + data.libelle;
        document.getElementById('cr-submit').textContent = 'Enregistrer';
        document.getElementById('cr-libelle').value = data.libelle ?? '';
        document.getElementById('cr-description').value = data.description ?? '';
        document.getElementById('cr-theme').value = data.theme_id ? String(data.theme_id) : '';
        document.getElementById('cr-echelle-min').value = data.echelle_min ?? '1';
        document.getElementById('cr-echelle-max').value = data.echelle_max ?? '5';
        document.getElementById('cr-poids').value = data.poids ?? '1.00';
        document.getElementById('cr-ordre').value = data.ordre ?? '0';
        document.getElementById('cr-actif-wrap').style.display = '';
        document.getElementById('cr-actif').checked = !!data.actif;
    }
    document.querySelectorAll('[data-critere-add]').forEach(btn => {
        btn.addEventListener('click', () => {
            critereOpenCreate(btn.dataset.themeId || null);
            critereModal.show();
        });
    });
    document.querySelectorAll('[data-critere-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            critereOpenEdit({
                id: btn.dataset.id,
                libelle: btn.dataset.libelle,
                description: btn.dataset.description,
                theme_id: btn.dataset.themeId,
                echelle_min: btn.dataset.echelleMin,
                echelle_max: btn.dataset.echelleMax,
                poids: btn.dataset.poids,
                ordre: btn.dataset.ordre,
                actif: btn.dataset.actif === '1',
            });
            critereModal.show();
        });
    });
});
</script>
@endsection
