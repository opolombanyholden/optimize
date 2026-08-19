@extends('layouts.app')
@section('title', $client->raison_sociale)
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.clients.index') }}">Clients</a></li>
        <li class="breadcrumb-item active">{{ $client->raison_sociale }}</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $client->raison_sociale }}</h1>
        <p class="text-muted mb-0">
            <code>{{ $client->code }}</code>
            @if($client->forme_juridique) · {{ $client->forme_juridique }} @endif
            @if($client->statut) <span class="badge bg-success ms-2">Actif</span> @else <span class="badge bg-secondary ms-2">Inactif</span> @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.clients.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @can('update:client')
            <a href="{{ route('finance.clients.edit', $client) }}" class="btn btn-primary"><i class="fas fa-pen me-1"></i> Modifier</a>
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card data-card">
            <div class="card-header"><strong><i class="fas fa-id-card me-1"></i> Identité</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">NIF</dt><dd class="col-sm-8">{{ $client->nif ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">RCCM</dt><dd class="col-sm-8">{{ $client->rccm ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Adresse</dt><dd class="col-sm-8">{{ $client->adresse ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Ville / Pays</dt><dd class="col-sm-8">{{ $client->ville ?? '—' }} · {{ $client->pays ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Téléphone</dt><dd class="col-sm-8">{{ $client->telephone ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Email</dt><dd class="col-sm-8">{{ $client->email ?? '—' }}</dd>
                    @if($client->site_web)
                        <dt class="col-sm-4 text-muted">Site web</dt><dd class="col-sm-8"><a href="{{ $client->site_web }}" target="_blank">{{ $client->site_web }}</a></dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card data-card">
            <div class="card-header"><strong><i class="fas fa-user me-1"></i> Contact principal</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Nom</dt><dd class="col-sm-8">{{ $client->contact_nom ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Téléphone</dt><dd class="col-sm-8">{{ $client->contact_telephone ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">Email</dt><dd class="col-sm-8">{{ $client->contact_email ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card data-card mt-3">
            <div class="card-header"><strong><i class="fas fa-university me-1"></i> Banque</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Banque</dt><dd class="col-sm-8">{{ $client->banque ?? '—' }}</dd>
                    <dt class="col-sm-4 text-muted">RIB</dt><dd class="col-sm-8"><code class="small">{{ $client->rib ?? '—' }}</code></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><i class="fas fa-file-invoice me-1"></i> Dernières factures</strong>
                @can('create:facture')
                    <a href="{{ route('finance.factures.create', ['sens' => 'recette', 'tiers_type' => 'client', 'tiers_id' => $client->id]) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Nouvelle facture
                    </a>
                @endcan
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>N°</th><th>Date</th><th>Objet</th><th class="text-end">Montant TTC</th><th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($factures as $f)
                        <tr>
                            <td><a href="{{ route('finance.factures.show', $f) }}">{{ $f->numero }}</a></td>
                            <td><small>{{ $f->date_emission?->format('d/m/Y') }}</small></td>
                            <td>{{ \Illuminate\Support\Str::limit($f->objet, 50) }}</td>
                            <td class="text-end">{{ number_format((float) $f->montant_ttc, 0, ',', ' ') }}</td>
                            <td><span class="badge bg-{{ $f->statut_couleur }}">{{ $f->statut_libelle }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucune facture pour ce client.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($client->notes)
    <div class="alert alert-info mt-3">
        <strong>Notes :</strong> {{ $client->notes }}
    </div>
@endif
@endsection
