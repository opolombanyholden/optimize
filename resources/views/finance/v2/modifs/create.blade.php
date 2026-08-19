@extends('layouts.app')
@section('title', 'Nouvelle modification budgétaire')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvelle modification budgétaire</h1>
    <a href="{{ route('finance.v2.modifs.index') }}" class="btn btn-outline-secondary">Retour</a>
</div>

<form action="{{ route('finance.v2.modifs.store') }}" method="POST">@csrf
    <div class="card data-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-9">
                    <label class="form-label">Objet <span class="text-danger">*</span></label>
                    <input type="text" name="comment" class="form-control" required maxlength="255" value="{{ old('comment') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Budget d'émission (source)</label>
                    <select name="budget_emission_id" class="form-select">
                        <option value="">— Aucun (ajout pur) —</option>
                        @foreach($budgetSources as $bs)
                            <option value="{{ $bs->id }}">
                                {{ $bs->source?->code }} / {{ $bs->ligne?->code }} · {{ $bs->exercice?->label ?? $bs->exercice?->libelle }}
                                — Dispo : {{ number_format((float) $bs->montant, 0, ',', ' ') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Budget de réception <span class="text-danger">*</span></label>
                    <select name="budget_reception_id" class="form-select" required>
                        <option value="">—</option>
                        @foreach($budgetSources as $bs)
                            <option value="{{ $bs->id }}">
                                {{ $bs->source?->code }} / {{ $bs->ligne?->code }} · {{ $bs->exercice?->label ?? $bs->exercice?->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Montant <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="montant" class="form-control" required value="{{ old('montant') }}">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('finance.v2.modifs.index') }}" class="btn btn-outline-secondary">Annuler</a>
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Créer</button>
        </div>
    </div>
</form>
@endsection
