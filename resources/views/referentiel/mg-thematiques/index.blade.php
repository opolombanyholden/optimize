@extends('layouts.app')
@section('title', 'Thématiques MG')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item active">Thématiques MG</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-tags text-muted me-2"></i> Thématiques Moyens Généraux</h1>
        <p class="text-muted mb-0">Classification hiérarchique des dysfonctionnements et interventions.</p>
    </div>
    <button class="btn btn-primary" data-th-add>
        <i class="fas fa-plus me-1"></i> Nouvelle thématique racine
    </button>
</div>

<div class="card data-card">
    <div class="card-header py-2">
        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>
            Une thématique peut contenir des sous-thématiques. Suppression bloquée si utilisée par un dysfonctionnement ou une intervention.
        </small>
    </div>
    <div class="card-body p-0">
        @if($racines->isEmpty())
            <p class="text-center text-muted py-5 mb-0">
                <i class="fas fa-tag fa-2x mb-2 d-block text-muted"></i>
                Aucune thématique. Créez-en une.
            </p>
        @else
            <ul class="list-group list-group-flush">
                @include('referentiel.mg-thematiques._noeud', ['nodes' => $racines, 'niveau' => 0])
            </ul>
        @endif
    </div>
</div>

{{-- ═════════ MODALE UNIVERSELLE (create / edit) ═════════ --}}
<div class="modal fade" id="modal-thematique" tabindex="-1"><div class="modal-dialog">
    <form method="POST" class="modal-content" id="form-thematique">
        @csrf
        <input type="hidden" name="_method" value="POST" id="th-method">
        <div class="modal-header">
            <h5 class="modal-title" id="th-title">Nouvelle thématique</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="th-libelle" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Couleur</label>
                    <input type="color" name="couleur" id="th-couleur" class="form-control form-control-color" value="#6c757d">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Thématique parente</label>
                    <select name="parent_id" id="th-parent" class="form-select">
                        <option value="">— Racine (aucun parent)</option>
                        @foreach($toutes as $t)
                            <option value="{{ $t->id }}">{{ $t->chemin }}</option>
                        @endforeach
                    </select>
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
                        <label for="th-actif" class="form-check-label">Thématique active</label>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeUrl = @json(route('referentiel.mg-thematiques.store'));
    const updateUrlTpl = @json(route('referentiel.mg-thematiques.update', ['thematique' => '__ID__']));

    const modal = new bootstrap.Modal(document.getElementById('modal-thematique'));
    const form = document.getElementById('form-thematique');
    const method = document.getElementById('th-method');
    const title = document.getElementById('th-title');
    const submit = document.getElementById('th-submit');
    const actifWrap = document.getElementById('th-actif-wrap');
    const actifChk = document.getElementById('th-actif');

    function reset() {
        form.reset();
        document.getElementById('th-parent').value = '';
        document.getElementById('th-ordre').value = '0';
        document.getElementById('th-couleur').value = '#6c757d';
        actifWrap.style.display = 'none';
        actifChk.checked = true;
    }

    function openCreate(parentId) {
        reset();
        form.action = storeUrl;
        method.value = 'POST';
        title.textContent = parentId ? 'Nouvelle sous-thématique' : 'Nouvelle thématique racine';
        submit.textContent = 'Créer';
        if (parentId) document.getElementById('th-parent').value = String(parentId);
    }

    function openEdit(data) {
        reset();
        form.action = updateUrlTpl.replace('__ID__', String(data.id));
        method.value = 'PUT';
        title.textContent = 'Modifier — ' + data.libelle;
        submit.textContent = 'Enregistrer';
        document.getElementById('th-libelle').value = data.libelle ?? '';
        document.getElementById('th-couleur').value = data.couleur || '#6c757d';
        document.getElementById('th-parent').value = data.parent_id ? String(data.parent_id) : '';
        document.getElementById('th-ordre').value = data.ordre ?? '0';
        document.getElementById('th-description').value = data.description ?? '';
        actifWrap.style.display = '';
        actifChk.checked = !!data.actif;
    }

    document.querySelectorAll('[data-th-add]').forEach(btn => {
        btn.addEventListener('click', () => {
            openCreate(btn.dataset.parent || null);
            modal.show();
        });
    });
    document.querySelectorAll('[data-th-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            openEdit({
                id: btn.dataset.id,
                libelle: btn.dataset.libelle,
                couleur: btn.dataset.couleur,
                parent_id: btn.dataset.parentId,
                ordre: btn.dataset.ordre,
                description: btn.dataset.description,
                actif: btn.dataset.actif === '1',
            });
            modal.show();
        });
    });
});
</script>
@endsection
