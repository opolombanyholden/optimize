@extends('layouts.app')

@section('title', 'Nouvelle ligne budg&eacute;taire')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.budgets.index') }}">Lignes budg&eacute;taires</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Nouvelle ligne budg&eacute;taire</h1>
    <p class="text-muted mb-0">Ajoutez une nouvelle ligne de budget &agrave; un exercice.</p>
</div>

<div class="card data-card">
    <div class="card-header">
        <h5><i class="fas fa-plus-circle me-2 text-muted"></i>Informations de la ligne budg&eacute;taire</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('finance.budgets.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                {{-- Exercice --}}
                <div class="col-12 col-md-6">
                    <label for="exercice_id" class="form-label">Exercice <span class="text-danger">*</span></label>
                    <select name="exercice_id" id="exercice_id" class="form-select @error('exercice_id') is-invalid @enderror" required>
                        <option value="">S&eacute;lectionnez un exercice</option>
                        @foreach($exercices as $exercice)
                            <option value="{{ $exercice->id }}" {{ old('exercice_id', request('exercice_id')) == $exercice->id ? 'selected' : '' }}>
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
                            <option value="{{ $ligne->id }}" {{ old('ligne_id') == $ligne->id ? 'selected' : '' }}>
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
                        <input type="number" name="montant_initial" id="montant_initial" class="form-control @error('montant_initial') is-invalid @enderror" value="{{ old('montant_initial') }}" min="0" step="0.01" required placeholder="0">
                        <span class="input-group-text">F</span>
                        @error('montant_initial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Commentaire --}}
                <div class="col-12">
                    <label for="commentaire" class="form-label">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="3" class="form-control @error('commentaire') is-invalid @enderror" placeholder="Notes ou remarques...">{{ old('commentaire') }}</textarea>
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
                    <i class="fas fa-save me-1"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
