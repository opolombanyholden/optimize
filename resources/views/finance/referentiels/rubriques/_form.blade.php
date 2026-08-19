@php
    $isEdit = isset($rubrique) && $rubrique->exists;
    $preLigne = request('ligne_id'); // depuis vue ligne/show
@endphp

<div class="card data-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $rubrique->code ?? '') }}" required maxlength="50">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-9">
                <label class="form-label">Libellé <span class="text-danger">*</span></label>
                <input type="text" name="libelle" class="form-control" required maxlength="255"
                       value="{{ old('libelle', $rubrique->libelle ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Ligne (code analytique) <span class="text-danger">*</span></label>
                <select name="ligne_id" class="form-select @error('ligne_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($lignes as $l)
                        <option value="{{ $l->id }}" @selected(old('ligne_id', $rubrique->ligne_id ?? $preLigne) == $l->id)>
                            {{ $l->id_codeanalytique }} — {{ $l->libelle }} @if($l->titre)({{ $l->titre->libelle }})@endif
                        </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Cette rubrique apparaîtra dans les opérations dont la ligne budgétaire est rattachée à ce code.</small>
                @error('ligne_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Sens <span class="text-danger">*</span></label>
                <select name="sens" class="form-select" required>
                    @foreach(\App\Models\RubriqueOperation::SENS as $k => $v)
                        <option value="{{ $k }}" @selected(old('sens', $rubrique->sens ?? 'mixte') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Catégorie</label>
                <input type="text" name="categorie" class="form-control" maxlength="50"
                       value="{{ old('categorie', $rubrique->categorie ?? '') }}" placeholder="Matériel, services…">
            </div>

            <div class="col-md-3">
                <label class="form-label">Ordre d'affichage</label>
                <input type="number" name="ordre_affichage" min="0" class="form-control"
                       value="{{ old('ordre_affichage', $rubrique->ordre_affichage ?? 0) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="1" @selected(old('statut', $rubrique->statut ?? 1) == 1)>Actif</option>
                    <option value="0" @selected(old('statut', $rubrique->statut ?? null) === 0)>Archivé</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $rubrique->description ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('finance.referentiels.rubriques.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer' }}</button>
    </div>
</div>
