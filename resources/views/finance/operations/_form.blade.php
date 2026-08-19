@php
    $isEdit = isset($operation) && $operation->exists;
    $type = old('type_operation', $operation->type_operation ?? $type ?? 'depense');
@endphp

<input type="hidden" name="type_operation" value="{{ $type }}">

<div class="card data-card">
    <div class="card-header">
        <strong>
            @if($type === 'depense')
                <i class="fas fa-arrow-down text-danger me-1"></i> Ordre de dépense
            @else
                <i class="fas fa-arrow-up text-success me-1"></i> Ordre de recette
            @endif
        </strong>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Exercice</label>
                <select name="exercice_id" class="form-select">
                    <option value="">—</option>
                    @foreach($exercices as $e)
                        <option value="{{ $e->id }}" @selected(old('exercice_id', $operation->exercice_id ?? '') == $e->id)>{{ $e->libelle ?? $e->exercice }}</option>
                    @endforeach
                </select>
            </div>
            @if($type === 'depense')
                <div class="col-md-8">
                    <label class="form-label">Ligne budgétaire (à engager) <span class="text-danger">*</span></label>
                    <select name="budget_ligne_id" class="form-select @error('budget_ligne_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($lignes as $l)
                            <option value="{{ $l->id }}" data-solde="{{ $l->solde_disponible }}"
                                    @selected(old('budget_ligne_id', $operation->budget_ligne_id ?? '') == $l->id)>
                                {{ $l->id_budgetligne }} — Solde dispo : {{ number_format($l->solde_disponible, 0, ',', ' ') }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Seules les lignes validées sont listées.</small>
                    @error('budget_ligne_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endif

            <div class="col-md-4">
                <label class="form-label">Date d'opération <span class="text-danger">*</span></label>
                <input type="date" name="date_operation" class="form-control"
                       value="{{ old('date_operation', $operation->date_operation?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mode de règlement</label>
                <select name="mode_reglement" class="form-select">
                    <option value="">—</option>
                    @foreach(['virement', 'cheque', 'especes', 'mobile_money'] as $m)
                        <option value="{{ $m }}" @selected(old('mode_reglement', $operation->mode_reglement ?? '') === $m)>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Référence règlement</label>
                <input type="text" name="reference_reglement" class="form-control" maxlength="100"
                       value="{{ old('reference_reglement', $operation->reference_reglement ?? '') }}"
                       placeholder="N° chèque, ref. virement…">
            </div>

            <div class="col-md-4">
                <label class="form-label">Type de tiers</label>
                <select name="tiers_type" class="form-select" id="tiers_type_sel">
                    <option value="">Aucun</option>
                    @if($type === 'depense')
                        <option value="fournisseur" @selected(old('tiers_type', $operation->tiers_type ?? '') === 'fournisseur')>Fournisseur</option>
                    @else
                        <option value="client" @selected(old('tiers_type', $operation->tiers_type ?? '') === 'client')>Client</option>
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tiers</label>
                <select name="tiers_id" class="form-select">
                    <option value="">—</option>
                    @if($type === 'depense')
                        @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}" @selected(old('tiers_id', $operation->tiers_id ?? '') == $f->id)>{{ $f->nom }}</option>
                        @endforeach
                    @else
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('tiers_id', $operation->tiers_id ?? '') == $c->id)>{{ $c->raison_sociale }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Facture rattachée (optionnel)</label>
                <select name="facture_id" class="form-select">
                    <option value="">—</option>
                    @foreach($factures->where('sens', $type) as $f)
                        <option value="{{ $f->id }}" @selected(old('facture_id', $operation->facture_id ?? '') == $f->id)>
                            {{ $f->numero }} — {{ number_format((float) $f->montant_ttc, 0, ',', ' ') }} XAF
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Objet <span class="text-danger">*</span></label>
                <input type="text" name="objet" class="form-control @error('objet') is-invalid @enderror"
                       value="{{ old('objet', $operation->objet ?? '') }}" required maxlength="500">
                @error('objet')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Montant <small class="text-muted">(auto si détails)</small></label>
                <input type="number" name="montant" step="0.01" min="0.01" id="op_montant"
                       class="form-control @error('montant') is-invalid @enderror"
                       value="{{ old('montant', $operation->montant ?? '') }}" required>
                @error('montant')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" class="form-control" rows="3" maxlength="2000">{{ old('commentaire', $operation->commentaire ?? '') }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- ═════════ TABLE DES DÉTAILS (rubriques) ═════════ --}}
<div class="card data-card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-list-ul me-1"></i> Détails (rubriques)</strong>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAjouterLigne">
                <i class="fas fa-plus me-1"></i> Ajouter une rubrique
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="rubriqueAvertissement" class="alert alert-warning d-none">
            <i class="fas fa-info-circle me-1"></i>
            Sélectionnez d'abord une ligne budgétaire pour voir les rubriques disponibles.
        </div>
        <div id="rubriqueVide" class="alert alert-info d-none">
            <i class="fas fa-info-circle me-1"></i>
            Aucune rubrique référencée pour cette ligne budgétaire.
            <a href="{{ route('finance.referentiels.rubriques.create') }}" target="_blank">Créer une rubrique →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" id="tableDetails">
                <thead>
                    <tr>
                        <th style="width:30%;">Rubrique</th>
                        <th style="width:25%;">Libellé</th>
                        <th style="width:10%;">Quantité</th>
                        <th style="width:14%;">Prix unitaire</th>
                        <th style="width:14%;">Montant</th>
                        <th style="width:7%;"></th>
                    </tr>
                </thead>
                <tbody id="detailsBody">
                    @if($isEdit && $operation->details)
                        @foreach($operation->details as $i => $d)
                            <tr class="ligne-detail">
                                <td>
                                    <select name="details[{{ $i }}][rubrique_id]" class="form-select form-select-sm rubrique-select">
                                        <option value="">— Aucune —</option>
                                        @if($d->rubrique)
                                            <option value="{{ $d->rubrique_id }}" selected>{{ $d->rubrique->code }} — {{ $d->rubrique->libelle }}</option>
                                        @endif
                                    </select>
                                </td>
                                <td><input type="text" name="details[{{ $i }}][libelle]" class="form-control form-control-sm" value="{{ $d->libelle }}" required></td>
                                <td><input type="number" step="0.01" min="0" name="details[{{ $i }}][quantite]" class="form-control form-control-sm qte" value="{{ $d->quantite }}"></td>
                                <td><input type="number" step="0.01" min="0" name="details[{{ $i }}][prix_unitaire]" class="form-control form-control-sm pu" value="{{ $d->prix_unitaire }}"></td>
                                <td class="text-end fw-bold montant-affiche">{{ number_format((float) $d->montant, 0, ',', ' ') }}</td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger btn-supprimer-ligne"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total des détails :</th>
                        <th class="text-end"><span id="totalDetails">0</span> XAF</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('finance.operations.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer' }}</button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const sensOp        = @json($type);
    const tbody         = document.getElementById('detailsBody');
    const btnAjouter    = document.getElementById('btnAjouterLigne');
    const totalAffiche  = document.getElementById('totalDetails');
    const opMontant     = document.getElementById('op_montant');
    const avertissement = document.getElementById('rubriqueAvertissement');
    const ruVide        = document.getElementById('rubriqueVide');
    @if($type === 'depense')
        const budgetLigneSel = document.querySelector('select[name="budget_ligne_id"]');
    @else
        const budgetLigneSel = null;
    @endif

    let rubriquesDispo = []; // chargées via AJAX
    let counter = {{ $isEdit ? $operation->details->count() : 0 }};

    function fmt(n) {
        return Number(n || 0).toLocaleString('fr-FR', {maximumFractionDigits: 0});
    }

    function recalculerLigne(tr) {
        const qte = parseFloat(tr.querySelector('.qte')?.value) || 0;
        const pu  = parseFloat(tr.querySelector('.pu')?.value) || 0;
        const mt  = qte * pu;
        const cellMt = tr.querySelector('.montant-affiche');
        if (cellMt) cellMt.textContent = fmt(mt);
        return mt;
    }

    function recalculerTotal() {
        let total = 0;
        tbody.querySelectorAll('tr.ligne-detail').forEach(tr => total += recalculerLigne(tr));
        totalAffiche.textContent = fmt(total);
        if (total > 0 && opMontant) opMontant.value = total.toFixed(2);
    }

    function ajouterLigne(rubriqueId = '', libelle = '', quantite = 1, pu = 0) {
        const i = counter++;
        const tr = document.createElement('tr');
        tr.className = 'ligne-detail';
        // Construction sécurisée DOM (jamais innerHTML pour les valeurs venant de la DB).
        const tdRub = document.createElement('td');
        const sel = document.createElement('select');
        sel.name = `details[${i}][rubrique_id]`;
        sel.className = 'form-select form-select-sm rubrique-select';
        // Option « aucune » : statique, créée via DOM
        const optNone = document.createElement('option');
        optNone.value = '';
        optNone.textContent = '— Aucune —';
        sel.appendChild(optNone);
        // Options rubriques : créées une par une avec textContent (anti-XSS)
        rubriquesDispo.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.id;
            opt.textContent = `${r.code} — ${r.libelle}`;
            if (String(r.id) === String(rubriqueId)) opt.selected = true;
            sel.appendChild(opt);
        });
        sel.addEventListener('change', (e) => {
            const r = rubriquesDispo.find(x => x.id == e.target.value);
            if (r) tr.querySelector('input[name$="[libelle]"]').value = r.libelle;
        });
        tdRub.appendChild(sel);

        const tdLib = document.createElement('td');
        const inpLib = document.createElement('input');
        inpLib.type = 'text';
        inpLib.name = `details[${i}][libelle]`;
        inpLib.className = 'form-control form-control-sm';
        inpLib.required = true;
        inpLib.value = libelle;
        tdLib.appendChild(inpLib);

        const tdQte = document.createElement('td');
        const inpQte = document.createElement('input');
        inpQte.type = 'number'; inpQte.step = '0.01'; inpQte.min = '0';
        inpQte.name = `details[${i}][quantite]`;
        inpQte.className = 'form-control form-control-sm qte';
        inpQte.value = quantite;
        inpQte.addEventListener('input', recalculerTotal);
        tdQte.appendChild(inpQte);

        const tdPu = document.createElement('td');
        const inpPu = document.createElement('input');
        inpPu.type = 'number'; inpPu.step = '0.01'; inpPu.min = '0';
        inpPu.name = `details[${i}][prix_unitaire]`;
        inpPu.className = 'form-control form-control-sm pu';
        inpPu.value = pu;
        inpPu.addEventListener('input', recalculerTotal);
        tdPu.appendChild(inpPu);

        const tdMt = document.createElement('td');
        tdMt.className = 'text-end fw-bold montant-affiche';
        tdMt.textContent = '0';

        const tdSup = document.createElement('td');
        const btnSup = document.createElement('button');
        btnSup.type = 'button';
        btnSup.className = 'btn btn-sm btn-outline-danger';
        btnSup.innerHTML = '<i class="fas fa-trash"></i>';
        btnSup.addEventListener('click', () => { tr.remove(); recalculerTotal(); });
        tdSup.appendChild(btnSup);

        tr.append(tdRub, tdLib, tdQte, tdPu, tdMt, tdSup);
        tbody.appendChild(tr);
        recalculerTotal();
    }

    async function chargerRubriques(budgetLigneId) {
        rubriquesDispo = [];
        if (!budgetLigneId) {
            avertissement.classList.remove('d-none');
            ruVide.classList.add('d-none');
            actualiserSelectsRubriques();
            return;
        }
        avertissement.classList.add('d-none');
        try {
            const url = `{{ url('/finance/referentiels/rubriques/par-budget-ligne') }}/${budgetLigneId}?sens=${sensOp}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('Erreur AJAX');
            const data = await res.json();
            rubriquesDispo = data.rubriques || [];
            if (rubriquesDispo.length === 0) {
                ruVide.classList.remove('d-none');
            } else {
                ruVide.classList.add('d-none');
            }
            actualiserSelectsRubriques();
        } catch (e) {
            console.error(e);
        }
    }

    function actualiserSelectsRubriques() {
        // Met à jour les options des selects rubrique-select des lignes existantes
        tbody.querySelectorAll('select.rubrique-select').forEach(sel => {
            const valActuel = sel.value;
            sel.innerHTML = '<option value="">— Aucune —</option>';
            rubriquesDispo.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = `${r.code} — ${r.libelle}`;
                if (String(r.id) === String(valActuel)) opt.selected = true;
                sel.appendChild(opt);
            });
        });
    }

    btnAjouter.addEventListener('click', () => ajouterLigne());

    // Listeners sur les lignes existantes (édition)
    tbody.querySelectorAll('tr.ligne-detail').forEach(tr => {
        tr.querySelector('.qte')?.addEventListener('input', recalculerTotal);
        tr.querySelector('.pu')?.addEventListener('input', recalculerTotal);
        tr.querySelector('.btn-supprimer-ligne')?.addEventListener('click', () => {
            tr.remove();
            recalculerTotal();
        });
    });

    if (budgetLigneSel) {
        budgetLigneSel.addEventListener('change', e => chargerRubriques(e.target.value));
        // Chargement initial si une ligne est déjà sélectionnée
        if (budgetLigneSel.value) chargerRubriques(budgetLigneSel.value);
        else avertissement.classList.remove('d-none');
    }

    recalculerTotal();
})();
</script>
@endpush
