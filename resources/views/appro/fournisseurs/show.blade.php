@extends('layouts.app')

@section('title', 'Fournisseur - ' . $fournisseur->raison_sociale)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">Achats</li>
        <li class="breadcrumb-item"><a href="{{ route('appro.fournisseurs.index') }}">Fournisseurs</a></li>
        <li class="breadcrumb-item active">{{ $fournisseur->raison_sociale }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>{{ $fournisseur->raison_sociale }}</h1>
        <p class="text-muted mb-0">
            <span class="badge badge-status {{ $fournisseur->statut ? 'badge-actif' : 'badge-inactif' }}">
                {{ $fournisseur->statut ? 'Actif' : 'Inactif' }}
            </span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('appro.fournisseurs.edit', $fournisseur) }}" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Modifier
        </a>
        <a href="{{ route('appro.fournisseurs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

{{-- Informations du fournisseur --}}
<div class="card data-card mb-4">
    <div class="card-header">
        <h5><i class="fas fa-truck me-2 text-muted"></i>Informations du fournisseur</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Raison sociale</label>
                    <p class="mb-0">{{ $fournisseur->raison_sociale }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">NIF</label>
                    <p class="mb-0">{{ $fournisseur->nif ?? '-' }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Registre de commerce</label>
                    <p class="mb-0">{{ $fournisseur->registre_commerce ?? '-' }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Contact</label>
                    <p class="mb-0">{{ $fournisseur->contact ?? '-' }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Email</label>
                    <p class="mb-0">{{ $fournisseur->email ?? '-' }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Site web</label>
                    <p class="mb-0">
                        @if($fournisseur->site_web)
                            <a href="{{ $fournisseur->site_web }}" target="_blank">{{ $fournisseur->site_web }}</a>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Adresse</label>
                    <p class="mb-0">{{ $fournisseur->adresse ?? '-' }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Ville / Pays</label>
                    <p class="mb-0">{{ $fournisseur->ville ?? '-' }} {{ $fournisseur->pays ? '/ ' . $fournisseur->pays : '' }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Delai de livraison</label>
                    <p class="mb-0">{{ $fournisseur->delai_livraison ? $fournisseur->delai_livraison . ' jour(s)' : '-' }}</p>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Conditions de paiement</label>
                    <p class="mb-0">{{ $fournisseur->conditions_paiement ?? '-' }}</p>
                </div>
            </div>
            @if($fournisseur->commentaire)
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label text-muted fw-semibold">Commentaire</label>
                    <p class="mb-0">{{ $fournisseur->commentaire }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Commandes du fournisseur --}}
<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5><i class="fas fa-file-invoice me-2 text-muted"></i>Commandes</h5>
        <a href="{{ route('appro.commandes.create') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus me-1"></i>Nouvelle commande
        </a>
    </div>
    <div class="card-body p-0">
        @if(isset($commandes) && $commandes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>N&#176; Commande</th>
                            <th>Date commande</th>
                            <th>Date livraison prevue</th>
                            <th class="text-end">Montant total</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commandes as $commande)
                        <tr>
                            <td><strong>{{ $commande->numero_commande ?? '-' }}</strong></td>
                            <td>{{ $commande->date_commande ? $commande->date_commande->format('d/m/Y') : '-' }}</td>
                            <td>{{ $commande->date_livraison_prevue ? $commande->date_livraison_prevue->format('d/m/Y') : '-' }}</td>
                            <td class="text-end">{{ number_format($commande->montant_total ?? 0, 0, ',', ' ') }} F</td>
                            <td>
                                @php
                                    $badgeClass = match($commande->statut) {
                                        'livree' => 'badge-valide',
                                        'annulee' => 'badge-inactif',
                                        default => 'badge-en-attente',
                                    };
                                @endphp
                                <span class="badge badge-status {{ $badgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $commande->statut ?? 'en cours')) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('appro.commandes.show', $commande) }}" class="btn btn-sm btn-outline-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Aucune commande pour ce fournisseur</p>
            </div>
        @endif
    </div>
</div>
@endsection
