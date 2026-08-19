@extends('layouts.app')

@section('title', 'Nouvelle &eacute;criture')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.grand-livre.index') }}">Grand Livre</a></li>
        <li class="breadcrumb-item active">Nouvelle &eacute;criture</li>
    </ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Nouvelle &eacute;criture comptable</h1>
    <p class="text-muted mb-0">Enregistrez une nouvelle &eacute;criture dans le grand livre.</p>
</div>

<div class="card data-card">
    <div class="card-header">
        <h5><i class="fas fa-plus-circle me-2 text-muted"></i>Informations de l'&eacute;criture</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('finance.grand-livre.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                {{-- Exercice --}}
                <div class="col-12 col-md-6">
                    <label for="exercice_id" class="form-label">Exercice <span class="text-danger">*</span></label>
                    <select name="exercice_id" id="exercice_id" class="form-select @error('exercice_id') is-invalid @enderror" required>
                        <option value="">S&eacute;lectionnez un exercice</option>
                        @foreach($exercices as $exercice)
                            <option value="{{ $exercice->id }}" {{ old('exercice_id') == $exercice->id ? 'selected' : '' }}>
                                {{ $exercice->libelle }}
                            </option>
                        @endforeach
                    </select>
                    @error('exercice_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- R&eacute;f&eacute;rence --}}
                <div class="col-12 col-md-6">
                    <label for="reference" class="form-label">R&eacute;f&eacute;rence <span class="text-danger">*</span></label>
                    <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference') }}" required placeholder="Ex: ECR-2026-001">
                    @error('reference')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date &eacute;criture --}}
                <div class="col-12 col-md-6">
                    <label for="date_ecriture" class="form-label">Date de l'&eacute;criture <span class="text-danger">*</span></label>
                    <input type="date" name="date_ecriture" id="date_ecriture" class="form-control @error('date_ecriture') is-invalid @enderror" value="{{ old('date_ecriture', date('Y-m-d')) }}" required>
                    @error('date_ecriture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Libell&eacute; --}}
                <div class="col-12 col-md-6">
                    <label for="libelle" class="form-label">Libell&eacute; <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="libelle" class="form-control @error('libelle') is-invalid @enderror" value="{{ old('libelle') }}" required placeholder="Description de l'&eacute;criture">
                    @error('libelle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Montant d&eacute;bit --}}
                <div class="col-12 col-md-6">
                    <label for="montant_debit" class="form-label">Montant d&eacute;bit</label>
                    <div class="input-group">
                        <input type="number" name="montant_debit" id="montant_debit" class="form-control @error('montant_debit') is-invalid @enderror" value="{{ old('montant_debit', 0) }}" min="0" step="0.01" placeholder="0">
                        <span class="input-group-text">F</span>
                        @error('montant_debit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Montant cr&eacute;dit --}}
                <div class="col-12 col-md-6">
                    <label for="montant_credit" class="form-label">Montant cr&eacute;dit</label>
                    <div class="input-group">
                        <input type="number" name="montant_credit" id="montant_credit" class="form-control @error('montant_credit') is-invalid @enderror" value="{{ old('montant_credit', 0) }}" min="0" step="0.01" placeholder="0">
                        <span class="input-group-text">F</span>
                        @error('montant_credit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('finance.grand-livre.index') }}" class="btn btn-outline-secondary">
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
