@php $isEdit = isset($client) && $client->exists; @endphp

<div class="card data-card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                       value="{{ old('code', $client->code ?? '') }}" required maxlength="50">
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Raison sociale <span class="text-danger">*</span></label>
                <input type="text" name="raison_sociale" class="form-control @error('raison_sociale') is-invalid @enderror"
                       value="{{ old('raison_sociale', $client->raison_sociale ?? '') }}" required maxlength="255">
                @error('raison_sociale')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Forme juridique</label>
                <input type="text" name="forme_juridique" class="form-control"
                       value="{{ old('forme_juridique', $client->forme_juridique ?? '') }}" placeholder="SARL, SA, EI…" maxlength="50">
            </div>

            <div class="col-md-6">
                <label class="form-label">NIF</label>
                <input type="text" name="nif" class="form-control" value="{{ old('nif', $client->nif ?? '') }}" maxlength="50">
            </div>
            <div class="col-md-6">
                <label class="form-label">RCCM</label>
                <input type="text" name="rccm" class="form-control" value="{{ old('rccm', $client->rccm ?? '') }}" maxlength="50">
            </div>

            <div class="col-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $client->adresse ?? '') }}" maxlength="500">
            </div>
            <div class="col-md-6">
                <label class="form-label">Ville</label>
                <input type="text" name="ville" class="form-control" value="{{ old('ville', $client->ville ?? '') }}" maxlength="100">
            </div>
            <div class="col-md-6">
                <label class="form-label">Pays</label>
                <input type="text" name="pays" class="form-control" value="{{ old('pays', $client->pays ?? 'Gabon') }}" maxlength="100">
            </div>

            <div class="col-md-4">
                <label class="form-label">Téléphone</label>
                <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $client->telephone ?? '') }}" maxlength="50">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $client->email ?? '') }}" maxlength="150">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Site web</label>
                <input type="url" name="site_web" class="form-control @error('site_web') is-invalid @enderror"
                       value="{{ old('site_web', $client->site_web ?? '') }}" maxlength="255">
                @error('site_web')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <hr>
            <h6 class="text-muted small mt-2">Contact principal</h6>
            <div class="col-md-4">
                <label class="form-label">Nom</label>
                <input type="text" name="contact_nom" class="form-control" value="{{ old('contact_nom', $client->contact_nom ?? '') }}" maxlength="150">
            </div>
            <div class="col-md-4">
                <label class="form-label">Téléphone</label>
                <input type="text" name="contact_telephone" class="form-control" value="{{ old('contact_telephone', $client->contact_telephone ?? '') }}" maxlength="50">
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $client->contact_email ?? '') }}" maxlength="150">
            </div>

            <hr>
            <h6 class="text-muted small mt-2">Coordonnées bancaires</h6>
            <div class="col-md-8">
                <label class="form-label">RIB / IBAN</label>
                <input type="text" name="rib" class="form-control" value="{{ old('rib', $client->rib ?? '') }}" maxlength="50">
            </div>
            <div class="col-md-4">
                <label class="form-label">Banque</label>
                <input type="text" name="banque" class="form-control" value="{{ old('banque', $client->banque ?? '') }}" maxlength="100">
            </div>

            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3" maxlength="2000">{{ old('notes', $client->notes ?? '') }}</textarea>
            </div>

            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="1" @selected(old('statut', $client->statut ?? 1) == 1)>Actif</option>
                    <option value="0" @selected(old('statut', $client->statut ?? null) === 0)>Inactif</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('finance.clients.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer le client' }}</button>
    </div>
</div>
