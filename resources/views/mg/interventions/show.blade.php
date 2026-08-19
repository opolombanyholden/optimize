@extends('layouts.app')
@section('title', $intervention->label)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item"><a href="{{ route('mg.interventions.index') }}">Interventions</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($intervention->label, 40) }}</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            {{ $intervention->label }}
            <span class="badge bg-{{ $intervention->statut_couleur }} ms-2">{{ $intervention->statut_libelle }}</span>
        </h1>
        <p class="text-muted mb-0">
            <strong>Type :</strong> {{ \App\Models\Intervention::TYPES[$intervention->type_intervention] ?? '—' }}
            · <strong>Technicien :</strong> {{ $intervention->technicien?->name ?? '— À affecter' }}
            @if($intervention->immobilisation)
                · <strong>Immobilisation :</strong> <a href="{{ route('mg.immobilisations.show', $intervention->immobilisation) }}">{{ $intervention->immobilisation->designation }}</a>
            @endif
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @can('update:intervention')
            <a href="{{ route('mg.interventions.edit', $intervention) }}" class="btn btn-outline-secondary"><i class="fas fa-pen me-1"></i> Modifier</a>
            @if($intervention->peutEtreDemarree())
                <form action="{{ route('mg.interventions.demarrer', $intervention) }}" method="POST" class="d-inline">@csrf
                    <button class="btn btn-info text-white"><i class="fas fa-play me-1"></i> Démarrer</button>
                </form>
            @endif
            @if($intervention->peutEtreTerminee())
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-terminer"><i class="fas fa-flag-checkered me-1"></i> Terminer</button>
            @endif
            @if($intervention->peutEtreAnnulee())
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modal-annuler"><i class="fas fa-ban me-1"></i> Annuler</button>
            @endif
        @endcan
        <a href="{{ route('mg.interventions.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

@if($intervention->dysfonctionnement)
    <div class="alert alert-info small">
        <i class="fas fa-link me-1"></i> Cette intervention traite le dysfonctionnement :
        <a href="{{ route('mg.dysfonctionnements.show', $intervention->dysfonctionnement) }}" class="alert-link">{{ $intervention->dysfonctionnement->label }}</a>
        <span class="badge bg-{{ $intervention->dysfonctionnement->statut_couleur }} ms-2">{{ $intervention->dysfonctionnement->statut_libelle }}</span>
    </div>
@endif

@if($intervention->statut === \App\Models\Intervention::STATUT_ANNULEE)
    <div class="alert alert-warning">
        <strong>Intervention annulée</strong> le {{ $intervention->annulee_at?->format('d/m/Y H:i') }}
        · <strong>Motif :</strong> {{ $intervention->motif_annulation }}
    </div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-align-left me-2"></i> Description</h6></div>
            <div class="card-body">
                <p class="mb-0">{!! nl2br(e($intervention->description ?: '— Aucune description')) !!}</p>
            </div>
        </div>

        @if($intervention->rapport)
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-file-alt me-2"></i> Rapport d'intervention</h6></div>
                <div class="card-body">
                    <p class="mb-0">{!! nl2br(e($intervention->rapport)) !!}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-4">
        <div class="card data-card mb-3">
            <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Suivi</h6></div>
            <div class="card-body">
                <p class="mb-1"><strong>Planifiée :</strong> {{ $intervention->date_planifiee?->format('d/m/Y H:i') ?? '—' }}</p>
                @if($intervention->date_debut)<p class="mb-1"><strong>Démarrée :</strong> {{ $intervention->date_debut->format('d/m/Y H:i') }}</p>@endif
                @if($intervention->date_fin)<p class="mb-1"><strong>Terminée :</strong> {{ $intervention->date_fin->format('d/m/Y H:i') }}</p>@endif
                <p class="mb-0"><strong>Coût :</strong> {{ $intervention->cout !== null ? number_format((float) $intervention->cout, 0, ',', ' ') . ' XAF' : '—' }}</p>
            </div>
        </div>

        @if($intervention->piecesJointes->isNotEmpty())
            <div class="card data-card mb-3">
                <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes</h6></div>
                <div class="card-body">
                    @include('mg._partials.pieces-jointes', ['pieces' => $intervention->piecesJointes])
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ═════════ MODALES ═════════ --}}
@can('update:intervention')
@if($intervention->peutEtreTerminee())
<div class="modal fade" id="modal-terminer" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('mg.interventions.terminer', $intervention) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-flag-checkered text-success me-2"></i> Terminer l'intervention</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nature du problème constaté</label>
                        <textarea name="nature_probleme" class="form-control" rows="2" maxlength="5000">{{ $intervention->nature_probleme }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Pistes de solution envisagées</label>
                        <textarea name="pistes_solution" class="form-control" rows="2" maxlength="5000">{{ $intervention->pistes_solution }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Solution appliquée</label>
                        <textarea name="solution_appliquee" class="form-control" rows="2" maxlength="5000">{{ $intervention->solution_appliquee }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Résultat constaté</label>
                        <textarea name="resultat" class="form-control" rows="2" maxlength="5000">{{ $intervention->resultat }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Statut de résolution <span class="text-danger">*</span></label>
                        <select name="statut_resolution" class="form-select" required>
                            <option value="">— Choisir</option>
                            @foreach(\App\Models\Intervention::RESOLUTIONS as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Coût réel (XAF)</label>
                        <input type="number" step="0.01" min="0" name="cout" class="form-control text-end" value="{{ $intervention->cout }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Rapport global (facultatif)</label>
                        <textarea name="rapport" class="form-control" rows="2" maxlength="5000">{{ $intervention->rapport }}</textarea>
                    </div>
                </div>
                <div class="alert alert-info small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i>
                    Le ticket ne sera clôturé automatiquement que si le statut est <strong>Résolu</strong>. Sinon il reste ouvert pour de nouvelles interventions.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Terminer</button>
            </div>
        </form>
    </div>
</div>
@endif

@if($intervention->peutEtreAnnulee())
<div class="modal fade" id="modal-annuler" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('mg.interventions.annuler', $intervention) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-ban text-warning me-2"></i> Annuler l'intervention</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Motif d'annulation <span class="text-danger">*</span></label>
                <textarea name="motif_annulation" class="form-control" rows="3" required minlength="5" maxlength="500"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                <button class="btn btn-warning"><i class="fas fa-check me-1"></i> Confirmer</button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan
@endsection
