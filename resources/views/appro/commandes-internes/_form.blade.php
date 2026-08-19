@csrf
<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Objet <span class="text-danger">*</span></label>
        <input type="text" name="objet" class="form-control" value="{{ old('objet', $commande->objet) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Date de besoin</label>
        <input type="date" name="date_besoin" class="form-control" value="{{ old('date_besoin', optional($commande->date_besoin)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label">Supérieur hiérarchique (N+1) <small class="text-muted">(facultatif)</small></label>
        <select name="superieur_id" class="form-select">
            <option value="">—</option>
            @foreach($utilisateurs as $u)
                <option value="{{ $u->id }}" @selected(old('superieur_id', $commande->superieur_id) == $u->id)>
                    {{ trim(($u->name ?? '') . ' ' . ($u->prenoms ?? '')) ?: $u->email }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Si renseigné : la demande sera validée par cette personne avant transmission à Appro. Sinon : la demande part directement en triage Appro (qui pourra la traiter en direct ou l'assigner à un N+1 après coup).</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Date de demande</label>
        <input type="date" name="date_demande" class="form-control" value="{{ old('date_demande', optional($commande->date_demande)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Justification</label>
        <textarea name="justification" class="form-control" rows="3">{{ old('justification', $commande->justification) }}</textarea>
    </div>
</div></div></div>

<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-list me-2"></i> Articles demandés</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-ligne"><i class="fas fa-plus me-1"></i> Ajouter</button>
    </div>
    <div class="table-responsive"><table class="table table-sm mb-0" id="tbl-lignes">
        <thead class="table-light"><tr>
            <th>Article catalogue <span class="text-danger">*</span></th>
            <th style="width:120px">Qté <span class="text-danger">*</span></th>
            <th style="width:80px">Unité</th>
            <th>Commentaire</th>
            <th style="width:60px"></th>
        </tr></thead>
        <tbody>
            @foreach(($commande->lignes ?? collect()) as $i => $l)
                @include('appro.commandes-internes._ligne', ['index' => $i, 'ligne' => $l])
            @endforeach
        </tbody>
    </table></div>
</div>

<div class="card data-card mb-3"><div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes</h6></div>
    <div class="card-body">
        <input type="file" name="pieces_jointes[]" class="form-control" multiple>
        @if($commande->exists && $commande->piecesJointes->isNotEmpty())
            <hr>@include('mg._partials.pieces-jointes', ['pieces' => $commande->piecesJointes])
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer en brouillon</button>
    <a href="{{ route('appro.commandes-internes.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>

<template id="tpl-ligne">
    @include('appro.commandes-internes._ligne', ['index' => '__IDX__', 'ligne' => null])
</template>

<script>
(function () {
    const tbody = document.querySelector('#tbl-lignes tbody');
    const btn = document.getElementById('btn-add-ligne');
    const tpl = document.getElementById('tpl-ligne');
    let idx = {{ ($commande->lignes ?? collect())->count() }};
    btn.addEventListener('click', () => {
        const frag = tpl.content.cloneNode(true);
        const currentIdx = String(idx++);
        // Ré-injecte l'index dans les attributs name= des inputs (safe : aucun HTML injecté)
        frag.querySelectorAll('[name*="__IDX__"]').forEach((el) => {
            el.setAttribute('name', el.getAttribute('name').replaceAll('__IDX__', currentIdx));
        });
        tbody.appendChild(frag);
    });
    tbody.addEventListener('click', (e) => {
        const rm = e.target.closest('.btn-remove-ligne');
        if (rm) rm.closest('tr').remove();
    });
    // Auto-remplissage de l'unité au changement de produit
    tbody.addEventListener('change', (e) => {
        if (!e.target.matches('.produit-select')) return;
        const opt = e.target.selectedOptions[0];
        const cell = e.target.closest('tr').querySelector('.unite-cell small');
        if (cell) cell.textContent = opt?.dataset?.unite || '';
    });
})();
</script>
