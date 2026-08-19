@extends('layouts.app')

@section('title', $echantillon->libelle)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.echantillons-paie.index') }}">Échantillons de paie</a></li>
        <li class="breadcrumb-item active">{{ $echantillon->libelle }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-3">
        @if($echantillon->icone)
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                  style="width:54px;height:54px;background:{{ $echantillon->couleur ?? '#0EA5E9' }}20;color:{{ $echantillon->couleur ?? '#0EA5E9' }};font-size:1.4rem;">
                <i class="fas {{ $echantillon->icone }}"></i>
            </span>
        @endif
        <div>
            <h1 class="h4 mb-1">{{ $echantillon->libelle }}</h1>
            <p class="text-muted mb-0">
                <code>{{ $echantillon->code }}</code> ·
                {{ $echantillon->employes->count() }} employé(s) ·
                @if($echantillon->statut)
                    <span class="badge bg-success">Actif</span>
                @else
                    <span class="badge bg-secondary">Archivé</span>
                @endif
            </p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.echantillons-paie.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @can('create:paie')
            <a href="{{ route('rh.campagnes-paie.create', ['echantillon' => $echantillon->id]) }}" class="btn btn-success">
                <i class="fas fa-rocket me-1"></i> Lancer une campagne
            </a>
        @endcan
        @can('update:paie')
            <a href="{{ route('rh.echantillons-paie.edit', $echantillon) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
        @endcan
    </div>
</div>

@if($echantillon->description)
    <div class="card data-card mb-3">
        <div class="card-body">
            <p class="mb-0 text-muted">{{ $echantillon->description }}</p>
        </div>
    </div>
@endif

<div class="card data-card">
    <div class="card-header"><strong><i class="fas fa-users me-1"></i> Employés inclus</strong></div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nom complet</th>
                    <th>Matricule</th>
                    <th>Département</th>
                    <th>Poste</th>
                    <th class="text-end">Salaire de base</th>
                </tr>
            </thead>
            <tbody>
                @forelse($echantillon->employes as $emp)
                    <tr>
                        <td>
                            <a href="{{ route('rh.employees.show', $emp) }}">{{ $emp->noms }} {{ $emp->prenoms }}</a>
                        </td>
                        <td><code class="small">{{ $emp->matricule ?? '—' }}</code></td>
                        <td>{{ $emp->departement ?? '—' }}</td>
                        <td>{{ $emp->poste ?? '—' }}</td>
                        <td class="text-end">{{ number_format($emp->salaire_base ?? 0, 0, ',', ' ') }} XAF</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucun employé dans cet échantillon.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="text-muted small mt-2">
    Créé par {{ $echantillon->createur?->name ?? '—' }} · {{ $echantillon->created_at?->translatedFormat('d M Y H:i') }}
    @if($echantillon->updated_at && $echantillon->updated_at->ne($echantillon->created_at))
        · Mise à jour : {{ $echantillon->updated_at->translatedFormat('d M Y H:i') }}
    @endif
</div>
@endsection
