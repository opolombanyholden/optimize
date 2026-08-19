@php $isEdit = isset($mod) && $mod->exists; @endphp

@if($exercices->isEmpty())
    <div class="alert alert-warning">
        <i class="fas fa-triangle-exclamation me-2"></i>
        <strong>Aucun exercice éligible.</strong>
        Une modification budgétaire ne peut viser qu'un exercice
        <span class="badge bg-info">Planification</span> ou
        <span class="badge bg-success">En exécution</span>.
        <a href="{{ route('finance.exercices.create') }}" class="alert-link">Créer un exercice</a>
        ou vérifier l'état des exercices existants.
    </div>
@endif

<div class="card data-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Exercice <span class="text-danger">*</span></label>
                <select name="exercice_id" class="form-select @error('exercice_id') is-invalid @enderror" required @disabled($exercices->isEmpty())>
                    <option value="">— Sélectionner —</option>
                    @foreach($exercices as $e)
                        @php
                            $statutOk = in_array((int) $e->statut, [\App\Models\Exercice::STATUT_PLANIFICATION, \App\Models\Exercice::STATUT_EN_EXECUTION], true);
                            $lbl = ($e->libelle ?? $e->exercice) . ' · ' . $e->statut_libelle;
                        @endphp
                        <option value="{{ $e->id }}" @selected(old('exercice_id', $mod->exercice_id ?? '') == $e->id) @disabled(!$statutOk)>
                            {{ $lbl }}{{ !$statutOk ? ' (verrouillé)' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Seuls les exercices en <strong>planification</strong> ou en <strong>exécution</strong> acceptent une modification budgétaire.
                </small>
                @error('exercice_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Type de modification <span class="text-danger">*</span></label>
                <select name="type_modification" id="type_modification" class="form-select @error('type_modification') is-invalid @enderror" required>
                    <option value="transfert" @selected(old('type_modification', $mod->type_modification ?? 'transfert') === 'transfert')>Transfert entre lignes</option>
                    <option value="ajout" @selected(old('type_modification', $mod->type_modification ?? null) === 'ajout')>Apport sur une ligne</option>
                </select>
                @error('type_modification')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">Objet <span class="text-danger">*</span></label>
                <input type="text" name="objetmodification" class="form-control @error('objetmodification') is-invalid @enderror"
                       value="{{ old('objetmodification', $mod->objetmodification ?? '') }}" required maxlength="255"
                       placeholder="Ex : Réaffectation budgétaire — formation équipe RH">
                @error('objetmodification')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @php
                // Regroupe les lignes par exercice puis par titre pour un <optgroup> lisible.
                $lignesParExercice = $lignes->groupBy(fn($l) => $l->exercice?->libelle ?? $l->exercice?->exercice ?? 'Sans exercice');

                // Libellé d'option : préférence au libellé du référentiel, fallback commentaire, fallback code
                $labelLigne = function ($l) {
                    $titre  = $l->ligne?->titre?->imputation;
                    $lib    = $l->ligne?->libelle ?: $l->commentaire ?: $l->id_budgetligne;
                    return ($titre ? "[{$titre}] " : '') . $lib;
                };
            @endphp

            <div class="col-md-6" id="zone-source" @if(($mod->type_modification ?? 'transfert') === 'ajout') style="display:none;" @endif>
                <label class="form-label">Ligne source (qui finance) <span class="text-danger">*</span></label>
                <select name="budget_ligne_source_id" class="form-select @error('budget_ligne_source_id') is-invalid @enderror">
                    <option value="">— Sélectionner —</option>
                    @foreach($lignesParExercice as $exercice => $lignesEx)
                        <optgroup label="{{ $exercice }}">
                            @foreach($lignesEx as $l)
                                <option value="{{ $l->id }}" data-solde="{{ $l->solde_disponible }}"
                                        @selected(old('budget_ligne_source_id', $mod->budget_ligne_source_id ?? '') == $l->id)>
                                    {{ $labelLigne($l) }} · Solde : {{ number_format($l->solde_disponible, 0, ',', ' ') }} XAF
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('budget_ligne_source_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Ligne destination <span class="text-danger">*</span></label>
                <select name="budget_ligne_destination_id" class="form-select @error('budget_ligne_destination_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($lignesParExercice as $exercice => $lignesEx)
                        <optgroup label="{{ $exercice }}">
                            @foreach($lignesEx as $l)
                                <option value="{{ $l->id }}"
                                        @selected(old('budget_ligne_destination_id', $mod->budget_ligne_destination_id ?? '') == $l->id)>
                                    {{ $labelLigne($l) }} · Budget : {{ number_format($l->budget_total, 0, ',', ' ') }} XAF
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('budget_ligne_destination_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Montant <span class="text-danger">*</span></label>
                <input type="number" name="montant_modification" step="0.01" min="0.01"
                       class="form-control @error('montant_modification') is-invalid @enderror"
                       value="{{ old('montant_modification', $mod->montant_modification ?? '') }}" required>
                @error('montant_modification')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">Commentaire</label>
                <textarea name="commentaire" rows="3" class="form-control" maxlength="2000"
                          placeholder="Justification, contexte…">{{ old('commentaire', $mod->commentaire ?? '') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('finance.modifications-budgetaires.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer' }}</button>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const sel = document.getElementById('type_modification');
        const src = document.getElementById('zone-source');
        function refresh() {
            if (sel.value === 'ajout') {
                src.style.display = 'none';
                src.querySelector('select').required = false;
            } else {
                src.style.display = '';
                src.querySelector('select').required = true;
            }
        }
        sel.addEventListener('change', refresh);
        refresh();
    })();
</script>
@endpush
