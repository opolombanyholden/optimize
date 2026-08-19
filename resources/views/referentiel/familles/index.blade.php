@extends('layouts.app')
@section('title', 'Familles d\'articles')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item active">Familles d'articles</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-sitemap text-muted me-2"></i> Familles d'articles</h1>
        <p class="text-muted mb-0">Organisation hiérarchique des biens & services du catalogue.</p>
    </div>
    <button class="btn btn-primary" data-fam-add>
        <i class="fas fa-plus me-1"></i> Nouvelle famille racine
    </button>
</div>

<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>
            Clic droit ou boutons pour agir. Une famille avec produits <strong>actifs</strong> ne peut pas être supprimée.
        </small>
        <div class="d-flex gap-2 small">
            <span><span class="badge bg-success">A</span> Actifs</span>
            <span><span class="badge bg-secondary">T</span> Total</span>
        </div>
    </div>
    <div class="card-body p-0">
        @if($racines->isEmpty())
            <p class="text-center text-muted py-5 mb-0">
                <i class="fas fa-folder-open fa-2x mb-2 d-block text-muted"></i>
                Aucune famille. Créez votre première famille racine.
            </p>
        @else
            <ul class="list-group list-group-flush">
                @include('referentiel.familles._noeud', ['nodes' => $racines, 'niveau' => 0])
            </ul>
        @endif
    </div>
</div>

{{-- ═════════ MODALE UNIVERSELLE (create / edit) ═════════ --}}
<div class="modal fade" id="modal-famille" tabindex="-1"><div class="modal-dialog">
    <form method="POST" class="modal-content" id="form-famille">
        @csrf
        <input type="hidden" name="_method" value="POST" id="fam-method">
        <div class="modal-header">
            <h5 class="modal-title" id="fam-title">Nouvelle famille</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="fam-libelle" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Code interne</label>
                    <input type="text" name="code" id="fam-code" class="form-control" maxlength="30" placeholder="FAM-01">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Famille parente</label>
                    <select name="parent_id" id="fam-parent" class="form-select">
                        <option value="">— Racine (aucun parent)</option>
                        @foreach($toutes as $f)
                            <option value="{{ $f->id }}">{{ $f->chemin }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ordre</label>
                    <input type="number" name="ordre" id="fam-ordre" class="form-control" value="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="fam-description" class="form-control" rows="2" maxlength="1000"></textarea>
                </div>
                <div class="col-12" id="fam-actif-wrap" style="display:none;">
                    <div class="form-check">
                        <input type="hidden" name="actif" value="0">
                        <input type="checkbox" class="form-check-input" name="actif" id="fam-actif" value="1">
                        <label for="fam-actif" class="form-check-label">Famille active</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            <button class="btn btn-primary" id="fam-submit">Créer</button>
        </div>
    </form>
</div></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeUrl = @json(route('referentiel.familles.store'));
    const updateUrlTpl = @json(route('referentiel.familles.update', ['famille' => '__ID__']));

    const modal = new bootstrap.Modal(document.getElementById('modal-famille'));
    const form = document.getElementById('form-famille');
    const method = document.getElementById('fam-method');
    const title = document.getElementById('fam-title');
    const submit = document.getElementById('fam-submit');
    const actifWrap = document.getElementById('fam-actif-wrap');
    const actifChk = document.getElementById('fam-actif');

    function reset() {
        form.reset();
        document.getElementById('fam-parent').value = '';
        document.getElementById('fam-ordre').value = '0';
        actifWrap.style.display = 'none';
        actifChk.checked = true;
    }

    function openCreate(parentId) {
        reset();
        form.action = storeUrl;
        method.value = 'POST';
        title.textContent = parentId ? 'Nouvelle sous-famille' : 'Nouvelle famille racine';
        submit.textContent = 'Créer';
        if (parentId) document.getElementById('fam-parent').value = String(parentId);
    }

    function openEdit(data) {
        reset();
        form.action = updateUrlTpl.replace('__ID__', String(data.id));
        method.value = 'PUT';
        title.textContent = 'Modifier — ' + data.libelle;
        submit.textContent = 'Enregistrer';
        document.getElementById('fam-libelle').value = data.libelle ?? '';
        document.getElementById('fam-code').value = data.code ?? '';
        document.getElementById('fam-parent').value = data.parent_id ? String(data.parent_id) : '';
        document.getElementById('fam-ordre').value = data.ordre ?? '0';
        document.getElementById('fam-description').value = data.description ?? '';
        actifWrap.style.display = '';
        actifChk.checked = !!data.actif;
    }

    document.querySelectorAll('[data-fam-add]').forEach(btn => {
        btn.addEventListener('click', () => {
            openCreate(btn.dataset.parent || null);
            modal.show();
        });
    });
    document.querySelectorAll('[data-fam-edit]').forEach(btn => {
        btn.addEventListener('click', () => {
            openEdit({
                id: btn.dataset.id,
                libelle: btn.dataset.libelle,
                code: btn.dataset.code,
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
