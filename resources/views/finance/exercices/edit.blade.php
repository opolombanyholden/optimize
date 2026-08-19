@extends('layouts.app')

@section('title', 'Modifier exercice')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.exercices.index') }}">Exercices</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
@endsection

@section('content')
<div class="page-header">
    <h1>Modifier l'exercice</h1>
    <p class="text-muted mb-0">Modifiez les informations de l'exercice <strong>{{ $exercice->libelle }}</strong>.</p>
</div>

<div class="card data-card">
    <div class="card-header">
        <h5><i class="fas fa-edit me-2 text-muted"></i>Informations de l'exercice</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('finance.exercices.update', $exercice) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Libell&eacute; --}}
                <div class="col-12 col-md-6">
                    <label for="libelle" class="form-label">Libell&eacute; <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="libelle" class="form-control @error('libelle') is-invalid @enderror" value="{{ old('libelle', $exercice->libelle) }}" required>
                    @error('libelle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Exercice (ann&eacute;e) --}}
                <div class="col-12 col-md-6">
                    <label for="exercice" class="form-label">Exercice</label>
                    <input type="text" name="exercice" id="exercice" class="form-control @error('exercice') is-invalid @enderror" value="{{ old('exercice', $exercice->exercice) }}">
                    @error('exercice')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date d&eacute;but --}}
                <div class="col-12 col-md-6">
                    <label for="datedebut" class="form-label">Date de d&eacute;but</label>
                    <input type="date" name="datedebut" id="datedebut" class="form-control @error('datedebut') is-invalid @enderror" value="{{ old('datedebut', $exercice->datedebut) }}">
                    @error('datedebut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Date fin --}}
                <div class="col-12 col-md-6">
                    <label for="datefin" class="form-label">Date de fin</label>
                    <input type="date" name="datefin" id="datefin" class="form-control @error('datefin') is-invalid @enderror" value="{{ old('datefin', $exercice->datefin) }}">
                    @error('datefin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Budget global initial --}}
                <div class="col-12 col-md-6">
                    <label for="budgetglobalinitial" class="form-label">Budget global initial</label>
                    <div class="input-group">
                        <input type="number" name="budgetglobalinitial" id="budgetglobalinitial" class="form-control @error('budgetglobalinitial') is-invalid @enderror" value="{{ old('budgetglobalinitial', $exercice->budgetglobalinitial) }}" min="0" step="0.01">
                        <span class="input-group-text">F</span>
                        @error('budgetglobalinitial')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Dotation globale --}}
                <div class="col-12 col-md-6">
                    <label for="dotationglobale" class="form-label">Dotation globale</label>
                    <div class="input-group">
                        <input type="number" name="dotationglobale" id="dotationglobale" class="form-control @error('dotationglobale') is-invalid @enderror" value="{{ old('dotationglobale', $exercice->dotationglobale) }}" min="0" step="0.01">
                        <span class="input-group-text">F</span>
                        @error('dotationglobale')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Fond propre global --}}
                <div class="col-12 col-md-6">
                    <label for="fondpropreglobal" class="form-label">Fond propre global</label>
                    <div class="input-group">
                        <input type="number" name="fondpropreglobal" id="fondpropreglobal" class="form-control @error('fondpropreglobal') is-invalid @enderror" value="{{ old('fondpropreglobal', $exercice->fondpropreglobal) }}" min="0" step="0.01">
                        <span class="input-group-text">F</span>
                        @error('fondpropreglobal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Report budg&eacute;taire --}}
                <div class="col-12 col-md-6">
                    <label for="reportbudgetare" class="form-label">Report budg&eacute;taire</label>
                    <div class="input-group">
                        <input type="number" name="reportbudgetare" id="reportbudgetare" class="form-control @error('reportbudgetare') is-invalid @enderror" value="{{ old('reportbudgetare', $exercice->reportbudgetare) }}" min="0" step="0.01">
                        <span class="input-group-text">F</span>
                        @error('reportbudgetare')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Report tr&eacute;sorerie global --}}
                <div class="col-12 col-md-6">
                    <label for="reporttresorerieglobal" class="form-label">Report tr&eacute;sorerie global</label>
                    <div class="input-group">
                        <input type="number" name="reporttresorerieglobal" id="reporttresorerieglobal" class="form-control @error('reporttresorerieglobal') is-invalid @enderror" value="{{ old('reporttresorerieglobal', $exercice->reporttresorerieglobal) }}" min="0" step="0.01">
                        <span class="input-group-text">F</span>
                        @error('reporttresorerieglobal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Statut --}}
                <div class="col-12 col-md-6">
                    <label for="statut" class="form-label">Statut</label>
                    <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror">
                        <option value="3" {{ old('statut', $exercice->statut) == 3 ? 'selected' : '' }}>Brouillon</option>
                        <option value="1" {{ old('statut', $exercice->statut) == 1 ? 'selected' : '' }}>Actif</option>
                        <option value="2" {{ old('statut', $exercice->statut) == 2 ? 'selected' : '' }}>Cl&ocirc;tur&eacute;</option>
                    </select>
                    @error('statut')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Périmètre de planification --}}
                <div class="col-12">
                    <label class="form-label">Périmètre de planification <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        @foreach(\App\Models\Exercice::TYPES_PLANIFICATION as $tk => $tv)
                            @php $couleur = \App\Models\Exercice::TYPE_PLANIFICATION_COULEURS[$tk]; @endphp
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="type_planification" id="tp-{{ $tk }}" value="{{ $tk }}"
                                       @checked(old('type_planification', $exercice->type_planification ?? 'mixte') === $tk) required>
                                <label class="btn btn-outline-{{ $couleur }} w-100 text-start" for="tp-{{ $tk }}">
                                    <i class="fas {{ \App\Models\Exercice::TYPE_PLANIFICATION_ICONES[$tk] }} me-2"></i>
                                    <strong>{{ ucfirst($tk) }}</strong>
                                    <div class="small text-muted">{{ $tv }}</div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted mt-1 d-block">
                        <i class="fas fa-info-circle me-1"></i> Changer ce périmètre matérialisera les nouvelles lignes en scope. Les lignes hors scope existantes resteront (retirez-les manuellement si besoin).
                    </small>
                </div>

                {{-- Commentaire --}}
                <div class="col-12">
                    <label for="commentaire" class="form-label">Commentaire</label>
                    <textarea name="commentaire" id="commentaire" rows="3" class="form-control @error('commentaire') is-invalid @enderror">{{ old('commentaire', $exercice->commentaire) }}</textarea>
                    @error('commentaire')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('finance.exercices.index') }}" class="btn btn-outline-secondary">
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
