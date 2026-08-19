@php
    $c = $champ; // alias : null si création, OrdreModeleChamp sinon
    $optionsTexte = '';
    if ($c && is_array($c->options_json)) {
        foreach ($c->options_json as $k => $v) $optionsTexte .= $k . '=' . $v . "\n";
        $optionsTexte = rtrim($optionsTexte);
    }
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Code technique <span class="text-danger">*</span></label>
        <input type="text" name="code_champ" class="form-control" required
               value="{{ old('code_champ', $c->code_champ ?? '') }}"
               placeholder="imputation_budget">
        <small class="text-muted">Minuscules, chiffres, underscores.</small>
    </div>
    <div class="col-md-6">
        <label class="form-label">Label affiché <span class="text-danger">*</span></label>
        <input type="text" name="label_personnalise" class="form-control" required
               value="{{ old('label_personnalise', $c->label_personnalise ?? '') }}"
               placeholder="IMPUTATION BUDGETAIRE">
    </div>
    <div class="col-md-4">
        <label class="form-label">Type de saisie <span class="text-danger">*</span></label>
        <select name="type_saisie" class="form-select" required>
            @foreach($typesSaisie as $k => $v)
                <option value="{{ $k }}" @selected(old('type_saisie', $c->type_saisie ?? 'text') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Mapping Grand Livre</label>
        <select name="mapping_gl" class="form-select">
            <option value="">— Aucun (stocké dans donnees_json de l'ordre) —</option>
            @foreach($champsGlDispo as $k => $v)
                <option value="{{ $k }}" @selected(old('mapping_gl', $c->mapping_gl ?? '') === $k)>{{ $v }} ({{ $k }})</option>
            @endforeach
        </select>
        <small class="text-muted">Colonne de la table <code>grand_livres</code> qui recevra la valeur à l'exécution.</small>
    </div>
    <div class="col-md-2">
        <label class="form-label">Largeur</label>
        <select name="largeur_col" class="form-select">
            @foreach([12, 6, 4, 3] as $l)
                <option value="{{ $l }}" @selected(old('largeur_col', $c->largeur_col ?? 12) == $l)>col-{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Placeholder</label>
        <input type="text" name="placeholder" class="form-control"
               value="{{ old('placeholder', $c->placeholder ?? '') }}"
               placeholder="Ex : 618909">
    </div>
    <div class="col-md-6">
        <label class="form-label">Valeur par défaut</label>
        <input type="text" name="valeur_par_defaut" class="form-control"
               value="{{ old('valeur_par_defaut', $c->valeur_par_defaut ?? '') }}">
    </div>
    <div class="col-md-12" data-only-select>
        <label class="form-label">Options (une par ligne, format <code>code=libellé</code>)</label>
        <textarea name="options_texte" class="form-control font-monospace" rows="4"
                  placeholder="numeraire=Numéraire&#10;cheque=Chèque&#10;virement=Virement bancaire">{{ old('options_texte', $optionsTexte) }}</textarea>
        <small class="text-muted">Uniquement pour le type <em>Liste déroulante</em>.</small>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" name="obligatoire" value="1"
                   @checked(old('obligatoire', $c->obligatoire ?? false))>
            <label class="form-check-label">Champ obligatoire</label>
        </div>
    </div>
</div>
