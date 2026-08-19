@php $isEdit = isset($ordre) && $ordre->exists; @endphp

<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-file-invoice me-2"></i> {{ $modele->libelle }}
            <span class="badge bg-{{ $modele->sens_couleur }} ms-2">
                @if($modele->sens === 'depense')<i class="fas fa-arrow-down me-1"></i>Dépense
                @else<i class="fas fa-arrow-up me-1"></i>Recette
                @endif
            </span>
        </h5>
        @if(!$isEdit)
        <a href="{{ route('finance.ordres.create') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-shuffle me-1"></i> Changer de modèle
        </a>
        @endif
    </div>
    <div class="card-body">
        {{-- Contexte : exercice + ligne budgétaire --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Exercice <span class="text-danger">*</span></label>
                <select name="exercice_id" id="exercice_id" class="form-select @error('exercice_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($exercices as $e)
                        <option value="{{ $e->id }}" @selected(old('exercice_id', $ordre->exercice_id) == $e->id)>
                            {{ $e->libelle ?? $e->exercice }} · {{ $e->statut_libelle }}
                        </option>
                    @endforeach
                </select>
                @error('exercice_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Ligne budgétaire <span class="text-danger">*</span></label>
                <select name="budget_ligne_id" id="budget_ligne_id" class="form-select @error('budget_ligne_id') is-invalid @enderror" required>
                    <option value="">— Sélectionnez d'abord un exercice —</option>
                </select>
                @error('budget_ligne_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Détermine les désignations disponibles pour les détails.</small>
            </div>
            <div class="col-md-12">
                <label class="form-label">Libellé (résumé)</label>
                <input type="text" name="libelle" class="form-control"
                       value="{{ old('libelle', $ordre->libelle) }}"
                       placeholder="Résumé court de l'ordre">
            </div>
        </div>
    </div>
</div>

{{-- ═════════ CHAMPS DYNAMIQUES DU MODÈLE ═════════ --}}
@if($modele->champs->isNotEmpty())
<div class="card data-card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-list-check me-2"></i> Informations de l'ordre</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($modele->champs as $c)
                @php
                    $val = old("donnees.{$c->code_champ}", $ordre->donnees_json[$c->code_champ] ?? $c->valeur_par_defaut);
                    $err = $errors->first("donnees.{$c->code_champ}");
                    // Champs auto-alimentés (lecture seule) :
                    //  - imputation via mapping_gl
                    //  - dotation_initiale / solde_precedent / nouveau_solde via convention code_champ
                    $autoParConvention = in_array($c->code_champ, ['dotation_initiale', 'solde_precedent', 'nouveau_solde'], true);
                    $isAuto = $c->mapping_gl === 'imputation' || $autoParConvention;
                    $extra = $isAuto ? ' readonly' : '';
                    $classAuto = $isAuto ? ' bg-light' : '';
                    $tooltipAuto = match (true) {
                        $c->mapping_gl === 'imputation' => 'Auto-rempli depuis la ligne budgétaire (id_codeanalytique)',
                        $c->code_champ === 'dotation_initiale' => 'Auto-rempli : budget planifié de la ligne',
                        $c->code_champ === 'solde_precedent'   => $modele->sens === 'depense'
                            ? 'Auto-rempli : solde disponible de la ligne avant cette opération'
                            : 'Auto-rempli : cumul des recettes déjà enregistrées sur la ligne',
                        $c->code_champ === 'nouveau_solde'     => $modele->sens === 'depense'
                            ? 'Calculé : solde précédent − montant'
                            : 'Calculé : solde précédent + montant',
                        default => null,
                    };
                @endphp
                <div class="col-md-{{ $c->largeur_col }}">
                    <label class="form-label">
                        {{ $c->label_personnalise }}
                        @if($c->obligatoire)<span class="text-danger">*</span>@endif
                        @if($isAuto)<span class="badge bg-secondary ms-1" title="{{ $tooltipAuto }}">auto</span>@endif
                    </label>
                    @switch($c->type_saisie)
                        @case('textarea')
                            <textarea name="donnees[{{ $c->code_champ }}]" class="form-control {{ $err ? 'is-invalid' : '' }}{{ $classAuto }}" rows="3"
                                      data-mapping-gl="{{ $c->mapping_gl }}" data-code-champ="{{ $c->code_champ }}"
                                      placeholder="{{ $c->placeholder }}"
                                      @if($c->obligatoire) required @endif{!! $extra !!}>{{ $val }}</textarea>
                            @break
                        @case('select')
                            <select name="donnees[{{ $c->code_champ }}]" class="form-select {{ $err ? 'is-invalid' : '' }}{{ $classAuto }}"
                                    data-mapping-gl="{{ $c->mapping_gl }}" data-code-champ="{{ $c->code_champ }}"
                                    @if($c->obligatoire) required @endif>
                                <option value="">— Sélectionner —</option>
                                @foreach(($c->options_json ?? []) as $k => $lbl)
                                    <option value="{{ $k }}" @selected($val == $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @break
                        @case('date')
                            <input type="date" name="donnees[{{ $c->code_champ }}]" class="form-control {{ $err ? 'is-invalid' : '' }}{{ $classAuto }}"
                                   data-mapping-gl="{{ $c->mapping_gl }}" data-code-champ="{{ $c->code_champ }}"
                                   value="{{ $val }}" @if($c->obligatoire) required @endif{!! $extra !!}>
                            @break
                        @case('number')
                            <input type="number" step="0.01" min="0" name="donnees[{{ $c->code_champ }}]" class="form-control {{ $err ? 'is-invalid' : '' }}{{ $classAuto }}"
                                   data-mapping-gl="{{ $c->mapping_gl }}" data-code-champ="{{ $c->code_champ }}"
                                   value="{{ $val }}" placeholder="{{ $c->placeholder ?: '0' }}"
                                   @if($c->obligatoire) required @endif{!! $extra !!}>
                            @break
                        @default
                            <input type="text" name="donnees[{{ $c->code_champ }}]" class="form-control {{ $err ? 'is-invalid' : '' }}{{ $classAuto }}"
                                   data-mapping-gl="{{ $c->mapping_gl }}" data-code-champ="{{ $c->code_champ }}"
                                   value="{{ $val }}" placeholder="{{ $c->placeholder }}"
                                   @if($c->obligatoire) required @endif{!! $extra !!}>
                    @endswitch
                    @if($err)<div class="invalid-feedback d-block">{{ $err }}</div>@endif
                    @if($isAuto && $tooltipAuto)
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>{{ $tooltipAuto }}.</small>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ═════════ RATTACHEMENT À UNE FACTURE (optionnel) ═════════ --}}
@if(isset($facturesRattachables) && $facturesRattachables->count() > 0)
<div class="card data-card mb-3">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-file-invoice me-2"></i>
            Facture {{ $modele->sens === 'depense' ? 'à régler' : 'à encaisser' }} (optionnel)
        </h6>
    </div>
    <div class="card-body">
        <select name="facture_id" class="form-select @error('facture_id') is-invalid @enderror">
            <option value="">— Aucune facture rattachée —</option>
            @foreach($facturesRattachables as $f)
                @php $solde = $f->solde_restant; @endphp
                <option value="{{ $f->id }}"
                        @selected(old('facture_id', $ordre->facture_id ?? '') == $f->id)
                        @disabled($solde <= 0.01)>
                    {{ $f->numero }} · {{ $f->tiers_libelle ?? '—' }}
                    · TTC : {{ number_format((float) $f->montant_ttc, 0, ',', ' ') }}
                    @if($solde > 0.01)
                        · Reste : {{ number_format($solde, 0, ',', ' ') }}
                    @else
                        · Soldée
                    @endif
                </option>
            @endforeach
        </select>
        @error('facture_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>
            Une facture peut être réglée par un ou plusieurs ordres. La liste ne montre que les factures {{ $modele->sens }} non clôturées.
        </small>
    </div>
</div>
@endif

{{-- ═════════ COMPTE DE TRÉSORERIE (débit / crédit) ═════════ --}}
@php
    $labelCompte = $modele->sens === 'depense' ? 'Compte à débiter' : 'Compte à créditer';
    $modeCourant = old('donnees.mode_reglement', $ordre->donnees_json['mode_reglement'] ?? null);
    $compteCourant = old('compte_id', $ordre->compte_id ?? '');
    $comptesParType = $comptes->groupBy('type');
@endphp
<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-wallet me-2"></i> {{ $labelCompte }}</h6>
        <small class="text-muted">Trésorerie : bancaire · caisse · électronique</small>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label">Compte <span class="text-danger">*</span> <small class="text-muted">requis à l'exécution</small></label>
                <select name="compte_id" id="compte_id" class="form-select @error('compte_id') is-invalid @enderror">
                    <option value="">— Sélectionner un compte —</option>
                    @foreach($comptesParType as $type => $comptesType)
                        <optgroup label="{{ \App\Models\Compte::TYPES[$type] ?? $type }}"
                                  data-modes="{{ implode(',', \App\Models\Compte::MODES_PAR_TYPE[$type] ?? []) }}">
                            @foreach($comptesType as $c)
                                <option value="{{ $c->id }}"
                                        data-type="{{ $c->type }}"
                                        data-modes="{{ implode(',', \App\Models\Compte::MODES_PAR_TYPE[$c->type] ?? []) }}"
                                        @selected($compteCourant == $c->id)>
                                    {{ $c->nom }}
                                    @if($c->rib) · {{ $c->rib }} @endif
                                    · Solde : {{ number_format((float) $c->solde, 0, ',', ' ') }} {{ $c->devise ?: 'XAF' }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('compte_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted" id="compte-info">
                    <i class="fas fa-info-circle me-1"></i>
                    Les comptes proposés sont filtrés selon le mode de règlement (numéraire → caisse ; chèque → bancaire ; virement → bancaire ou électronique).
                </small>
            </div>
        </div>
    </div>
</div>

{{-- ═════════ BÉNÉFICIAIRE / CLIENT ═════════ --}}
@php
    $labelUi   = $modele->sens === 'depense' ? 'Bénéficiaire' : 'Client';
    $benefSrc  = old('beneficiaire_source', $ordre->beneficiaire_source ?? '');
    $benefRef  = old('beneficiaire_ref_id', $ordre->beneficiaire_ref_id ?? '');
    $benefInfos = old('beneficiaire_infos', $ordre->beneficiaire_infos_json ?? []);
    // Sources autorisées selon sens
    $sourcesAutorisees = $modele->sens === 'depense'
        ? ['user' => 'Personnel interne', 'organisation' => 'Organisation (fournisseur)', 'externe' => 'Externe (saisie manuelle)']
        : ['contact' => 'Contact CRM', 'organisation' => 'Organisation (client)', 'externe' => 'Externe (saisie manuelle)'];
@endphp
<div class="card data-card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-user-tag me-2"></i> {{ $labelUi }}</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label small">Source</label>
            <div class="btn-group w-100" role="group">
                @foreach($sourcesAutorisees as $src => $lbl)
                    <input type="radio" class="btn-check" name="beneficiaire_source" id="bs-{{ $src }}" value="{{ $src }}" @checked($benefSrc === $src)>
                    <label class="btn btn-outline-primary" for="bs-{{ $src }}">{{ $lbl }}</label>
                @endforeach
            </div>
        </div>

        {{-- Sélecteur d'entité interne (user / contact / organisation) --}}
        <div class="benef-ref-block" data-visible-for="user contact organisation">
            <label class="form-label">Sélectionner un {{ strtolower($labelUi) }}</label>
            <select name="beneficiaire_ref_id" id="benef-ref-id" class="form-select">
                <option value="">— Choisir —</option>
                {{-- Groupe Users --}}
                <optgroup label="Personnel interne" data-source="user">
                    @foreach($usersBenef as $u)
                        <option value="{{ $u->id }}" data-source="user" @selected($benefSrc==='user' && $benefRef == $u->id)>
                            {{ trim(($u->prenoms ?? '') . ' ' . $u->name) }}
                        </option>
                    @endforeach
                </optgroup>
                {{-- Groupe Contacts --}}
                <optgroup label="Contacts CRM" data-source="contact">
                    @foreach($contactsBenef as $c)
                        <option value="{{ $c->id }}" data-source="contact" @selected($benefSrc==='contact' && $benefRef == $c->id)>
                            {{ trim(($c->prenoms ?? '') . ' ' . $c->nom) }}
                        </option>
                    @endforeach
                </optgroup>
                {{-- Groupe Organisations --}}
                <optgroup label="Organisations" data-source="organisation">
                    @foreach($orgsBenef as $o)
                        <option value="{{ $o->id }}" data-source="organisation" @selected($benefSrc==='organisation' && $benefRef == $o->id)>
                            [{{ $o->type }}] {{ $o->raison_sociale ?: $o->nom }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
        </div>

        {{-- Saisie manuelle (externe) --}}
        <div class="benef-ref-block mt-3" data-visible-for="externe">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small">Nom / raison sociale <span class="text-danger">*</span></label>
                    <input type="text" name="beneficiaire_infos[nom]" class="form-control"
                           value="{{ $benefInfos['nom'] ?? '' }}" placeholder="Ex : Kabou & Fils SARL">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Entité / structure</label>
                    <input type="text" name="beneficiaire_infos[entite]" class="form-control"
                           value="{{ $benefInfos['entite'] ?? '' }}" placeholder="Ex : Direction commerciale">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Téléphone</label>
                    <input type="text" name="beneficiaire_infos[telephone]" class="form-control"
                           value="{{ $benefInfos['telephone'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Email</label>
                    <input type="email" name="beneficiaire_infos[email]" class="form-control"
                           value="{{ $benefInfos['email'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">NIF</label>
                    <input type="text" name="beneficiaire_infos[nif]" class="form-control"
                           value="{{ $benefInfos['nif'] ?? '' }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label small">Adresse</label>
                    <input type="text" name="beneficiaire_infos[adresse]" class="form-control"
                           value="{{ $benefInfos['adresse'] ?? '' }}"
                           placeholder="Ex : BP : 3403 Centre-ville, Libreville/Gabon">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═════════ JUSTIFICATIFS ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Justificatifs (pièces jointes)</h6>
    </div>
    <div class="card-body">
        @if($isEdit && $ordre->piecesJointes && $ordre->piecesJointes->isNotEmpty())
            <div class="mb-3">
                <h6 class="small text-muted mb-2">Justificatifs déjà attachés</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($ordre->piecesJointes as $pj)
                        @php
                            $url  = asset('storage/' . ltrim($pj->fichier, '/'));
                            $ext  = strtolower(pathinfo($pj->nom_original ?? $pj->fichier, PATHINFO_EXTENSION));
                            $isImg = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                            $isPdf = $ext === 'pdf';
                        @endphp
                        <div class="border rounded p-2 d-flex align-items-center gap-2" style="max-width:280px;">
                            @if($isImg)
                                <img src="{{ $url }}" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:.25rem;">
                            @elseif($isPdf)
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            @else
                                <i class="fas fa-file fa-2x text-muted"></i>
                            @endif
                            <div class="flex-grow-1 small">
                                <div class="text-truncate" title="{{ $pj->nom_original }}">{{ $pj->nom_original }}</div>
                                <a href="{{ $url }}" target="_blank" class="text-decoration-none small">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </div>
                            <form action="{{ route('finance.ordres.justificatifs.destroy', [$ordre, $pj]) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ce justificatif ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-link text-danger p-0" title="Supprimer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <label class="form-label small">Ajouter des fichiers (PDF, images, DOC, XLS — 20 Mo max chacun)</label>
        <input type="file" id="justificatifs-input" name="justificatifs[]" class="form-control" multiple
               accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.zip">
        <small class="text-muted">Les images et PDF seront prévisualisables sur la fiche de l'ordre.</small>
        {{-- Prévisualisation des fichiers sélectionnés (avant soumission) --}}
        <div id="justificatifs-preview" class="d-flex flex-wrap gap-2 mt-3"></div>
    </div>
</div>

{{-- ═════════ DÉTAILS VENTILÉS (rubriques par ligne budgétaire) ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0"><i class="fas fa-table-list me-2"></i> Détail des {{ $modele->sens === 'depense' ? 'dépenses' : 'recettes' }}
            <span id="rubriques-info" class="badge bg-info-subtle text-info ms-2 d-none">
                <i class="fas fa-lightbulb me-1"></i>
                <span id="rubriques-count">0</span> rubrique(s) prédéfinie(s)
            </span>
        </h6>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-info d-none" id="btn-reload-rubriques"
                    title="Réinjecte les rubriques prédéfinies liées à la ligne budgétaire">
                <i class="fas fa-rotate me-1"></i> Recharger rubriques
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-detail">
                <i class="fas fa-plus me-1"></i> Ajouter une ligne
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" id="table-details">
            <thead class="table-light">
                <tr>
                    <th style="width:22%">Désignation</th>
                    <th>Libellé</th>
                    <th class="text-end" style="width:10%">Qté</th>
                    <th class="text-end" style="width:14%">Prix unitaire</th>
                    <th class="text-end" style="width:14%">Montant</th>
                    <th style="width:5%"></th>
                </tr>
            </thead>
            <tbody id="tbody-details">
            @php $existants = $isEdit ? $ordre->details : collect(); @endphp
            @foreach($existants as $i => $d)
                <tr class="detail-row">
                    <input type="hidden" name="details[{{ $i }}][id]" value="{{ $d->id }}">
                    <input type="hidden" name="details[{{ $i }}][_delete]" value="0" class="input-delete">
                    <td>
                        <select name="details[{{ $i }}][rubrique_id]" class="form-select form-select-sm select-rubrique">
                            <option value="">— Libre —</option>
                            @if($d->rubrique)
                                <option value="{{ $d->rubrique_id }}" selected>{{ $d->rubrique->libelle }}</option>
                            @endif
                        </select>
                    </td>
                    <td><input type="text" name="details[{{ $i }}][libelle]" class="form-control form-control-sm" value="{{ $d->libelle }}"></td>
                    <td><input type="number" step="0.01" min="0" name="details[{{ $i }}][quantite]" class="form-control form-control-sm text-end input-qte" value="{{ $d->quantite }}"></td>
                    <td><input type="number" step="0.01" min="0" name="details[{{ $i }}][prix_unitaire]" class="form-control form-control-sm text-end input-pu" value="{{ $d->prix_unitaire }}"></td>
                    <td class="text-end fw-semibold cell-montant">{{ number_format((float) $d->montant, 0, ',', ' ') }}</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger btn-remove-detail"><i class="fas fa-times"></i></button></td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="text-end">Total</th>
                    <th class="text-end fs-6" id="total-details">0</th>
                    <th></th>
                </tr>
                <tr id="row-coherence" class="d-none">
                    <td colspan="6">
                        <div id="alerte-coherence" class="alert py-2 mb-0 small d-none">
                            {{-- Rempli dynamiquement --}}
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer text-muted small">
        <i class="fas fa-info-circle me-1"></i>
        Les désignations disponibles dépendent de la ligne budgétaire sélectionnée et du sens de l'ordre.
        Configurez-les dans <a href="{{ route('finance.referentiels.rubriques.index') }}">Référentiel → Rubriques opérations</a>.
    </div>
</div>

{{-- Template ligne détail vide (injecté par JS) --}}
<template id="tpl-detail-row">
    <tr class="detail-row">
        <input type="hidden" name="details[__idx__][_delete]" value="0" class="input-delete">
        <td>
            <select name="details[__idx__][rubrique_id]" class="form-select form-select-sm select-rubrique">
                <option value="">— Libre —</option>
            </select>
        </td>
        <td><input type="text" name="details[__idx__][libelle]" class="form-control form-control-sm"></td>
        <td><input type="number" step="0.01" min="0" name="details[__idx__][quantite]" class="form-control form-control-sm text-end input-qte" value="1"></td>
        <td><input type="number" step="0.01" min="0" name="details[__idx__][prix_unitaire]" class="form-control form-control-sm text-end input-pu" value="0"></td>
        <td class="text-end fw-semibold cell-montant">0</td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger btn-remove-detail"><i class="fas fa-times"></i></button></td>
    </tr>
</template>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('finance.ordres.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
    <button type="submit" id="ordre-submit-btn" class="btn btn-primary">
        <span class="ordre-submit-label"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Enregistrer' : 'Créer l\'ordre (brouillon)' }}</span>
        <span class="ordre-submit-loading d-none">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Enregistrement en cours…
        </span>
    </button>
</div>

{{-- Overlay plein écran pendant l'upload (bloque la double-soumission) --}}
<div id="ordre-loading-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
     style="background:rgba(0,0,0,0.5);z-index:1080;">
    <div class="bg-white rounded p-4 text-center" style="min-width:280px;">
        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;" role="status"></div>
        <h6 class="mb-1">Enregistrement en cours…</h6>
        <div class="text-muted small">Les fichiers peuvent prendre quelques secondes.</div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const SENS = @json($modele->sens);
    const rubUrlBase = @json(url('finance/ordres/rubriques'));
    const selExercice = document.getElementById('exercice_id');
    const selBl       = document.getElementById('budget_ligne_id');
    const tbody       = document.getElementById('tbody-details');
    const tpl         = document.getElementById('tpl-detail-row');
    const btnAdd      = document.getElementById('btn-add-detail');
    let rubriquesActuelles = [];
    let idxCounter    = tbody.querySelectorAll('.detail-row').length;

    // ── Helper : reset d'un <select> avec un placeholder text-only (pas d'innerHTML) ──
    function resetSelectAvecPlaceholder(select, placeholderText) {
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = placeholderText;
        select.replaceChildren(placeholder);
    }

    // ── Charge les lignes budgétaires d'un exercice via l'endpoint existant ──
    async function chargerLignesBudgetaires(exerciceId, selectedId = null) {
        resetSelectAvecPlaceholder(selBl, '— Chargement… —');
        if (!exerciceId) {
            resetSelectAvecPlaceholder(selBl, "— Sélectionnez d'abord un exercice —");
            return;
        }
        try {
            const resp = await fetch(`/finance/api/exercices/${exerciceId}/budget-lignes`, { headers: { Accept: 'application/json' } });
            if (!resp.ok) throw new Error('endpoint absent');
            const data = await resp.json();
            resetSelectAvecPlaceholder(selBl, '— Sélectionner —');
            for (const l of data) {
                const opt = document.createElement('option');
                opt.value = l.id;
                const titre = l.titre || '';
                const lib   = l.libelle || l.commentaire || l.id_budgetligne;
                opt.textContent = (titre ? `[${titre}] ` : '') + lib
                    + (l.id_codeanalytique ? ` — ${l.id_codeanalytique}` : '');
                if (l.id_codeanalytique) opt.dataset.codeanalytique = l.id_codeanalytique;
                if (l.dotation_totale  != null) opt.dataset.dotationTotale  = l.dotation_totale;
                if (l.solde_disponible != null) opt.dataset.soldeDisponible = l.solde_disponible;
                if (l.cumul_recettes   != null) opt.dataset.cumulRecettes   = l.cumul_recettes;
                if (selectedId && String(selectedId) === String(l.id)) opt.selected = true;
                selBl.appendChild(opt);
            }
        } catch (e) {
            resetSelectAvecPlaceholder(selBl, '(chargement impossible — saisie manuelle)');
        }
        chargerRubriques();
    }

    // ── Charge les rubriques disponibles pour la BL sélectionnée ──
    async function chargerRubriques() {
        const blId = selBl.value;
        rubriquesActuelles = [];
        if (!blId) {
            appliquerImputationAuto('');
            appliquerChiffresBudgetaires(null, null);
            majBadgeRubriques();
            return applyRubriquesToAllRows();
        }
        try {
            const resp = await fetch(`${rubUrlBase}/${blId}/${SENS}`, { headers: { Accept: 'application/json' } });
            rubriquesActuelles = resp.ok ? await resp.json() : [];
        } catch (e) { rubriquesActuelles = []; }
        applyRubriquesToAllRows();
        // Auto-remplit imputation + chiffres budgétaires depuis l'option BL sélectionnée
        const opt = selBl.options[selBl.selectedIndex];
        appliquerImputationAuto(opt?.dataset?.codeanalytique || '');
        // Solde précédent : dépense = solde disponible ; recette = cumul recettes enregistrées
        const soldePrecDataset = SENS === 'depense' ? opt?.dataset?.soldeDisponible : opt?.dataset?.cumulRecettes;
        appliquerChiffresBudgetaires(
            opt?.dataset?.dotationTotale ? Number(opt.dataset.dotationTotale) : null,
            soldePrecDataset != null && soldePrecDataset !== '' ? Number(soldePrecDataset) : null
        );
        majBadgeRubriques();
        // Auto-injection : si aucune ligne de détail visible n'existe encore, on ajoute
        // automatiquement une ligne par rubrique prédéfinie (l'utilisateur n'aura plus
        // qu'à renseigner quantités et prix unitaires).
        if (rubriquesActuelles.length > 0 && aucuneLigneVisible()) {
            injecterRubriquesPredefinies();
        }
    }

    // ── UI du badge « N rubrique(s) prédéfinie(s) » + bouton recharger ──
    function majBadgeRubriques() {
        const badge = document.getElementById('rubriques-info');
        const cnt   = document.getElementById('rubriques-count');
        const btnReload = document.getElementById('btn-reload-rubriques');
        const n = rubriquesActuelles.length;
        if (!badge || !cnt) return;
        cnt.textContent = n;
        badge.classList.toggle('d-none', n === 0);
        if (btnReload) btnReload.classList.toggle('d-none', n === 0);
    }

    function aucuneLigneVisible() {
        return Array.from(tbody.querySelectorAll('.detail-row'))
            .every(r => r.querySelector('.input-delete')?.value === '1' || r.style.display === 'none');
    }

    // ── Injecte une ligne par rubrique disponible ──
    function injecterRubriquesPredefinies() {
        for (const r of rubriquesActuelles) {
            const clone = tpl.content.firstElementChild.cloneNode(true);
            const currentIdx = idxCounter++;
            clone.querySelectorAll('[name]').forEach(el => {
                el.setAttribute('name', el.getAttribute('name').replace('__idx__', currentIdx));
            });
            // Applique les rubriques dans le select puis pré-sélectionne la bonne
            const sel = clone.querySelector('.select-rubrique');
            applyRubriquesToRow(sel, r.id);
            // Recopie le libellé de la rubrique dans le champ libellé (pré-rempli)
            const inpLibelle = clone.querySelector('input[name*="[libelle]"]');
            if (inpLibelle && !inpLibelle.value) inpLibelle.value = r.libelle;
            tbody.appendChild(clone);
        }
        recalcTotal();
    }

    // ── Auto-remplit tout champ dynamique dont mapping_gl = imputation ──
    function appliquerImputationAuto(codeanalytique) {
        document.querySelectorAll('[data-mapping-gl="imputation"]').forEach(input => {
            input.value = codeanalytique;
        });
    }

    // ── Auto-remplit dotation_initiale + solde_precedent depuis les datasets de l'option BL ──
    function appliquerChiffresBudgetaires(dotationTotale, soldeDisponible) {
        document.querySelectorAll('[data-code-champ="dotation_initiale"]').forEach(input => {
            input.value = dotationTotale ?? '';
        });
        document.querySelectorAll('[data-code-champ="solde_precedent"]').forEach(input => {
            input.value = soldeDisponible ?? '';
        });
        recalculerNouveauSolde();
    }

    // ── Calcule NOUVEAU SOLDE = solde_precedent ± montant selon sens de l'ordre ──
    function recalculerNouveauSolde() {
        const soldePrec = parseFloat(document.querySelector('[data-code-champ="solde_precedent"]')?.value || 0) || 0;
        const montant   = parseFloat(document.querySelector('[data-code-champ="montant"]')?.value || 0) || 0;
        const nouveau   = SENS === 'depense' ? (soldePrec - montant) : (soldePrec + montant);
        document.querySelectorAll('[data-code-champ="nouveau_solde"]').forEach(input => {
            input.value = Math.round(nouveau * 100) / 100;
        });
    }

    // Recalcul automatique quand l'utilisateur change le MONTANT
    document.querySelectorAll('[data-code-champ="montant"]').forEach(input => {
        input.addEventListener('input', function () {
            recalculerNouveauSolde();
            recalcTotal(); // re-vérifie la cohérence total détails / montant
        });
    });

    // ── Filtre des comptes selon le MODE DE RÈGLEMENT ──
    const selCompte = document.getElementById('compte_id');
    const modeInput = document.querySelector('[data-code-champ="mode_reglement"]');
    function filtrerComptesParMode() {
        if (!selCompte) return;
        const mode = modeInput?.value || '';
        Array.from(selCompte.querySelectorAll('optgroup')).forEach(og => {
            const modesOg = (og.dataset.modes || '').split(',').filter(Boolean);
            const visible = !mode || modesOg.includes(mode);
            og.hidden = !visible;
            Array.from(og.querySelectorAll('option')).forEach(opt => opt.disabled = !visible);
        });
        // Si le compte sélectionné est masqué, on le désélectionne
        const optSel = selCompte.options[selCompte.selectedIndex];
        if (optSel && optSel.value !== '' && optSel.disabled) {
            selCompte.value = '';
        }
    }
    if (modeInput) {
        modeInput.addEventListener('change', filtrerComptesParMode);
        filtrerComptesParMode();
    }

    // ── Bloc bénéficiaire : masquer/afficher selon la source ──
    const benefRadios  = document.querySelectorAll('input[name="beneficiaire_source"]');
    const benefBlocks  = document.querySelectorAll('.benef-ref-block');
    const benefRefSel  = document.getElementById('benef-ref-id');
    function appliquerVisibiliteBenef() {
        const sel = document.querySelector('input[name="beneficiaire_source"]:checked')?.value;
        benefBlocks.forEach(b => {
            const target = (b.dataset.visibleFor || '').split(' ');
            b.style.display = (sel && target.includes(sel)) ? '' : 'none';
        });
        // Filtre les options du select selon la source (via optgroup data-source)
        if (benefRefSel) {
            Array.from(benefRefSel.querySelectorAll('optgroup')).forEach(og => {
                og.hidden = og.dataset.source !== sel;
            });
        }
    }
    benefRadios.forEach(r => r.addEventListener('change', appliquerVisibiliteBenef));
    appliquerVisibiliteBenef();

    // ── Preview des justificatifs sélectionnés (avant upload) ──
    const inputJustif   = document.getElementById('justificatifs-input');
    const previewJustif = document.getElementById('justificatifs-preview');
    const IMG_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (inputJustif && previewJustif) {
        inputJustif.addEventListener('change', function () {
            previewJustif.replaceChildren();
            for (const file of this.files) {
                const ext = (file.name.split('.').pop() || '').toLowerCase();
                const card = document.createElement('div');
                card.className = 'border rounded p-2 d-flex align-items-center gap-2';
                card.style.maxWidth = '260px';

                const iconWrap = document.createElement('div');
                if (IMG_EXTS.includes(ext)) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.alt = '';
                    img.style.width = '48px'; img.style.height = '48px';
                    img.style.objectFit = 'cover'; img.style.borderRadius = '.25rem';
                    // Libère l'URL une fois chargée (économie mémoire)
                    img.onload = () => URL.revokeObjectURL(img.src);
                    iconWrap.appendChild(img);
                } else {
                    const icon = document.createElement('i');
                    icon.className = (ext === 'pdf' ? 'fas fa-file-pdf fa-2x text-danger' : 'fas fa-file fa-2x text-muted');
                    iconWrap.appendChild(icon);
                }
                card.appendChild(iconWrap);

                const info = document.createElement('div');
                info.className = 'flex-grow-1 small overflow-hidden';
                const name = document.createElement('div');
                name.className = 'text-truncate fw-semibold';
                name.textContent = file.name;
                name.title = file.name;
                const size = document.createElement('div');
                size.className = 'text-muted';
                size.textContent = (file.size / 1024).toFixed(1) + ' Ko';
                info.appendChild(name);
                info.appendChild(size);
                card.appendChild(info);

                previewJustif.appendChild(card);
            }
        });
    }

    // ── Loading overlay au submit ──
    const form         = document.querySelector('form');
    const overlay      = document.getElementById('ordre-loading-overlay');
    const submitBtn    = document.getElementById('ordre-submit-btn');
    if (form && overlay && submitBtn) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return; // laisse le navigateur signaler les erreurs
            overlay.classList.remove('d-none');
            submitBtn.disabled = true;
            submitBtn.querySelector('.ordre-submit-label').classList.add('d-none');
            submitBtn.querySelector('.ordre-submit-loading').classList.remove('d-none');
        });
    }

    function applyRubriquesToRow(select, keepValue = null) {
        const currentValue = keepValue ?? select.value;
        resetSelectAvecPlaceholder(select, '— Libre —');
        for (const r of rubriquesActuelles) {
            const o = document.createElement('option');
            o.value = r.id;
            o.textContent = `[${r.code}] ${r.libelle}`;
            if (String(currentValue) === String(r.id)) o.selected = true;
            select.appendChild(o);
        }
    }
    function applyRubriquesToAllRows() {
        tbody.querySelectorAll('.select-rubrique').forEach(s => applyRubriquesToRow(s));
    }

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
        let aDesLignes = false;
        tbody.querySelectorAll('.detail-row').forEach(r => {
            if (r.querySelector('.input-delete')?.value === '1') return;
            aDesLignes = true;
            const q = parseFloat(r.querySelector('.input-qte')?.value || 0) || 0;
            const pu = parseFloat(r.querySelector('.input-pu')?.value || 0) || 0;
            total += q * pu;
        });
        total = Math.round(total * 100) / 100;
        document.getElementById('total-details').textContent = new Intl.NumberFormat('fr-FR').format(total);
        verifierCoherenceMontant(total, aDesLignes);
    }

    // ── Contrôle temps réel : le total des rubriques doit égaler MONTANT EN CHIFFRES ──
    function verifierCoherenceMontant(totalDetails, aDesLignes) {
        const row      = document.getElementById('row-coherence');
        const alerte   = document.getElementById('alerte-coherence');
        const inpMont  = document.querySelector('[data-code-champ="montant"]');
        if (!row || !alerte || !inpMont) return;

        // Aucun détail actif → aucune contrainte, on masque
        if (!aDesLignes) {
            row.classList.add('d-none');
            alerte.classList.add('d-none');
            inpMont.classList.remove('is-invalid', 'is-valid');
            return;
        }

        const montantSaisi = parseFloat(inpMont.value || 0) || 0;
        const ecart = Math.round((totalDetails - montantSaisi) * 100) / 100;

        row.classList.remove('d-none');
        alerte.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');

        const fmt = new Intl.NumberFormat('fr-FR');
        if (montantSaisi === 0) {
            alerte.classList.add('alert-warning');
            alerte.textContent = `Ⓘ Renseignez MONTANT EN CHIFFRES avec le total des rubriques (${fmt.format(totalDetails)} XAF).`;
            inpMont.classList.add('is-invalid');
            inpMont.classList.remove('is-valid');
        } else if (Math.abs(ecart) <= 0.01) {
            alerte.classList.add('alert-success');
            alerte.textContent = `✓ Total des rubriques (${fmt.format(totalDetails)} XAF) = MONTANT EN CHIFFRES.`;
            inpMont.classList.add('is-valid');
            inpMont.classList.remove('is-invalid');
        } else {
            alerte.classList.add('alert-danger');
            const signe = ecart > 0 ? 'dépasse' : 'est inférieur au';
            alerte.textContent = `⚠ Le total des rubriques (${fmt.format(totalDetails)} XAF) ${signe} MONTANT EN CHIFFRES (${fmt.format(montantSaisi)} XAF). Écart : ${fmt.format(Math.abs(ecart))} XAF.`;
            inpMont.classList.add('is-invalid');
            inpMont.classList.remove('is-valid');
        }
    }

    function ajouterLigne() {
        const clone = tpl.content.firstElementChild.cloneNode(true);
        // Substitue __idx__ par un nouvel index
        const currentIdx = idxCounter++;
        clone.querySelectorAll('[name]').forEach(el => {
            el.setAttribute('name', el.getAttribute('name').replace('__idx__', currentIdx));
        });
        tbody.appendChild(clone);
        applyRubriquesToRow(clone.querySelector('.select-rubrique'));
    }

    // ── Bindings ──
    if (selExercice) {
        selExercice.addEventListener('change', function () {
            chargerLignesBudgetaires(this.value);
        });
        // Init si valeur pré-sélectionnée (édition ou old)
        if (selExercice.value) {
            chargerLignesBudgetaires(selExercice.value, {{ (int) old('budget_ligne_id', $ordre->budget_ligne_id ?? 0) }});
        }
    }
    if (selBl) selBl.addEventListener('change', chargerRubriques);
    if (btnAdd) btnAdd.addEventListener('click', ajouterLigne);

    // Bouton "Recharger les rubriques prédéfinies"
    const btnReloadRub = document.getElementById('btn-reload-rubriques');
    if (btnReloadRub) {
        btnReloadRub.addEventListener('click', function () {
            if (!aucuneLigneVisible() && !confirm('Ajouter les rubriques prédéfinies aux lignes existantes ?')) return;
            injecterRubriquesPredefinies();
        });
    }

    // Délégation d'événements sur le tbody (input qte/pu, suppression)
    tbody.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-qte') || e.target.classList.contains('input-pu')) {
            const row = e.target.closest('.detail-row');
            if (row) recalcRow(row);
        }
    });
    tbody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-remove-detail');
        if (!btn) return;
        const row = btn.closest('.detail-row');
        const idInput = row.querySelector('input[name*="[id]"]');
        if (idInput) {
            // Ligne persistée : marque pour suppression
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
