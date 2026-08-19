@extends('layouts.app')

@section('title', 'Modifier ' . $organisation->label)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Administration</li>
        <li class="breadcrumb-item"><a href="{{ route('systeme.organisations.index') }}">Organisations</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Modifier : {{ $organisation->label }}</h1>
    <p class="text-muted mb-0">Mettre a jour les informations de l'organisation</p>
</div>

<div class="card data-card">
    <div class="card-body">
        <form method="POST" action="{{ route('systeme.organisations.update', $organisation) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="label" class="form-label">Designation <span class="text-danger">*</span></label>
                    <input type="text" name="label" id="label" class="form-control @error('label') is-invalid @enderror" value="{{ old('label', $organisation->label) }}" required>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="typesorganisation_id" class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="typesorganisation_id" id="typesorganisation_id" class="form-select @error('typesorganisation_id') is-invalid @enderror" required>
                        <option value="">Selectionner un type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ old('typesorganisation_id', $organisation->typesorganisation_id) == $type->id ? 'selected' : '' }}>{{ $type->label }}</option>
                        @endforeach
                    </select>
                    @error('typesorganisation_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="peres" class="form-label">Organisation parente</label>
                    <select name="peres" id="peres" class="form-select @error('peres') is-invalid @enderror">
                        <option value="">Aucune (racine)</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('peres', $organisation->peres) == $parent->id ? 'selected' : '' }}>{{ $parent->label }}</option>
                        @endforeach
                    </select>
                    @error('peres')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="chefs" class="form-label">Responsable</label>
                    <select name="chefs" id="chefs" class="form-select @error('chefs') is-invalid @enderror">
                        <option value="">Non defini</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('chefs', $organisation->chefs) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('chefs')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="introduction" class="form-label">Introduction</label>
                    <input type="text" name="introduction" id="introduction" class="form-control @error('introduction') is-invalid @enderror" value="{{ old('introduction', $organisation->introduction) }}" placeholder="Courte description...">
                    @error('introduction')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Description detaillee...">{{ old('description', $organisation->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select">
                        <option value="1" {{ old('statut', $organisation->statut) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ old('statut', $organisation->statut) == 0 ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Mettre a jour
                </button>
                <a href="{{ route('systeme.organisations.show', $organisation) }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
