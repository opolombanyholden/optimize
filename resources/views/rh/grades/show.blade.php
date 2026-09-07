@extends('layouts.app')

@section('title', 'Grade — ' . $grade->libelle)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rh.grades.index') }}">Grades</a></li>
        <li class="breadcrumb-item active">{{ $grade->libelle }}</li>
    </ol>
@endsection

@section('content')
<div class="row g-3">
    {{-- Colonne gauche : infos grade --}}
    <div class="col-lg-4">
        <div class="card data-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div style="width:44px; height:44px; background:#D1FAE5; color:#059669; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; font-size:1.15rem; margin-right:.75rem;">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div>
                        <h1 class="h5 mb-0">{{ $grade->libelle }}</h1>
                        <code style="background:#D1FAE5; color:#047857; padding:.1rem .35rem; border-radius:3px; font-size:.75rem;">{{ $grade->code }}</code>
                    </div>
                </div>

                @if($grade->description)
                <p class="text-muted" style="font-size:.9rem;">{{ $grade->description }}</p>
                @endif

                <dl class="row mb-0" style="font-size:.85rem;">
                    <dt class="col-5 text-muted">Ordre</dt>
                    <dd class="col-7">{{ $grade->ordre }}</dd>
                    <dt class="col-5 text-muted">Statut</dt>
                    <dd class="col-7">@if($grade->actif)<span class="badge bg-success">Actif</span>@else<span class="badge bg-secondary">Inactif</span>@endif</dd>
                    <dt class="col-5 text-muted">Employés</dt>
                    <dd class="col-7">{{ $grade->employees->count() }}</dd>
                    <dt class="col-5 text-muted">Critères</dt>
                    <dd class="col-7">{{ $grade->criteres->count() }}</dd>
                </dl>
            </div>
        </div>

        @if($grade->employees->isNotEmpty())
        <div class="card data-card mt-3">
            <div class="card-header" style="background:#F8FAFC;">
                <h6 class="mb-0" style="font-size:.85rem; font-weight:700;">Employés à ce grade</h6>
            </div>
            <ul class="list-group list-group-flush" style="font-size:.85rem;">
                @foreach($grade->employees->take(15) as $emp)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('rh.employees.show', $emp) }}" style="text-decoration:none; color:#0F172A; font-weight:600;">
                        {{ $emp->prenoms }} {{ $emp->noms }}
                    </a>
                    <small class="text-muted">{{ $emp->poste ?: '—' }}</small>
                </li>
                @endforeach
            </ul>
            @if($grade->employees->count() > 15)
            <div class="card-footer text-muted text-center" style="font-size:.75rem;">
                + {{ $grade->employees->count() - 15 }} autre(s)
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- Colonne droite : critères --}}
    <div class="col-lg-8">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#F8FAFC;">
                <h6 class="mb-0" style="font-size:.9rem; font-weight:700;">
                    <i class="fas fa-list-check me-2" style="color:#059669;"></i>Critères à remplir
                </h6>
                @can('update:employee')
                <button type="button" class="btn btn-sm" style="background:#059669; color:#fff;" data-bs-toggle="modal" data-bs-target="#createCritereModal">
                    <i class="fas fa-plus me-1"></i> Ajouter un critère
                </button>
                @endcan
            </div>

            @forelse($grade->criteres as $c)
            <div class="border-bottom p-3 d-flex justify-content-between align-items-start">
                <div style="flex:1;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge" style="background:#F1F5F9; color:#334155;">#{{ $c->ordre }}</span>
                        <strong style="color:#0F172A;">{{ $c->libelle }}</strong>
                        @if($c->obligatoire)
                        <span class="badge" style="background:#FEE2E2; color:#DC2626;">Obligatoire</span>
                        @else
                        <span class="badge" style="background:#F1F5F9; color:#64748B;">Facultatif</span>
                        @endif
                    </div>
                    @if($c->description)
                    <p class="text-muted mb-0" style="font-size:.85rem;">{{ $c->description }}</p>
                    @endif
                </div>
                @can('update:employee')
                <div class="d-flex gap-1 flex-shrink-0 ms-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCritereModal-{{ $c->id }}" title="Modifier">
                        <i class="fas fa-pen"></i>
                    </button>
                    <form action="{{ route('rh.grades.criteres.destroy', [$grade, $c]) }}" method="POST" onsubmit="return confirm('Supprimer ce critère ?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
                @endcan
            </div>
            @empty
            <div class="p-4 text-center text-muted">
                <i class="fas fa-list-check d-block mb-2" style="font-size:1.75rem; opacity:.4;"></i>
                Aucun critère défini pour ce grade.
                @can('update:employee')
                <br><small>Utilisez « Ajouter un critère » pour commencer.</small>
                @endcan
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Modales critères --}}
@can('update:employee')
<div class="modal fade" id="createCritereModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rh.grades.criteres.store', $grade) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2" style="color:#059669;"></i>Ajouter un critère</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Libellé du critère <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" class="form-control" required maxlength="255" placeholder="Ex : 5 ans d'ancienneté minimum">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description / précisions</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="2000"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="ordre" class="form-control" min="0" max="999" value="0">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="obligatoire" value="1" id="createOblig" checked>
                            <label class="form-check-label" for="createOblig">Critère obligatoire</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn" style="background:#059669; color:#fff;">Ajouter</button>
            </div>
        </form>
    </div>
</div>

@foreach($grade->criteres as $c)
<div class="modal fade" id="editCritereModal-{{ $c->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rh.grades.criteres.update', [$grade, $c]) }}" method="POST" class="modal-content">@csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen me-2" style="color:#059669;"></i>Modifier le critère</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" class="form-control" value="{{ $c->libelle }}" required maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="2000">{{ $c->description }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="ordre" class="form-control" min="0" max="999" value="{{ $c->ordre }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="obligatoire" value="1" id="editOblig-{{ $c->id }}" @checked($c->obligatoire)>
                            <label class="form-check-label" for="editOblig-{{ $c->id }}">Obligatoire</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn" style="background:#059669; color:#fff;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endcan
@endsection
