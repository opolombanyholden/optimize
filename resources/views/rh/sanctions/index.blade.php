@extends('layouts.app')

@section('title', 'Sanctions disciplinaires')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Sanctions disciplinaires</li>
    </ol>
@endsection

@section('content')
@php
    $niveauCouleurs = [
        'mineure' => 'info',
        'modere'  => 'warning',
        'grave'   => 'danger',
        'severe'  => 'dark',
    ];
@endphp
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>Sanctions disciplinaires</h1>
        <p class="text-muted mb-0">Suivi des sanctions et mesures disciplinaires</p>
    </div>
    <a href="{{ route('rh.sanctions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nouvelle sanction
    </a>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('rh.sanctions.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label for="employee_id" class="form-label">Employe</label>
                <select name="employee_id" id="employee_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les employes</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->noms }} {{ $employee->prenoms }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="type" class="form-label">Type</label>
                <select name="type" id="type" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    @foreach(\App\Models\Sanction::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="niveau_gravite" class="form-label">Niveau de gravite</label>
                <select name="niveau_gravite" id="niveau_gravite" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous les niveaux</option>
                    @foreach(\App\Models\Sanction::NIVEAUX as $key => $label)
                        <option value="{{ $key }}" {{ request('niveau_gravite') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Filtrer
                </button>
                <a href="{{ route('rh.sanctions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-gavel me-2 text-muted"></i>Liste des sanctions</h5>
        <span class="text-muted">{{ $sanctions->total() }} resultat(s)</span>
    </div>
    <div class="card-body p-0">
        @if($sanctions->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune sanction trouvee</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Employe</th>
                            <th>Type</th>
                            <th>Motif</th>
                            <th>Date notification</th>
                            <th>Gravite</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sanctions as $sanction)
                        <tr>
                            <td>{{ $sanction->employee?->noms }} {{ $sanction->employee?->prenoms }}</td>
                            <td>{{ \App\Models\Sanction::TYPES[$sanction->type] ?? $sanction->type }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($sanction->motif, 40) }}</td>
                            <td>{{ $sanction->date_notification?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                @php $couleur = $niveauCouleurs[$sanction->niveau_gravite] ?? 'secondary'; @endphp
                                <span class="badge bg-{{ $couleur }}">
                                    {{ \App\Models\Sanction::NIVEAUX[$sanction->niveau_gravite] ?? $sanction->niveau_gravite }}
                                </span>
                            </td>
                            <td>
                                @if($sanction->statut == 0)
                                    <span class="badge badge-status badge-rejete">Annulee</span>
                                @elseif($sanction->statut == 1)
                                    <span class="badge badge-status badge-valide">Active</span>
                                @elseif($sanction->statut == 2)
                                    <span class="badge badge-status badge-en-attente">Archivee</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('rh.sanctions.show', $sanction) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('rh.sanctions.edit', $sanction) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('rh.sanctions.destroy', $sanction) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression de cette sanction ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Pagination --}}
@if($sanctions->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $sanctions->withQueryString()->links() }}
</div>
@endif
@endsection
