@csrf
<div class="card data-card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Fournisseur <span class="text-danger">*</span></label>
                <select name="fournisseur_id" class="form-select" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($fournisseurs as $f)
                        <option value="{{ $f->id }}" @selected(old('fournisseur_id', $contrat->fournisseur_id) == $f->id)>{{ $f->raison_sociale }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select" required>
                    @foreach($types as $k => $l)
                        <option value="{{ $k }}" @selected(old('type', $contrat->type) === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut <span class="text-danger">*</span></label>
                <select name="statut" class="form-select" required>
                    @foreach($statuts as $k => $l)
                        <option value="{{ $k }}" @selected(old('statut', $contrat->statut) === $k)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Objet <span class="text-danger">*</span></label>
                <input type="text" name="objet" class="form-control" value="{{ old('objet', $contrat->objet) }}" required maxlength="255">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $contrat->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de signature</label>
                <input type="date" name="date_signature" class="form-control" value="{{ old('date_signature', optional($contrat->date_signature)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de début <span class="text-danger">*</span></label>
                <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', optional($contrat->date_debut)->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de fin <span class="text-muted small">(vide = indéterminée)</span></label>
                <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', optional($contrat->date_fin)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Montant HT</label>
                <input type="number" step="0.01" min="0" name="montant_ht" class="form-control text-end" value="{{ old('montant_ht', $contrat->montant_ht) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Montant TTC</label>
                <input type="number" step="0.01" min="0" name="montant_ttc" class="form-control text-end" value="{{ old('montant_ttc', $contrat->montant_ttc) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Devise</label>
                <input type="text" name="devise" class="form-control" value="{{ old('devise', $contrat->devise) }}" maxlength="3">
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" name="renouvellement_auto" value="1" id="renouvAuto" @checked(old('renouvellement_auto', $contrat->renouvellement_auto))>
                    <label class="form-check-label" for="renouvAuto">Renouvellement automatique</label>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Préavis de résiliation (jours)</label>
                <input type="number" min="0" name="preavis_resiliation_jours" class="form-control" value="{{ old('preavis_resiliation_jours', $contrat->preavis_resiliation_jours) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Conditions particulières</label>
                <textarea name="conditions" class="form-control" rows="3">{{ old('conditions', $contrat->conditions) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-money-check-dollar me-2"></i> Modalités de paiement</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Type de paiement <span class="text-danger">*</span></label>
                <select name="frequence_paiement" class="form-select" required>
                    @foreach($frequences as $k => $l)
                        <option value="{{ $k }}" @selected(old('frequence_paiement', $contrat->frequence_paiement ?? 'ponctuel') === $k)>{{ $l }}</option>
                    @endforeach
                </select>
                <div class="form-text">Périodicité des versements au fournisseur.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Montant à chaque paiement</label>
                <div class="input-group">
                    <input type="number" step="0.01" min="0" name="montant_par_paiement" class="form-control text-end" value="{{ old('montant_par_paiement', $contrat->montant_par_paiement) }}">
                    <span class="input-group-text">{{ old('devise', $contrat->devise ?? 'XAF') }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Jour du mois d'échéance</label>
                <input type="number" min="1" max="31" name="jour_paiement" class="form-control" value="{{ old('jour_paiement', $contrat->jour_paiement) }}" placeholder="ex: 15">
                <div class="form-text">Utilisé pour les paiements récurrents (1–31).</div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Délai de paiement (jours après facturation)</label>
                <input type="number" min="0" max="365" name="delai_paiement_jours" class="form-control" value="{{ old('delai_paiement_jours', $contrat->delai_paiement_jours) }}" placeholder="ex: 30">
                <div class="form-text">Ex : 30 = « Net 30 jours ».</div>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes (contrat signé, annexes, avenants…)</h6></div>
    <div class="card-body">
        <input type="file" name="pieces_jointes[]" class="form-control" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,image/*">
        @if($contrat->exists && $contrat->piecesJointes->isNotEmpty())
            <hr>
            @foreach($contrat->piecesJointes as $pj)
                <a href="{{ $pj->url }}" target="_blank" class="d-inline-block me-2 mb-2" style="text-decoration:none;">
                    <span class="badge bg-light text-dark"><i class="fas {{ $pj->icone }} me-1"></i>{{ $pj->nom_original }}</span>
                </a>
            @endforeach
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    <a href="{{ route('appro.contrats.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
