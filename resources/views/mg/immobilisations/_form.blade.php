@csrf
<div class="card data-card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Code interne</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $immobilisation->code) }}" placeholder="INV-2027-001">
            </div>
            <div class="col-md-6">
                <label class="form-label">Désignation <span class="text-danger">*</span></label>
                <input type="text" name="designation" class="form-control" value="{{ old('designation', $immobilisation->designation) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Catégorie</label>
                <select name="categorie" class="form-select">
                    <option value="">—</option>
                    @foreach($categories as $k => $v)
                        <option value="{{ $k }}" @selected(old('categorie', $immobilisation->categorie) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $immobilisation->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Localisation</label>
                <input type="text" name="localisation" class="form-control" value="{{ old('localisation', $immobilisation->localisation) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Affecté à</label>
                <select name="affecte_a" class="form-select">
                    <option value="">—</option>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}" @selected(old('affecte_a', $immobilisation->affecte_a) == $e->id)>{{ $e->noms }} {{ $e->prenoms ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Entité</label>
                <select name="entite_id" class="form-select">
                    <option value="">—</option>
                    @foreach($entites as $ent)
                        <option value="{{ $ent->id }}" @selected(old('entite_id', $immobilisation->entite_id) == $ent->id)>{{ $ent->libelle }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-calculator me-2"></i> Amortissement</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Date acquisition</label>
                <input type="date" name="date_acquisition" class="form-control" value="{{ old('date_acquisition', optional($immobilisation->date_acquisition)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Mise en service</label>
                <input type="date" name="date_mise_en_service" class="form-control" value="{{ old('date_mise_en_service', optional($immobilisation->date_mise_en_service)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Valeur d'acquisition (XAF)</label>
                <input type="number" step="0.01" name="valeur_acquisition" class="form-control text-end" value="{{ old('valeur_acquisition', $immobilisation->valeur_acquisition) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Valeur résiduelle (XAF)</label>
                <input type="number" step="0.01" name="valeur_residuelle" class="form-control text-end" value="{{ old('valeur_residuelle', $immobilisation->valeur_residuelle) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Durée (mois)</label>
                <input type="number" min="1" max="1200" name="duree_amortissement" class="form-control text-end" value="{{ old('duree_amortissement', $immobilisation->duree_amortissement) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Méthode</label>
                <select name="methode_amortissement" class="form-select">
                    @foreach($methodes as $k => $v)
                        <option value="{{ $k }}" @selected(old('methode_amortissement', $immobilisation->methode_amortissement) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">État</label>
                <select name="etat" class="form-select">
                    @foreach($etats as $k => $v)
                        <option value="{{ $k }}" @selected(old('etat', $immobilisation->etat) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            @if($immobilisation->exists)
                <div class="col-md-3">
                    <label class="form-label">VNC actuelle (XAF)</label>
                    <input type="number" step="0.01" name="valeur_nette_comptable" class="form-control text-end" value="{{ old('valeur_nette_comptable', $immobilisation->valeur_nette_comptable) }}">
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes</h6></div>
    <div class="card-body">
        <input type="file" name="pieces_jointes[]" class="form-control" multiple>
        <small class="text-muted">Ex. facture d'achat, notice, photos.</small>
        @if($immobilisation->exists && $immobilisation->piecesJointes->isNotEmpty())
            <hr>
            @include('mg._partials.pieces-jointes', ['pieces' => $immobilisation->piecesJointes])
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    <a href="{{ route('mg.immobilisations.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
