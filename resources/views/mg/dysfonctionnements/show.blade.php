@extends('layouts.app')
@section('title', $dysfonctionnement->label)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item"><a href="{{ route('mg.dysfonctionnements.index') }}">Dysfonctionnements</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($dysfonctionnement->label, 40) }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            {{ $dysfonctionnement->label }}
            <span class="badge bg-{{ $dysfonctionnement->statut_couleur }} ms-2">{{ $dysfonctionnement->statut_libelle }}</span>
        </h1>
        <p class="text-muted mb-0">
            <strong>Priorité :</strong> {{ ucfirst($dysfonctionnement->priorite ?? '—') }}
            · <strong>Immobilisation :</strong> {{ $dysfonctionnement->immobilisation?->designation ?? '—' }}
            · <strong>Signalé le :</strong> {{ $dysfonctionnement->date_signalement?->format('d/m/Y H:i') }}
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('update:dysfonctionnement')
            <a href="{{ route('mg.dysfonctionnements.edit', $dysfonctionnement) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
            @if($dysfonctionnement->peutEtrePrisEnCharge())
                <form action="{{ route('mg.dysfonctionnements.prendre-en-charge', $dysfonctionnement) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-info text-white"><i class="fas fa-hand-holding me-1"></i> Prendre en charge</button>
                </form>
            @endif
            @if($dysfonctionnement->peutEtreResolu())
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-resoudre"><i class="fas fa-check me-1"></i> Résoudre</button>
            @endif
            @if($dysfonctionnement->peutEtreFerme())
                <form action="{{ route('mg.dysfonctionnements.fermer', $dysfonctionnement) }}" method="POST" class="d-inline" onsubmit="return confirm('Fermer définitivement ?');">@csrf
                    <button class="btn btn-dark"><i class="fas fa-lock me-1"></i> Fermer</button>
                </form>
            @endif
        @endcan
        @can('create:intervention')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-intervention"><i class="fas fa-tools me-1"></i> Planifier intervention</button>
        @endcan
        <a href="{{ route('mg.dysfonctionnements.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-align-left me-2"></i> Description</h6></div>
            <div class="card-body">
                <p class="mb-0">{!! nl2br(e($dysfonctionnement->description ?: '— Aucune description')) !!}</p>
                @if($dysfonctionnement->commentaire_resolution)
                    <hr>
                    <p class="mb-0"><strong class="text-success">Résolution :</strong> {{ $dysfonctionnement->commentaire_resolution }}</p>
                @endif
            </div>
        </div>

        <div class="card data-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-tools me-2"></i> Interventions liées ({{ $dysfonctionnement->interventions->count() }})</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Libellé</th><th>Type</th><th>Technicien</th><th>Planifiée</th><th>Statut</th></tr></thead>
                    <tbody>
                    @forelse($dysfonctionnement->interventions as $i)
                        <tr>
                            <td><a href="{{ route('mg.interventions.show', $i) }}">{{ $i->label }}</a></td>
                            <td><small>{{ \App\Models\Intervention::TYPES[$i->type_intervention] ?? '—' }}</small></td>
                            <td>{{ $i->technicien?->name ?? '—' }}</td>
                            <td><small>{{ $i->date_planifiee?->format('d/m/Y') ?? '—' }}</small></td>
                            <td><span class="badge bg-{{ $i->statut_couleur }}">{{ $i->statut_libelle }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Aucune intervention planifiée.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Suivi</h6></div>
            <div class="card-body">
                <p class="mb-1"><strong>Déclarant :</strong> {{ $dysfonctionnement->declarant?->name ?? '—' }}</p>
                <p class="mb-1"><strong>Signalement :</strong> {{ $dysfonctionnement->date_signalement?->format('d/m/Y H:i') ?? '—' }}</p>
                @if($dysfonctionnement->pris_en_charge_at)
                    <p class="mb-1"><strong>Prise en charge :</strong> {{ $dysfonctionnement->pris_en_charge_at->format('d/m/Y H:i') }}<br><small class="text-muted">par {{ $dysfonctionnement->priseurEnCharge?->name }}</small></p>
                @endif
                @if($dysfonctionnement->date_resolution)
                    <p class="mb-1"><strong>Résolution :</strong> {{ $dysfonctionnement->date_resolution->format('d/m/Y H:i') }}<br><small class="text-muted">par {{ $dysfonctionnement->resoluteur?->name }}</small></p>
                @endif
                @if($dysfonctionnement->date_fermeture)
                    <p class="mb-0"><strong>Fermeture :</strong> {{ $dysfonctionnement->date_fermeture->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>

        @if($dysfonctionnement->piecesJointes->isNotEmpty())
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes</h6></div>
                <div class="card-body">
                    @include('mg._partials.pieces-jointes', ['pieces' => $dysfonctionnement->piecesJointes])
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ═════════ MODALES ═════════ --}}
@can('update:dysfonctionnement')
@if($dysfonctionnement->peutEtreResolu())
<div class="modal fade" id="modal-resoudre" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('mg.dysfonctionnements.resoudre', $dysfonctionnement) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-check text-success me-2"></i> Marquer résolu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Commentaire de résolution</label>
                <textarea name="commentaire_resolution" class="form-control" rows="4" maxlength="2000" placeholder="Décrire ce qui a été fait…">{{ old('commentaire_resolution') }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Confirmer</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

@can('create:intervention')
<div class="modal fade" id="modal-intervention" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('mg.dysfonctionnements.planifier-intervention', $dysfonctionnement) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-tools me-2"></i> Planifier une intervention</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="label" class="form-control" value="Intervention — {{ Str::limit($dysfonctionnement->label, 60) }}" required>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Type</label>
                        <select name="type_intervention" class="form-select">
                            @foreach(\App\Models\Intervention::TYPES as $k => $v)
                                <option value="{{ $k }}" @selected($k === 'corrective')>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date planifiée</label>
                        <input type="datetime-local" name="date_planifiee" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-primary"><i class="fas fa-plus me-1"></i> Planifier</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
