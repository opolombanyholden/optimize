@extends('layouts.app')

@section('title', 'Modifier fournisseur')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Achats</li>
        <li class="breadcrumb-item"><a href="{{ route('appro.fournisseurs.index') }}">Fournisseurs</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Modifier le fournisseur</h1>
        <p class="text-muted mb-0">{{ $fournisseur->raison_sociale }}</p>
    </div>
    <a href="{{ route('appro.fournisseurs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="card data-card">
    <div class="card-body">
        <form action="{{ route('appro.fournisseurs.update', $fournisseur) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Raison sociale --}}
                <div class="col-12 col-md-6">
                    <label for="raison_sociale" class="form-label">Raison sociale <span class="text-danger">*</span></label>
                    <input type="text" name="raison_sociale" id="raison_sociale" class="form-control @error('raison_sociale') is-invalid @enderror" value="{{ old('raison_sociale', $fournisseur->raison_sociale) }}" required>
                    @error('raison_sociale')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- NIF --}}
                <div class="col-12 col-md-6">
                    <label for="nif" class="form-label">NIF</label>
                    <input type="text" name="nif" id="nif" class="form-control @error('nif') is-invalid @enderror" value="{{ old('nif', $fournisseur->nif) }}">
                    @error('nif')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Registre de commerce --}}
                <div class="col-12 col-md-6">
                    <label for="registre_commerce" class="form-label">Registre de commerce</label>
                    <input type="text" name="registre_commerce" id="registre_commerce" class="form-control @error('registre_commerce') is-invalid @enderror" value="{{ old('registre_commerce', $fournisseur->registre_commerce) }}">
                    @error('registre_commerce')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Statut --}}
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="1" {{ old('statut', $fournisseur->statut) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('statut', $fournisseur->statut) == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Adresse --}}
                <div class="col-12 col-md-6">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" name="adresse" id="adresse" class="form-control @error('adresse') is-invalid @enderror" value="{{ old('adresse', $fournisseur->adresse) }}">
                    @error('adresse')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Ville --}}
                <div class="col-12 col-md-6">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" name="ville" id="ville" class="form-control @error('ville') is-invalid @enderror" value="{{ old('ville', $fournisseur->ville) }}">
                    @error('ville')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Pays --}}
                <div class="col-12 col-md-6">
                    <label for="pays" class="form-label">Pays</label>
                    <input type="text" name="pays" id="pays" class="form-control @error('pays') is-invalid @enderror" value="{{ old('pays', $fournisseur->pays) }}">
                    @error('pays')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contact --}}
                <div class="col-12 col-md-6">
                    <label for="contact" class="form-label">Contact</label>
                    <input type="text" name="contact" id="contact" class="form-control @error('contact') is-invalid @enderror" value="{{ old('contact', $fournisseur->contact) }}">
                    @error('contact')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-12 col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $fournisseur->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Site web --}}
                <div class="col-12 col-md-6">
                    <label for="site_web" class="form-label">Site web</label>
                    <input type="url" name="site_web" id="site_web" class="form-control @error('site_web') is-invalid @enderror" value="{{ old('site_web', $fournisseur->site_web) }}" placeholder="https://">
                    @error('site_web')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Delai de livraison --}}
                <div class="col-12 col-md-6">
                    <label for="delai_livraison" class="form-label">Delai de livraison (jours)</label>
                    <input type="number" name="delai_livraison" id="delai_livraison" class="form-control @error('delai_livraison') is-invalid @enderror" value="{{ old('delai_livraison', $fournisseur->delai_livraison) }}" min="0">
                    @error('delai_livraison')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Conditions de paiement --}}
                <div class="col-12">
                    <label for="conditions_paiement" class="form-label">Conditions de paiement</label>
                    <textarea name="conditions_paiement" id="conditions_paiement" class="form-control @error('conditions_paiement') is-invalid @enderror" rows="3">{{ old('conditions_paiement', $fournisseur->conditions_paiement) }}</textarea>
                    @error('conditions_paiement')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Commentaire --}}
                <div class="col-12">
                    <label for="commentaire" class="form-label">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" class="form-control @error('commentaire') is-invalid @enderror" rows="3">{{ old('commentaire', $fournisseur->commentaire) }}</textarea>
                    @error('commentaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Mettre a jour
                </button>
                <a href="{{ route('appro.fournisseurs.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
