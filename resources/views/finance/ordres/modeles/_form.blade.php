@php $isEdit = isset($modele) && $modele->exists; @endphp

<div class="card data-card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Paramètres du modèle</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Code technique <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $modele->code) }}" required @disabled($isEdit)
                       placeholder="ordonnance_paiement">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Minuscules, chiffres, underscores.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
                       value="{{ old('libelle', $modele->libelle) }}" required
                       placeholder="Ordonnance de paiement">
                @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Sens <span class="text-danger">*</span></label>
                <select name="sens" class="form-select @error('sens') is-invalid @enderror" required>
                    @foreach(\App\Models\Finance\OrdreModele::SENS as $k => $v)
                        <option value="{{ $k }}" @selected(old('sens', $modele->sens) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('sens')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12">
                <label class="form-label">Format de numérotation</label>
                <input type="text" name="numerotation_format" class="form-control"
                       value="{{ old('numerotation_format', $modele->numerotation_format) }}"
                       placeholder="{n:04d}/BF/PR/ANPI-GABON/DG/DFMG/KAE">
                <small class="text-muted">
                    Placeholders : <code>{n:04d}</code> (séquence zéro-paddée), <code>{annee}</code>, <code>{code}</code>. Ex : <code>0007/BF/2027/ordre_recette</code>
                </small>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i> Présentation du document</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Entête — titre</label>
                <input type="text" name="entete_titre" class="form-control"
                       value="{{ old('entete_titre', $modele->entete_titre) }}"
                       placeholder="ORDONNANCE DE PAIEMENT">
            </div>
            <div class="col-md-6">
                <label class="form-label">Entête — sous-titre (institution)</label>
                <input type="text" name="entete_soustitre" class="form-control"
                       value="{{ old('entete_soustitre', $modele->entete_soustitre) }}"
                       placeholder="AGENCE NATIONALE DE PROMOTION DES INVESTISSEMENTS DU GABON">
            </div>
            <div class="col-md-12">
                <label class="form-label">Phrase d'introduction</label>
                <textarea name="phrase_intro" class="form-control" rows="2"
                          placeholder="L'Agent Comptable est invité à prendre en charge le présent ordre de recette.">{{ old('phrase_intro', $modele->phrase_intro) }}</textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label">Phrase de conclusion</label>
                <input type="text" name="phrase_conclusion" class="form-control"
                       value="{{ old('phrase_conclusion', $modele->phrase_conclusion) }}"
                       placeholder="ARRETE LE PRESENT ORDRE À LA SOMME DE :">
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="avec_mode_reglement" value="1" id="tf-avec-mr"
                           @checked(old('avec_mode_reglement', $modele->avec_mode_reglement))>
                    <label class="form-check-label" for="tf-avec-mr">Bloc Mode de règlement</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="avec_pieces_justif" value="1" id="tf-avec-pj"
                           @checked(old('avec_pieces_justif', $modele->avec_pieces_justif))>
                    <label class="form-check-label" for="tf-avec-pj">Bloc Pièces justificatives</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="actif" value="1" id="tf-actif"
                           @checked(old('actif', $modele->actif))>
                    <label class="form-check-label" for="tf-actif">Modèle actif</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('finance.referentiels.ordres-modeles.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Enregistrer' : 'Créer le modèle' }}
    </button>
</div>
