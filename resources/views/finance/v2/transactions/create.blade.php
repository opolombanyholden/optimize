@extends('layouts.app')
@section('title', 'Nouvelle transaction')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.transactions.index') }}">Transactions</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvelle {{ $type == 0 ? 'dépense' : 'recette' }}</h1>
    <a href="{{ route('finance.v2.transactions.index') }}" class="btn btn-outline-secondary">Retour</a>
</div>

<form action="{{ route('finance.v2.transactions.store') }}" method="POST">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">

    <div class="card data-card">
        <div class="card-header">
            <strong>@if($type == 0)<i class="fas fa-arrow-down text-danger me-1"></i>Dépense
                   @else<i class="fas fa-arrow-up text-success me-1"></i>Recette@endif</strong>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" required value="{{ old('date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Code / N° pièce</label>
                    <input type="text" name="code" class="form-control" maxlength="100" value="{{ old('code') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Libellé / Objet <span class="text-danger">*</span></label>
                    <input type="text" name="label" class="form-control" required maxlength="255" value="{{ old('label') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bénéficiaire</label>
                    <input type="text" name="beneficiaire" class="form-control" maxlength="255" value="{{ old('beneficiaire') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ligne</label>
                    <select name="ligne_id" class="form-select">
                        <option value="">—</option>
                        @foreach($lignes as $l)
                            <option value="{{ $l->id }}">{{ $l->code }} — {{ \Illuminate\Support\Str::limit($l->label ?? $l->libelle, 40) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Exercice</label>
                    <select name="exercice_id" class="form-select">
                        <option value="">—</option>
                        @foreach($exercices as $ex)
                            <option value="{{ $ex->id }}">{{ $ex->label ?? $ex->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Compte</label>
                    <select name="compte_id" class="form-select">
                        <option value="">—</option>
                        @foreach($comptes as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->label ?? $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mode règlement</label>
                    <select name="mode_reglement_id" class="form-select">
                        <option value="">—</option>
                        @foreach($modes as $m)
                            <option value="{{ $m->id }}">{{ $m->code }} — {{ $m->label ?? $m->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Entité</label>
                    <select name="entite_id" class="form-select">
                        <option value="">—</option>
                        @foreach($entites as $e)
                            <option value="{{ $e->id }}">{{ $e->code }} — {{ $e->label ?? $e->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Devise</label>
                    <input type="text" name="devise" class="form-control" value="XAF" maxlength="10">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Montant <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="montant" class="form-control" required value="{{ old('montant') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('finance.v2.transactions.index') }}" class="btn btn-outline-secondary">Annuler</a>
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Créer</button>
        </div>
    </div>
</form>
@endsection
