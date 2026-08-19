@extends('layouts.app')

@section('title', 'Modifier ligne budg&eacute;taire')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.budgets.index') }}">Lignes budg&eacute;taires</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Modifier la ligne budg&eacute;taire</h1>
    <p class="text-muted mb-0">Modifiez les informations de cette ligne de budget.</p>
</div>

<div class="card data-card">
    <div class="card-header">
        <h5><i class="fas fa-edit me-2 text-muted"></i>Informations de la ligne budg&eacute;taire</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('finance.budgets.update', $budget) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Exercice --}}
                <div class="col-12 col-md-6">
                    <label for="exercice_id" class="form-label">Exercice <span class="text-danger">*</span></label>
                    <select name="exercice_id" id="exercice_id" class="form-select @error('exercice_id') is-invalid @enderror" required>
                        <option value="">S&eacute;lectionnez un exercice</option>
                        @foreach($exercices as $exercice)
                            <option value="{{ $exercice->id }}" {{ old('exercice_id', $budget->exercice_id) == $exercice->id ? 'selected' : '' }}>
                                {{ $exercice->libelle }}
                            </option>
                        @endforeach
                    </select>
                    @error('exercice_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Ligne --}}
                <div class="col-12 col-md-6">
                    <label for="ligne_id" class="form-label">Ligne <span class="text-danger">*</span></label>
                    <select name="ligne_id" id="ligne_id" class="form-select @error('ligne_id') is-invalid @enderror" required>
                        <option value="">S&eacute;lectionnez une ligne</option>
                        @foreach($lignes as $ligne)
                            <option value="{{ $ligne->id }}" {{ old('ligne_id', $budget->ligne_id) == $ligne->id ? 'selected' : '' }}>
                                {{ $ligne->libelle }}
                            </option>
                        @endforeach
                    </select>
                    @error('ligne_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Montant initial --}}
                <div class="col-12 col-md-6">
                    <label for="montant_initial" class="form-label">Montant initial <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="montant_initial" id="montant_initial" class="form-control @error('montant_initial') is-invalid @enderror" value="{{ old('montant_initial', $budget->montant_initial) }}" min="0" step="0.01" required>
                        <span class="input-group-text">F</span>
                        @error('montant_initial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Montant r&eacute;vis&eacute; --}}
                <div class="col-12 col-md-6">
                    <label for="montant_revise" class="form-label">Montant r&eacute;vis&eacute;</label>
                    <div class="input-group">
                        <input type="number" name="montant_revise" id="montant_revise" class="form-control @error('montant_revise') is-invalid @enderror" value="{{ old('montant_revise', $budget->montant_revise) }}" min="0" step="0.01">
                        <span class="input-group-text">F</span>
                        @error('montant_revise')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Statut --}}
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="3" {{ old('statut', $budget->statut) == 3 ? 'selected' : '' }}>Brouillon</option>
                        <option value="1" {{ old('statut', $budget->statut) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="2" {{ old('statut', $budget->statut) == 2 ? 'selected' : '' }}>Cl&ocirc;tur&eacute;</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Commentaire --}}
                <div class="col-12">
                    <label for="commentaire" class="form-label">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="3" class="form-control @error('commentaire') is-invalid @enderror">{{ old('commentaire', $budget->commentaire) }}</textarea>
                    @error('commentaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('finance.budgets.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Mettre &agrave; jour
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
