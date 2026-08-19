@php $isEdit = isset($compte) && $compte->exists; @endphp

<div class="card data-card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-wallet me-2"></i> Paramètres du compte</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Code <small class="text-muted">(optionnel)</small></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $compte->code) }}" placeholder="Ex : BQ-BGFI-01">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5">
                <label class="form-label">Nom du compte <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                       value="{{ old('nom', $compte->nom) }}" required
                       placeholder="Ex : BGFI Bank — Compte courant DG">
                @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                    @foreach(\App\Models\Compte::TYPES as $k => $v)
                        <option value="{{ $k }}" @selected(old('type', $compte->type) == $k)>{{ $v }}</option>
                    @endforeach
                </select>
                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-coins me-2"></i> Solde et devise</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Solde initial</label>
                <input type="number" name="solde_initial" class="form-control text-end"
                       value="{{ old('solde_initial', $compte->solde_initial ?? 0) }}" step="0.01"
                       @if($isEdit) readonly title="Le solde initial ne peut pas être modifié après création." @endif>
                <small class="text-muted">{{ $isEdit ? 'Référence historique (lecture seule)' : "À l'ouverture du compte" }}</small>
            </div>
            @if($isEdit)
            <div class="col-md-4">
                <label class="form-label">Solde actuel</label>
                <input type="number" name="solde" class="form-control text-end fw-bold"
                       value="{{ old('solde', $compte->solde) }}" step="0.01">
                <small class="text-muted">Ajuster manuellement au besoin (rapprochement bancaire)</small>
            </div>
            @endif
            <div class="col-md-4">
                <label class="form-label">Devise</label>
                <select name="devise" class="form-select">
                    @foreach(['XAF' => 'XAF — Franc CFA (BEAC)', 'EUR' => 'EUR — Euro', 'USD' => 'USD — Dollar US'] as $k => $v)
                        <option value="{{ $k }}" @selected(old('devise', $compte->devise ?? 'XAF') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-id-card me-2"></i> Identification bancaire</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">RIB / Numéro de compte</label>
                <input type="text" name="rib" class="form-control" value="{{ old('rib', $compte->rib) }}"
                       placeholder="Pour compte bancaire ou électronique">
            </div>
            <div class="col-md-6">
                <label class="form-label">Domiciliation / Fournisseur</label>
                <input type="text" name="domiciliation" class="form-control" value="{{ old('domiciliation', $compte->domiciliation) }}"
                       placeholder="Ex : BGFI Bank Libreville, Airtel Money, Orange Money…">
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-user-tie me-2"></i> Gestion & responsables</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Responsable</label>
                <input type="text" name="responsable" class="form-control" value="{{ old('responsable', $compte->responsable) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Gestionnaire (personne)</label>
                <input type="text" name="gestionnaire" class="form-control" value="{{ old('gestionnaire', $compte->gestionnaire) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Contact gestionnaire</label>
                <input type="text" name="contact_gestionnaire" class="form-control" value="{{ old('contact_gestionnaire', $compte->contact_gestionnaire) }}"
                       placeholder="Tél / email">
            </div>
        </div>
        <div class="form-check form-switch mt-3">
            <input type="checkbox" name="actif" value="1" class="form-check-input" id="tf-actif"
                   @checked(old('actif', $compte->actif ?? true))>
            <label class="form-check-label" for="tf-actif">Compte actif (utilisable pour de nouveaux ordres)</label>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('finance.comptes.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> {{ $isEdit ? 'Enregistrer' : 'Créer le compte' }}
    </button>
</div>
