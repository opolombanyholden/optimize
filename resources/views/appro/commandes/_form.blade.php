@php $isEdit = isset($commande) && $commande->exists; @endphp

<div class="card data-card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Commande fournisseur</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">N° commande</label>
                <input type="text" name="numero_commande" class="form-control"
                       value="{{ old('numero_commande', $commande->numero_commande) }}"
                       placeholder="Auto — laissez vide"
                       @if($isEdit && $commande->numero_commande) readonly @endif>
            </div>
            <div class="col-md-12">
                <label class="form-label">Objet de la commande</label>
                <input type="text" name="objet" class="form-control @error('objet') is-invalid @enderror"
                       value="{{ old('objet', $commande->objet) }}"
                       maxlength="255" placeholder="Ex : Fournitures bureau — trimestre 1">
                @error('objet')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5">
                <label class="form-label">Fournisseur <small class="text-muted">(facultatif)</small></label>
                <select name="fournisseur_id" class="form-select @error('fournisseur_id') is-invalid @enderror">
                    <option value="">— À déterminer après consultation —</option>
                    @foreach($fournisseurs as $f)
                        <option value="{{ $f->id }}" @selected(old('fournisseur_id', $commande->fournisseur_id) == $f->id)>
                            {{ $f->raison_sociale ?: $f->nom }}
                            @if($f->code) · {{ $f->code }}@endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Laisser vide si plusieurs fournisseurs seront consultés — le choix se fera à la sélection d'un devis.</small>
                @error('fournisseur_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label">Date commande <span class="text-danger">*</span></label>
                <input type="date" name="date_commande" class="form-control" required
                       value="{{ old('date_commande', $commande->date_commande?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Livraison prévue</label>
                <input type="date" name="date_livraison_prevue" class="form-control"
                       value="{{ old('date_livraison_prevue', $commande->date_livraison_prevue?->format('Y-m-d') ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Mode de règlement</label>
                <select name="mode_reglement" class="form-select">
                    <option value="">—</option>
                    @foreach(\App\Models\CommandeFournisseur::MODES_REGLEMENT as $k => $v)
                        <option value="{{ $k }}" @selected(old('mode_reglement', $commande->mode_reglement) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Conditions</label>
                <input type="text" name="conditions" class="form-control"
                       value="{{ old('conditions', $commande->conditions) }}" placeholder="Ex : Livraison sur site">
            </div>
            <div class="col-12">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" class="form-control" rows="2">{{ old('commentaire', $commande->commentaire) }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- ═════════ LIGNES DE COMMANDE ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-list me-2"></i> Articles commandés</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-ligne">
            <i class="fas fa-plus me-1"></i> Ajouter un article
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:22%">Produit du référentiel</th>
                    <th>Désignation</th>
                    <th class="text-end" style="width:10%">Qté</th>
                    <th class="text-end" style="width:14%">Prix unit.</th>
                    <th class="text-end" style="width:14%">Montant</th>
                    <th style="width:5%"></th>
                </tr>
            </thead>
            <tbody id="tbody-lignes">
            @php $existantes = $isEdit ? $commande->lignes : collect(); @endphp
            @foreach($existantes as $i => $l)
                <tr class="ligne-row">
                    <input type="hidden" name="lignes[{{ $i }}][id]" value="{{ $l->id }}">
                    <input type="hidden" name="lignes[{{ $i }}][_delete]" value="0" class="input-delete">
                    <td>
                        <select name="lignes[{{ $i }}][produit_id]" class="form-select form-select-sm select-produit">
                            <option value="">— Libre —</option>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}" data-prix="{{ $p->prix_unitaire }}" data-lib="{{ $p->designation }}"
                                        @selected($l->produit_id == $p->id)>
                                    [{{ $p->code ?: '—' }}] {{ $p->designation }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="text" name="lignes[{{ $i }}][designation]" class="form-control form-control-sm" value="{{ $l->designation }}"></td>
                    <td><input type="number" step="0.001" min="0" name="lignes[{{ $i }}][quantite_commandee]" class="form-control form-control-sm text-end input-qte" value="{{ $l->quantite_commandee }}"></td>
                    <td><input type="number" step="0.01" min="0" name="lignes[{{ $i }}][prix_unitaire]" class="form-control form-control-sm text-end input-pu" value="{{ $l->prix_unitaire }}"></td>
                    <td class="text-end fw-semibold cell-montant">{{ number_format((float) $l->montant, 0, ',', ' ') }}</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger btn-remove-ligne"><i class="fas fa-times"></i></button></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="text-end">Total HT</th>
                    <th class="text-end fs-6" id="total-lignes">{{ number_format((float) ($commande->montant_ht ?? 0), 0, ',', ' ') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<template id="tpl-ligne-row">
    <tr class="ligne-row">
        <input type="hidden" name="lignes[__idx__][_delete]" value="0" class="input-delete">
        <td>
            <select name="lignes[__idx__][produit_id]" class="form-select form-select-sm select-produit">
                <option value="">— Libre —</option>
                @foreach($produits as $p)
                    <option value="{{ $p->id }}" data-prix="{{ $p->prix_unitaire }}" data-lib="{{ $p->designation }}">
                        [{{ $p->code ?: '—' }}] {{ $p->designation }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="text" name="lignes[__idx__][designation]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.001" min="0" name="lignes[__idx__][quantite_commandee]" class="form-control form-control-sm text-end input-qte" value="1"></td>
        <td><input type="number" step="0.01" min="0" name="lignes[__idx__][prix_unitaire]" class="form-control form-control-sm text-end input-pu" value="0"></td>
        <td class="text-end fw-semibold cell-montant">0</td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger btn-remove-ligne"><i class="fas fa-times"></i></button></td>
    </tr>
</template>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('appro.commandes.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Enregistrer' : 'Créer la commande (brouillon)' }}
    </button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbody-lignes');
    const tpl   = document.getElementById('tpl-ligne-row');
    const btnAdd = document.getElementById('btn-add-ligne');
    let idxCounter = tbody.querySelectorAll('.ligne-row').length;

    function recalcRow(row) {
        const q = parseFloat(row.querySelector('.input-qte')?.value || 0) || 0;
        const pu = parseFloat(row.querySelector('.input-pu')?.value || 0) || 0;
        const m = Math.round(q * pu * 100) / 100;
        const cell = row.querySelector('.cell-montant');
        if (cell) cell.textContent = new Intl.NumberFormat('fr-FR').format(m);
        recalcTotal();
    }
    function recalcTotal() {
        let total = 0;
        tbody.querySelectorAll('.ligne-row').forEach(r => {
            if (r.querySelector('.input-delete')?.value === '1') return;
            total += (parseFloat(r.querySelector('.input-qte')?.value || 0) || 0) * (parseFloat(r.querySelector('.input-pu')?.value || 0) || 0);
        });
        document.getElementById('total-lignes').textContent = new Intl.NumberFormat('fr-FR').format(Math.round(total * 100) / 100);
    }
    function ajouterLigne() {
        const clone = tpl.content.firstElementChild.cloneNode(true);
        const idx = idxCounter++;
        clone.querySelectorAll('[name]').forEach(el => el.setAttribute('name', el.getAttribute('name').replace('__idx__', idx)));
        tbody.appendChild(clone);
    }
    btnAdd?.addEventListener('click', ajouterLigne);

    // Auto-remplit désignation + prix quand un produit est choisi
    tbody.addEventListener('change', function (e) {
        if (!e.target.classList.contains('select-produit')) return;
        const opt = e.target.options[e.target.selectedIndex];
        const row = e.target.closest('.ligne-row');
        if (!opt || !opt.value) return;
        const desInput = row.querySelector('input[name*="[designation]"]');
        const puInput  = row.querySelector('.input-pu');
        if (desInput && !desInput.value) desInput.value = opt.dataset.lib || '';
        if (puInput && (!puInput.value || puInput.value === '0')) puInput.value = opt.dataset.prix || 0;
        recalcRow(row);
    });
    tbody.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-qte') || e.target.classList.contains('input-pu')) {
            recalcRow(e.target.closest('.ligne-row'));
        }
    });
    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-ligne');
        if (!btn) return;
        const row = btn.closest('.ligne-row');
        const idInput = row.querySelector('input[name*="[id]"]');
        if (idInput) {
            row.querySelector('.input-delete').value = '1';
            row.style.display = 'none';
        } else {
            row.remove();
        }
        recalcTotal();
    });
    recalcTotal();
});
</script>
@endpush
