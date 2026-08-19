@extends('layouts.app')

@section('title', 'Nouvel employe')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.employees.index') }}">Employes</a></li>
        <li class="breadcrumb-item active">Nouvel employe</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Nouvel employe</h1>
        <p class="text-muted mb-0">Ajouter un nouvel employe dans le systeme</p>
    </div>
    <a href="{{ route('rh.employees.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<form action="{{ route('rh.employees.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Compte utilisateur (obligatoire) --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-user-shield me-2 text-muted"></i>Compte utilisateur <span class="badge bg-danger ms-2">Obligatoire</span></h5>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-start">
                {{-- Photo --}}
                <div class="col-12 col-md-3 text-center">
                    <label for="photo" class="form-label d-block">Photo de profil</label>
                    <img id="avatarPreview" src="{{ asset('img/default-avatar.svg') }}" alt="Avatar par défaut"
                         style="width:140px;height:140px;border-radius:50%;object-fit:cover;border:3px solid #E2E8F0;background:#F8FAFC;">
                    <div class="mt-2">
                        <input type="file" name="photo" id="photo" accept="image/*"
                               class="form-control form-control-sm @error('photo') is-invalid @enderror"
                               onchange="previewPhoto(this)">
                    </div>
                    <small class="form-text text-muted">JPG, PNG, GIF ou SVG · max 5 Mo</small>
                    @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-9">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span> <small class="text-muted">(identifiant de connexion)</small></label>
                            <input type="email" name="email" id="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="role" class="form-label">Rôle</label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role', 'user') === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Détermine les permissions dans le système</small>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">Mot de passe initial <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" minlength="8"
                                       class="form-control pwd-toggle-input @error('password') is-invalid @enderror" required>
                                <button type="button" class="btn btn-outline-secondary pwd-toggle-btn" tabindex="-1"
                                        data-target="password" aria-label="Afficher le mot de passe" title="Afficher / masquer">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="form-text text-muted">8 caractères minimum. L'employé pourra le changer.</small>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation" minlength="8"
                                       class="form-control pwd-toggle-input" required>
                                <button type="button" class="btn btn-outline-secondary pwd-toggle-btn" tabindex="-1"
                                        data-target="password_confirmation" aria-label="Afficher le mot de passe" title="Afficher / masquer">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Identite --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-id-card me-2 text-muted"></i>Identite</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="noms" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="noms" id="noms" class="form-control @error('noms') is-invalid @enderror" value="{{ old('noms') }}" required>
                    @error('noms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="prenoms" class="form-label">Prenom <span class="text-danger">*</span></label>
                    <input type="text" name="prenoms" id="prenoms" class="form-control @error('prenoms') is-invalid @enderror" value="{{ old('prenoms') }}" required>
                    @error('prenoms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="matricule" class="form-label">Matricule</label>
                    <input type="text" name="matricule" id="matricule" class="form-control @error('matricule') is-invalid @enderror" value="{{ old('matricule') }}">
                    @error('matricule')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="date_naissance" class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" id="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance') }}">
                    @error('date_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="lieu_naissance" class="form-label">Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" id="lieu_naissance" class="form-control @error('lieu_naissance') is-invalid @enderror" value="{{ old('lieu_naissance') }}">
                    @error('lieu_naissance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="nationalite" class="form-label">Nationalite</label>
                    <select name="nationalite" id="nationalite" class="form-select tom-select-rh @error('nationalite') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @foreach(\App\Models\Referentiel\Nationalite::actif()->orderBy('ordre')->orderBy('libelle')->get() as $nat)
                            <option value="{{ $nat->libelle }}" {{ old('nationalite') === $nat->libelle ? 'selected' : '' }}>{{ $nat->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'nationalites') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('nationalite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="sexe" class="form-label">Sexe</label>
                    <select name="sexe" id="sexe" class="form-select @error('sexe') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                        <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Feminin</option>
                    </select>
                    @error('sexe')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="nip" class="form-label">NIP <small class="text-muted">(Numéro d'Identification Personnel)</small></label>
                    <input type="text" name="nip" id="nip" maxlength="20"
                           class="form-control @error('nip') is-invalid @enderror text-uppercase"
                           value="{{ old('nip') }}"
                           placeholder="A1-2345-19901225"
                           pattern="[A-Za-z0-9]{2}-[A-Za-z0-9]{4}-\d{8}">
                    <small class="form-text text-muted">Format : XX-XXXX-AAAAMMJJ (préfixe + série + date de naissance)</small>
                    @error('nip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Contact --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-address-book me-2 text-muted"></i>Contact</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="contact" class="form-label">Telephone</label>
                    <input type="text" name="contact" id="contact" class="form-control @error('contact') is-invalid @enderror" value="{{ old('contact') }}">
                    @error('contact')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="adresse" class="form-label">Adresse</label>
                    <textarea name="adresse" id="adresse" class="form-control @error('adresse') is-invalid @enderror" rows="3">{{ old('adresse') }}</textarea>
                    @error('adresse')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── Localisation administrative ────────────── --}}
                @php
                    $locOpts = \App\Models\Referentiel\LocaliteAdmin::actif()
                        ->orderBy('type')->orderBy('ordre')->orderBy('libelle')
                        ->get()->groupBy('type');
                    $locRef = [
                        'pays'                 => 'pays-loc',
                        'province'             => 'provinces',
                        'departement-admin'    => 'departements-admin',
                        'prefecture'           => 'prefectures',
                        'sous-prefecture'      => 'sous-prefectures',
                        'commune'              => 'communes',
                        'arrondissement'       => 'arrondissements',
                        'quartier'             => 'quartiers',
                        'canton'               => 'cantons',
                        'regroupement-village' => 'regroupements-village',
                        'village'              => 'villages',
                    ];
                @endphp
                <div class="col-12">
                    <hr>
                    <h6 class="text-muted mb-3"><i class="fas fa-map-location-dot me-2"></i> Localisation administrative</h6>
                </div>
                @foreach([
                    ['name'=>'pays',            'label'=>'Pays',            'type'=>'pays'],
                    ['name'=>'province',        'label'=>'Province',        'type'=>'province'],
                    ['name'=>'departement_geo', 'label'=>'Département',     'type'=>'departement-admin'],
                    ['name'=>'prefecture',      'label'=>'Préfecture',      'type'=>'prefecture'],
                    ['name'=>'sous_prefecture', 'label'=>'Sous-préfecture', 'type'=>'sous-prefecture'],
                ] as $f)
                    @php $items = $locOpts[$f['type']] ?? collect(); $cur = old($f['name']); @endphp
                    <div class="col-12 col-md-4">
                        <label for="{{ $f['name'] }}" class="form-label">{{ $f['label'] }}</label>
                        <select name="{{ $f['name'] }}" id="{{ $f['name'] }}" class="form-select tom-select-rh @error($f['name']) is-invalid @enderror">
                            <option value="">-- Selectionner --</option>
                            @foreach($items as $it)
                                <option value="{{ $it->libelle }}" {{ $cur === $it->libelle ? 'selected' : '' }}>{{ $it->libelle }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', $locRef[$f['type']]) }}" target="_blank">Admin › Référentiels</a></small>
                        @error($f['name'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <div class="col-12 col-md-4">
                    <label class="form-label d-block">Zone</label>
                    <div class="btn-group w-100" role="group" id="zoneToggle">
                        <input type="radio" class="btn-check" name="zone_type" id="zone_urbaine" value="urbaine" {{ old('zone_type') === 'urbaine' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-primary" for="zone_urbaine"><i class="fas fa-city me-1"></i> Urbaine</label>
                        <input type="radio" class="btn-check" name="zone_type" id="zone_rurale" value="rurale" {{ old('zone_type') === 'rurale' ? 'checked' : '' }} autocomplete="off">
                        <label class="btn btn-outline-success" for="zone_rurale"><i class="fas fa-tree me-1"></i> Rurale</label>
                    </div>
                    @error('zone_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Subdivisions urbaines --}}
                <div class="col-12 zone-fields zone-urbaine" style="display:none;">
                    <div class="row g-3">
                        @foreach([
                            ['name'=>'commune',        'label'=>'Commune',        'type'=>'commune'],
                            ['name'=>'arrondissement', 'label'=>'Arrondissement', 'type'=>'arrondissement'],
                            ['name'=>'quartier_loc',   'label'=>'Quartier',       'type'=>'quartier'],
                        ] as $f)
                            @php $items = $locOpts[$f['type']] ?? collect(); $cur = old($f['name']); @endphp
                            <div class="col-12 col-md-4">
                                <label for="{{ $f['name'] }}" class="form-label">{{ $f['label'] }}</label>
                                <select name="{{ $f['name'] }}" id="{{ $f['name'] }}" class="form-select tom-select-rh @error($f['name']) is-invalid @enderror">
                                    <option value="">-- Selectionner --</option>
                                    @foreach($items as $it)
                                        <option value="{{ $it->libelle }}" {{ $cur === $it->libelle ? 'selected' : '' }}>{{ $it->libelle }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', $locRef[$f['type']]) }}" target="_blank">Admin › Référentiels</a></small>
                                @error($f['name'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Subdivisions rurales --}}
                <div class="col-12 zone-fields zone-rurale" style="display:none;">
                    <div class="row g-3">
                        @foreach([
                            ['name'=>'canton',               'label'=>'Canton',                'type'=>'canton'],
                            ['name'=>'regroupement_village', 'label'=>'Regroupement de village','type'=>'regroupement-village'],
                            ['name'=>'village',              'label'=>'Village',               'type'=>'village'],
                        ] as $f)
                            @php $items = $locOpts[$f['type']] ?? collect(); $cur = old($f['name']); @endphp
                            <div class="col-12 col-md-4">
                                <label for="{{ $f['name'] }}" class="form-label">{{ $f['label'] }}</label>
                                <select name="{{ $f['name'] }}" id="{{ $f['name'] }}" class="form-select tom-select-rh @error($f['name']) is-invalid @enderror">
                                    <option value="">-- Selectionner --</option>
                                    @foreach($items as $it)
                                        <option value="{{ $it->libelle }}" {{ $cur === $it->libelle ? 'selected' : '' }}>{{ $it->libelle }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', $locRef[$f['type']]) }}" target="_blank">Admin › Référentiels</a></small>
                                @error($f['name'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Emploi --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-briefcase me-2 text-muted"></i>Emploi</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="date_embauche" class="form-label">Date d'embauche</label>
                    <input type="date" name="date_embauche" id="date_embauche" class="form-control @error('date_embauche') is-invalid @enderror" value="{{ old('date_embauche') }}">
                    @error('date_embauche')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @php
                    $typesContrat = \App\Models\Referentiel\TypeContrat::actif()->orderBy('ordre')->orderBy('libelle')->get();
                    $postes       = \App\Models\Referentiel\Poste::actif()->orderBy('ordre')->orderBy('libelle')->get();
                    $departements = \App\Models\Referentiel\Departement::actif()->orderBy('ordre')->orderBy('libelle')->get();
                @endphp
                <div class="col-12 col-md-4">
                    <label for="type_contrat" class="form-label">Type de contrat</label>
                    <select name="type_contrat" id="type_contrat" class="form-select tom-select-rh @error('type_contrat') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @foreach($typesContrat as $t)
                            <option value="{{ $t->libelle }}" {{ old('type_contrat') == $t->libelle ? 'selected' : '' }}>{{ $t->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'types-contrat') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('type_contrat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="poste" class="form-label">Poste</label>
                    <select name="poste" id="poste" class="form-select tom-select-rh @error('poste') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @foreach($postes as $p)
                            <option value="{{ $p->libelle }}" {{ old('poste') == $p->libelle ? 'selected' : '' }}>{{ $p->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'postes') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('poste')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="departement" class="form-label">Departement</label>
                    <select name="departement" id="departement" class="form-select tom-select-rh @error('departement') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @foreach($departements as $d)
                            <option value="{{ $d->libelle }}" {{ old('departement') == $d->libelle ? 'selected' : '' }}>{{ $d->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Géré dans <a href="{{ route('admin.referentiel-rh.index', 'departements') }}" target="_blank">Admin › Référentiels</a></small>
                    @error('departement')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="organisation_id" class="form-label">Entité juridique / Employeur</label>
                    <select name="organisation_id" id="organisation_id" class="form-select tom-select-rh @error('organisation_id') is-invalid @enderror">
                        <option value="">-- Selectionner --</option>
                        @foreach($organisations as $organisation)
                            <option value="{{ $organisation->id }}" {{ old('organisation_id') == $organisation->id ? 'selected' : '' }}>{{ $organisation->libelle ?? $organisation->nom }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Personne morale qui emploie (apparaît sur le contrat)</small>
                    @error('organisation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="superieur_poste_id" class="form-label">Supérieur hiérarchique <small class="text-muted">(par poste)</small></label>
                    <select name="superieur_poste_id" id="superieur_poste_id" class="form-select tom-select-rh @error('superieur_poste_id') is-invalid @enderror">
                        <option value="">-- Aucun --</option>
                        @foreach($postes as $p)
                            <option value="{{ $p->id }}" {{ old('superieur_poste_id') == $p->id ? 'selected' : '' }}>{{ $p->libelle }}</option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Le lien suit le poste, pas la personne — résolution dynamique du titulaire actuel.</small>
                    @error('superieur_poste_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Remuneration --}}
    <div class="card data-card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-money-bill-wave me-2 text-muted"></i>Remuneration</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="salaire_base" class="form-label">Salaire de base</label>
                    <input type="number" name="salaire_base" id="salaire_base" class="form-control @error('salaire_base') is-invalid @enderror" value="{{ old('salaire_base') }}" step="0.01" min="0">
                    @error('salaire_base')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="iban" class="form-label">IBAN</label>
                    <input type="text" name="iban" id="iban" class="form-control @error('iban') is-invalid @enderror" value="{{ old('iban') }}">
                    @error('iban')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="numero_secu" class="form-label">Numero de securite sociale</label>
                    <input type="text" name="numero_secu" id="numero_secu" class="form-control @error('numero_secu') is-invalid @enderror" value="{{ old('numero_secu') }}">
                    @error('numero_secu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.employees.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i>Enregistrer
        </button>
    </div>
</form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
function previewPhoto(input) {
    const file = input.files && input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select.tom-select-rh').forEach(function (el) {
        if (el.tomselect) return;
        new TomSelect(el, {
            allowEmptyOption: true,
            maxOptions: 500,
            create: false,
            render: {
                no_results: () => '<div class="no-results">Aucun résultat — gérez les options dans Admin › Référentiels</div>',
            },
        });
    });

    // Bascule afficher / masquer mot de passe
    document.querySelectorAll('.pwd-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }
            this.setAttribute('aria-label', showing ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
        });
    });

    // Bascule affichage subdivisions urbaines / rurales
    const zoneRadios = document.querySelectorAll('input[name="zone_type"]');
    const urbaineBlock = document.querySelector('.zone-fields.zone-urbaine');
    const ruraleBlock  = document.querySelector('.zone-fields.zone-rurale');
    function refreshZone() {
        const v = document.querySelector('input[name="zone_type"]:checked')?.value;
        if (urbaineBlock) urbaineBlock.style.display = (v === 'urbaine') ? '' : 'none';
        if (ruraleBlock)  ruraleBlock.style.display  = (v === 'rurale')  ? '' : 'none';
    }
    zoneRadios.forEach(r => r.addEventListener('change', refreshZone));
    refreshZone();
});
</script>
@endpush
