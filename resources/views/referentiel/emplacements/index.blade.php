@extends('layouts.app')
@section('title', 'Emplacements de stockage')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item active">Emplacements</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-warehouse text-muted me-2"></i> Emplacements de stockage</h1>
        <p class="text-muted mb-0">Hiérarchie des magasins, zones, étagères et cases.</p>
    </div>
    <button class="btn btn-primary" data-emp-add><i class="fas fa-plus me-1"></i> Nouvel emplacement racine</button>
</div>

<div class="card data-card">
    <div class="card-body p-0">
        @if($racines->isEmpty())
            <p class="text-center text-muted py-5 mb-0">
                <i class="fas fa-warehouse fa-2x mb-2 d-block text-muted"></i>
                Aucun emplacement. Créez votre premier magasin.
            </p>
        @else
            <ul class="list-group list-group-flush">
                @include('referentiel.emplacements._noeud', ['nodes' => $racines, 'niveau' => 0])
            </ul>
        @endif
    </div>
</div>

{{-- ═════════ MODALE UNIVERSELLE (create + edit) ═════════ --}}
<div class="modal fade" id="modal-emplacement" tabindex="-1"><div class="modal-dialog modal-lg">
    <form method="POST" class="modal-content" id="form-emplacement">
        @csrf
        <input type="hidden" name="_method" value="POST" id="emp-method">
        <div class="modal-header">
            <h5 class="modal-title" id="emp-title">Nouvel emplacement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" id="emp-code" class="form-control" maxlength="30" required placeholder="MAG-01">
            </div>
            <div class="col-md-6">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="libelle" id="emp-libelle" class="form-control" required maxlength="255">
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" id="emp-type" class="form-select">
                    <option value="">—</option>
                    @foreach($types as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Emplacement parent</label>
                <select name="parent_id" id="emp-parent" class="form-select">
                    <option value="">— Racine</option>
                    @foreach($toutes as $e)<option value="{{ $e->id }}">{{ $e->chemin }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Responsable</label>
                <select name="responsable_id" id="emp-responsable" class="form-select">
                    <option value="">—</option>
                    @foreach($utilisateurs as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ordre</label>
                <input type="number" name="ordre" id="emp-ordre" class="form-control" value="0">
            </div>
            <div class="col-12">
                <label class="form-label">Adresse</label>
                <textarea name="adresse" id="emp-adresse" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" id="emp-description" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-12" id="emp-actif-wrap" style="display:none;">
                <div class="form-check">
                    <input type="hidden" name="actif" value="0">
                    <input type="checkbox" class="form-check-input" name="actif" id="emp-actif" value="1">
                    <label for="emp-actif" class="form-check-label">Emplacement actif</label>
                </div>
            </div>
        </div></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-primary" id="emp-submit">Créer</button>
        </div>
    </form>
</div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeUrl = @json(route('referentiel.emplacements.store'));
    const updateUrlTpl = @json(route('referentiel.emplacements.update', ['emplacement' => '__ID__']));
    const modal = new bootstrap.Modal(document.getElementById('modal-emplacement'));
    const form = document.getElementById('form-emplacement');

    function reset() {
        form.reset();
        document.getElementById('emp-parent').value = '';
        document.getElementById('emp-ordre').value = '0';
        document.getElementById('emp-actif-wrap').style.display = 'none';
        document.getElementById('emp-actif').checked = true;
    }
    function openCreate(parentId) {
        reset();
        form.action = storeUrl;
        document.getElementById('emp-method').value = 'POST';
        document.getElementById('emp-title').textContent = parentId ? 'Nouveau sous-emplacement' : 'Nouvel emplacement racine';
        document.getElementById('emp-submit').textContent = 'Créer';
        if (parentId) document.getElementById('emp-parent').value = String(parentId);
    }
    function openEdit(data) {
        reset();
        form.action = updateUrlTpl.replace('__ID__', String(data.id));
        document.getElementById('emp-method').value = 'PUT';
        document.getElementById('emp-title').textContent = 'Modifier — ' + data.libelle;
        document.getElementById('emp-submit').textContent = 'Enregistrer';
        document.getElementById('emp-code').value = data.code ?? '';
        document.getElementById('emp-libelle').value = data.libelle ?? '';
        document.getElementById('emp-type').value = data.type ?? '';
        document.getElementById('emp-parent').value = data.parent_id ? String(data.parent_id) : '';
        document.getElementById('emp-ordre').value = data.ordre ?? '0';
        document.getElementById('emp-responsable').value = data.responsable_id ? String(data.responsable_id) : '';
        document.getElementById('emp-adresse').value = data.adresse ?? '';
        document.getElementById('emp-description').value = data.description ?? '';
        document.getElementById('emp-actif-wrap').style.display = '';
        document.getElementById('emp-actif').checked = !!data.actif;
    }
    document.querySelectorAll('[data-emp-add]').forEach(btn => {
        btn.addEventListener('click', () => { openCreate(btn.dataset.parent || null); modal.show(); });
    });
    document.querySelectorAll('[data-emp-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            openEdit({
                id: btn.dataset.id, code: btn.dataset.code, libelle: btn.dataset.libelle,
                type: btn.dataset.type, parent_id: btn.dataset.parentId, ordre: btn.dataset.ordre,
                responsable_id: btn.dataset.responsableId,
                adresse: btn.dataset.adresse, description: btn.dataset.description,
                actif: btn.dataset.actif === '1',
            });
            modal.show();
        });
    });
});
</script>
@endsection
