@extends('layouts.app')

@section('title', 'Grand Livre')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item active">Grand Livre</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Grand Livre</h1>
        <p class="text-muted mb-0">
            Consultation des &eacute;critures comptables.
            <span class="badge" style="background:#EEF2FF;color:#4338CA;font-weight:600;">Lecture seule</span>
            &mdash; les &eacute;critures sont g&eacute;n&eacute;r&eacute;es via les <a href="{{ route('finance.ordres.index') }}">ordres de d&eacute;pense/recette</a>.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        {{-- Dropdown export multi-format --}}
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-arrow-down me-1"></i>Exporter
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('finance.grand-livre.export', array_merge(request()->query(), ['format' => 'csv'])) }}">
                    <i class="fas fa-file-csv me-2" style="color:#0891B2;"></i>CSV
                </a></li>
                <li><a class="dropdown-item" href="{{ route('finance.grand-livre.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}">
                    <i class="fas fa-file-excel me-2" style="color:#059669;"></i>Excel (XLSX)
                </a></li>
                <li><a class="dropdown-item" href="{{ route('finance.grand-livre.export', array_merge(request()->query(), ['format' => 'pdf'])) }}">
                    <i class="fas fa-file-pdf me-2" style="color:#DC2626;"></i>PDF
                </a></li>
                <li><a class="dropdown-item" href="{{ route('finance.grand-livre.export', array_merge(request()->query(), ['format' => 'docx'])) }}">
                    <i class="fas fa-file-word me-2" style="color:#2563EB;"></i>Word (DOCX)
                </a></li>
            </ul>
        </div>
        @can('create:grandlivre')
        <a href="{{ route('finance.grand-livre.import.form') }}" class="btn btn-outline-secondary">
            <i class="fas fa-file-arrow-up me-1"></i>Importer (CSV / XLSX)
        </a>
        @endcan
        @can('read:operation')
        <a href="{{ route('finance.ordres.index') }}" class="btn" style="background:#4F46E5;color:#fff;">
            <i class="fas fa-file-signature me-1"></i>Aller aux ordres
        </a>
        @endcan
    </div>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('finance.grand-livre.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="search" class="form-label">Rechercher</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="R&eacute;f&eacute;rence, libell&eacute;..." value="{{ request('search') }}">
            </div>
            <div class="col-12 col-md-3">
                <label for="exercice_id" class="form-label">Exercice</label>
                <select name="exercice_id" id="exercice_id" class="form-select">
                    <option value="">Tous les exercices</option>
                    @foreach($exercices as $exercice)
                        <option value="{{ $exercice->id }}" {{ request('exercice_id') == $exercice->id ? 'selected' : '' }}>
                            {{ $exercice->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label for="date_debut" class="form-label">Date d&eacute;but</label>
                <input type="date" name="date_debut" id="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-12 col-md-2">
                <label for="date_fin" class="form-label">Date fin</label>
                <input type="date" name="date_fin" id="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('finance.grand-livre.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-book-open me-2 text-muted"></i>Liste des &eacute;critures</h5>
        <span class="text-muted small">{{ $ecritures->total() }} r&eacute;sultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($ecritures->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune &eacute;criture trouv&eacute;e</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>R&eacute;f&eacute;rence</th>
                            <th>Libell&eacute;</th>
                            <th>Exercice</th>
                            <th class="text-end">D&eacute;bit</th>
                            <th class="text-end">Cr&eacute;dit</th>
                            <th class="text-end">Solde</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $solde = 0; @endphp
                        @foreach($ecritures as $ecriture)
                        @php $solde += ($ecriture->montant_debit ?? 0) - ($ecriture->montant_credit ?? 0); @endphp
                        <tr>
                            <td>{{ $ecriture->date_ecriture ? \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') : '-' }}</td>
                            <td><strong>{{ $ecriture->reference ?? '-' }}</strong></td>
                            <td>{{ $ecriture->libelle ?? '-' }}</td>
                            <td>{{ $ecriture->exercice->libelle ?? '-' }}</td>
                            <td class="text-end">
                                @if($ecriture->montant_debit > 0)
                                    {{ number_format($ecriture->montant_debit, 0, ',', ' ') }} F
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($ecriture->montant_credit > 0)
                                    {{ number_format($ecriture->montant_credit, 0, ',', ' ') }} F
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ $solde >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($solde, 0, ',', ' ') }} F
                            </td>
                            <td class="text-end">
                                <a href="{{ route('finance.grand-livre.show', $ecriture) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Totaux :</td>
                            <td class="text-end">{{ number_format($ecritures->sum('montant_debit'), 0, ',', ' ') }} F</td>
                            <td class="text-end">{{ number_format($ecritures->sum('montant_credit'), 0, ',', ' ') }} F</td>
                            <td class="text-end {{ $solde >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($solde, 0, ',', ' ') }} F</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-center mt-4">
    {{ $ecritures->links() }}
</div>
@endsection
