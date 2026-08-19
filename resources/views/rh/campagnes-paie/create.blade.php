@extends('layouts.app')

@section('title', 'Nouvelle campagne de paie')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.campagnes-paie.index') }}">Campagnes de paie</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div><h1><i class="fas fa-rocket me-2 text-primary"></i> Nouvelle campagne de paie</h1></div>
    <a href="{{ route('rh.campagnes-paie.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('rh.campagnes-paie.store') }}" method="POST">
    @csrf

    {{-- Période et type --}}
    <div class="card data-card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-calendar me-2 text-muted"></i> Période & type</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control @error('libelle') is-invalid @enderror"
                           value="{{ old('libelle', 'Paie ' . now()->translatedFormat('F Y')) }}" required>
                    @error('libelle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Année <span class="text-danger">*</span></label>
                    <select name="annee" class="form-select" required>
                        @for($y = now()->year + 1; $y >= 2020; $y--)
                            <option value="{{ $y }}" @selected(old('annee', now()->year) == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Mois <span class="text-danger">*</span></label>
                    <select name="mois" class="form-select" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected(old('mois', now()->month) == $m)>{{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }} ({{ $m }})</option>
                        @endfor
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Périodicité</label>
                    <select name="periodicite" class="form-select">
                        <option value="mensuelle" @selected(old('periodicite', 'mensuelle') === 'mensuelle')>Mensuelle</option>
                        <option value="quinzaine" @selected(old('periodicite') === 'quinzaine')>Quinzaine</option>
                        <option value="hebdomadaire" @selected(old('periodicite') === 'hebdomadaire')>Hebdomadaire</option>
                        <option value="exceptionnelle" @selected(old('periodicite') === 'exceptionnelle')>Exceptionnelle (prime/bonus)</option>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Date paiement prévue</label>
                    <input type="date" name="date_paiement_prevue" class="form-control" value="{{ old('date_paiement_prevue') }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label d-block">Mode</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="simulation" id="modeReelle" value="0" @checked(old('simulation', '0') === '0')>
                        <label class="form-check-label" for="modeReelle"><i class="fas fa-circle-check text-primary me-1"></i> Campagne réelle</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="simulation" id="modeSim" value="1" @checked(old('simulation') === '1')>
                        <label class="form-check-label" for="modeSim"><i class="fas fa-vial text-info me-1"></i> Simulation</label>
                    </div>
                    <small class="form-text text-muted d-block">La simulation n'impacte ni la masse salariale ni les paiements.</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire" class="form-control" rows="2">{{ old('commentaire') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Sélection des employés --}}
    <div class="card data-card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="fas fa-users me-2 text-muted"></i> Sélection des employés</h5></div>
        <div class="card-body">
            @php
                // Si on arrive via /rh/campagnes-paie/create?echantillon=ID, on pré-sélectionne ce mode
                $modeDefault = old('mode_selection', $echantillonId ? 'par_echantillon' : 'tous_actifs');
            @endphp
            <div class="btn-group w-100 mb-3 flex-wrap" role="group">
                <input type="radio" class="btn-check" name="mode_selection" id="msTous" value="tous_actifs" @checked($modeDefault === 'tous_actifs')>
                <label class="btn btn-outline-primary" for="msTous"><i class="fas fa-users me-1"></i> Tous actifs</label>

                <input type="radio" class="btn-check" name="mode_selection" id="msDept" value="par_departement" @checked($modeDefault === 'par_departement')>
                <label class="btn btn-outline-success" for="msDept"><i class="fas fa-sitemap me-1"></i> Par département</label>

                <input type="radio" class="btn-check" name="mode_selection" id="msEch" value="par_echantillon" @checked($modeDefault === 'par_echantillon')>
                <label class="btn btn-outline-info" for="msEch"><i class="fas fa-layer-group me-1"></i> Par échantillon</label>

                <input type="radio" class="btn-check" name="mode_selection" id="msManu" value="manuel" @checked($modeDefault === 'manuel')>
                <label class="btn btn-outline-warning" for="msManu"><i class="fas fa-hand-pointer me-1"></i> Manuelle</label>
            </div>

            {{-- Mode "par département" --}}
            <div id="zoneDept" class="ms-fields" style="display:none;">
                <label class="form-label">Départements concernés</label>
                @php $depts = \App\Models\Employee::where('statut',1)->whereNotNull('departement')->distinct()->orderBy('departement')->pluck('departement'); @endphp
                <select name="departements[]" id="departements" multiple class="form-select tom-select-rh">
                    @foreach($depts as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mode "par échantillon" --}}
            <div id="zoneEch" class="ms-fields" style="display:none;">
                <label class="form-label">Échantillon à utiliser <span class="text-danger">*</span></label>
                @if($echantillons->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Aucun échantillon disponible.
                        <a href="{{ route('rh.echantillons-paie.create') }}">Créer le premier →</a>
                    </div>
                @else
                    <select name="echantillon_id" id="echantillon_id" class="form-select @error('echantillon_id') is-invalid @enderror">
                        <option value="">— Choisir un échantillon —</option>
                        @foreach($echantillons as $ech)
                            <option value="{{ $ech->id }}" @selected(old('echantillon_id', $echantillonId) == $ech->id)>
                                {{ $ech->libelle }} ({{ $ech->employes_count }} employé(s))
                            </option>
                        @endforeach
                    </select>
                    @error('echantillon_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-text text-muted">
                        <a href="{{ route('rh.echantillons-paie.index') }}" target="_blank"><i class="fas fa-cog me-1"></i>Gérer les échantillons</a>
                    </small>
                @endif
            </div>

            {{-- Mode "manuel" --}}
            <div id="zoneManu" class="ms-fields" style="display:none;">
                <label class="form-label">Employés à inclure ({{ $employees->count() }} actifs)</label>
                <select name="employee_ids[]" id="employee_ids" multiple class="form-select tom-select-rh">
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->matricule }} — {{ $e->noms }} {{ $e->prenoms }} @if($e->poste)<small>({{ $e->poste }})</small>@endif</option>
                    @endforeach
                </select>
            </div>

            {{-- Mode "tous actifs" --}}
            <div id="zoneTous" class="ms-fields">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-1"></i> <strong>{{ $employees->count() }}</strong> employé(s) actif(s) seront inclus dans la campagne.
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('rh.campagnes-paie.index') }}" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Créer la campagne</button>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select.tom-select-rh').forEach(function (el) {
        if (el.tomselect) return;
        new TomSelect(el, { plugins: ['remove_button'], maxOptions: 1000, create: false });
    });

    function refreshZones() {
        const v = document.querySelector('input[name="mode_selection"]:checked')?.value;
        document.getElementById('zoneTous').style.display = (v === 'tous_actifs') ? '' : 'none';
        document.getElementById('zoneDept').style.display = (v === 'par_departement') ? '' : 'none';
        document.getElementById('zoneEch').style.display  = (v === 'par_echantillon') ? '' : 'none';
        document.getElementById('zoneManu').style.display = (v === 'manuel') ? '' : 'none';
    }
    document.querySelectorAll('input[name="mode_selection"]').forEach(r => r.addEventListener('change', refreshZones));
    refreshZones();
});
</script>
@endpush
