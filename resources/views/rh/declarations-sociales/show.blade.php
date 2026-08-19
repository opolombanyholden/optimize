@extends('layouts.app')

@section('title', 'Déclaration ' . $declaration->code)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.declarations-sociales.index') }}">Déclarations sociales</a></li>
        <li class="breadcrumb-item active">{{ $declaration->code }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $declaration->organisme_libelle }} — {{ $declaration->periode_libelle }}</h1>
        <p class="text-muted mb-0">Code : <strong>{{ $declaration->code }}</strong> · Période : {{ $declaration->date_debut->format('d/m/Y') }} → {{ $declaration->date_fin->format('d/m/Y') }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.declarations-sociales.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        <a href="{{ route('rh.declarations-sociales.pdf', $declaration) }}" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf me-1"></i> PDF</a>
        <a href="{{ route('rh.declarations-sociales.csv', $declaration) }}" class="btn btn-success"><i class="fas fa-file-csv me-1"></i> CSV</a>
        @if($declaration->statut === 0)
            <form action="{{ route('rh.declarations-sociales.valider', $declaration) }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-primary"><i class="fas fa-check me-1"></i> Valider</button>
            </form>
        @elseif($declaration->statut === 1)
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDepot"><i class="fas fa-paper-plane me-1"></i> Marquer déposée</button>
        @endif
    </div>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card data-card"><div class="card-body">
            <div class="text-muted small">Employés</div>
            <div class="h4 mb-0">{{ $declaration->nombre_employes }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card data-card"><div class="card-body">
            <div class="text-muted small">Total brut</div>
            <div class="h4 mb-0">{{ number_format($declaration->total_brut, 0, ',', ' ') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card data-card"><div class="card-body">
            <div class="text-muted small">Brut plafonné</div>
            <div class="h4 mb-0">{{ number_format($declaration->total_brut_plafonne, 0, ',', ' ') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card data-card"><div class="card-body">
            <div class="text-muted small">Total à reverser</div>
            <div class="h4 mb-0 text-primary">{{ number_format($declaration->total_cot_salariale + $declaration->total_cot_patronale, 0, ',', ' ') }}</div>
        </div></div>
    </div>
</div>

{{-- Lignes détail --}}
<div class="card data-card">
    <div class="card-header"><strong>Détail par employé ({{ $declaration->lignes->count() }})</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Noms et prénoms</th>
                    <th>Mat. organisme</th>
                    <th>NIP</th>
                    <th class="text-end">Jours</th>
                    <th class="text-end">Brut</th>
                    <th class="text-end">Plafonné</th>
                    <th class="text-end">Cot. sal.</th>
                    <th class="text-end">Cot. pat.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($declaration->lignes as $l)
                    <tr>
                        <td>{{ $l->matricule_employeur ?? '—' }}</td>
                        <td>{{ $l->noms }} {{ $l->prenoms }}</td>
                        <td>{{ $l->matricule_organisme ?? '—' }}</td>
                        <td>{{ $l->nip ?? '—' }}</td>
                        <td class="text-end">{{ $l->nb_jours_travailles }}</td>
                        <td class="text-end">{{ number_format($l->brut, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($l->brut_plafonne, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($l->cot_salariale, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($l->cot_patronale, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="4">Totaux</td>
                    <td class="text-end">—</td>
                    <td class="text-end">{{ number_format($declaration->total_brut, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($declaration->total_brut_plafonne, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($declaration->total_cot_salariale, 0, ',', ' ') }}</td>
                    <td class="text-end">{{ number_format($declaration->total_cot_patronale, 0, ',', ' ') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Modal dépôt --}}
<div class="modal fade" id="modalDepot" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rh.declarations-sociales.deposer', $declaration) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Marquer la déclaration comme déposée</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Référence de dépôt <span class="text-danger">*</span></label>
                    <input type="text" name="reference_depot" class="form-control" required placeholder="N° accusé de réception">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-success">Confirmer le dépôt</button>
            </div>
        </form>
    </div>
</div>
@endsection
