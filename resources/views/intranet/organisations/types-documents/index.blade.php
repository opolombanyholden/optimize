@extends('layouts.app')
@section('title', 'Types de documents')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.organisations.index') }}">Organisations</a></li>
    <li class="breadcrumb-item active">Types de documents</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon"><i class="fas fa-folder-tree"></i></span>
                Types de documents
            </h1>
            <p class="page-subtitle">Référentiel des documents à fournir + matrice d'exigence par type d'organisation</p>
        </div>
        @can('create:organisation_crm')
        <button type="button" class="btn btn-intranet" data-bs-toggle="modal" data-bs-target="#modal-type-doc">
            <i class="fas fa-plus me-2"></i> Nouveau type
        </button>
        @endcan
    </div>

    {{-- ══════════ MATRICE D'EXIGENCE ══════════ --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i> Matrice d'exigence</h5>
            <small class="text-muted">O = Obligatoire · F = Facultatif · — = Non demandé</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('intranet.organisations.types-documents.matrice.save') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-3">
                        <thead class="table-light">
                            <tr>
                                <th style="width:24%">Document</th>
                                @foreach($typesOrganisation as $tk => $tv)
                                    <th class="text-center">
                                        <i class="fas {{ \App\Models\Intranet\ContactOrganisation::TYPE_ICONES[$tk] }} me-1"></i>
                                        {{ $tv }}
                                    </th>
                                @endforeach
                                <th style="width:6%">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($types as $type)
                            <tr>
                                <td>
                                    <i class="fas {{ $type->icone }} text-muted me-2"></i>
                                    <strong>{{ $type->libelle }}</strong>
                                    @if(!$type->actif)<span class="badge bg-secondary ms-1">Inactif</span>@endif
                                    @if($type->avec_expiration)
                                        <span class="badge bg-info ms-1" title="Ce document a une date d'expiration">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                    @endif
                                    <div class="text-muted small">{{ $type->code }}</div>
                                </td>
                                @foreach($typesOrganisation as $tk => $tv)
                                    @php
                                        $ex = $type->exigences->firstWhere('type_organisation', $tk);
                                        $niveau = $ex ? ($ex->obligatoire ? 'obligatoire' : 'facultatif') : '';
                                    @endphp
                                    <td class="text-center">
                                        <select name="matrice[{{ $type->id }}][{{ $tk }}]" class="form-select form-select-sm">
                                            <option value=""             @selected($niveau === '')>—</option>
                                            <option value="facultatif"   @selected($niveau === 'facultatif')>F</option>
                                            <option value="obligatoire"  @selected($niveau === 'obligatoire')>O</option>
                                        </select>
                                    </td>
                                @endforeach
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        @can('update:organisation_crm')
                                        <button type="button" class="btn btn-outline-secondary"
                                                data-bs-toggle="modal" data-bs-target="#modal-edit-type-{{ $type->id }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        @endcan
                                        @can('delete:organisation_crm')
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="if(confirm('Supprimer « {{ $type->libelle }} » et tous les documents associés ?')) document.getElementById('del-{{ $type->id }}').submit();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if($types->isEmpty())
                            <tr><td colspan="{{ count($typesOrganisation) + 2 }}" class="text-center text-muted py-4">
                                Aucun type de document. Créez-en un via le bouton ci-dessus.
                            </td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                @can('update:organisation_crm')
                <div class="text-end">
                    <button type="submit" class="btn btn-intranet"><i class="fas fa-save me-1"></i> Enregistrer la matrice</button>
                </div>
                @endcan
            </form>
        </div>
    </div>
</div>

{{-- Formulaires de suppression cachés --}}
@foreach($types as $type)
    <form id="del-{{ $type->id }}" method="POST" action="{{ route('intranet.organisations.types-documents.destroy', $type) }}" class="d-none">
        @csrf @method('DELETE')
    </form>
@endforeach

{{-- ══════════ MODALE : NOUVEAU TYPE ══════════ --}}
@can('create:organisation_crm')
<div class="modal fade" id="modal-type-doc" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.organisations.types-documents.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Nouveau type de document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="rccm, nif…" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Icône</label>
                        <input type="text" name="icone" class="form-control" placeholder="fa-file-lines" value="fa-file-lines">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" name="ordre" class="form-control" value="0">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="avec_expiration" value="1" id="new-avec-exp">
                            <label class="form-check-label" for="new-avec-exp">Avec date d'expiration</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet">Créer</button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- ══════════ MODALES : ÉDITION TYPES ══════════ --}}
@can('update:organisation_crm')
@foreach($types as $type)
<div class="modal fade" id="modal-edit-type-{{ $type->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('intranet.organisations.types-documents.update', $type) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen me-2"></i> {{ $type->libelle }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Libellé</label>
                        <input type="text" name="libelle" class="form-control" value="{{ $type->libelle }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Icône</label>
                        <input type="text" name="icone" class="form-control" value="{{ $type->icone }}">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ $type->description }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="ordre" class="form-control" value="{{ $type->ordre }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="avec_expiration" value="1" id="edit-exp-{{ $type->id }}" @checked($type->avec_expiration)>
                            <label class="form-check-label" for="edit-exp-{{ $type->id }}">Avec expiration</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="actif" value="1" id="edit-actif-{{ $type->id }}" @checked($type->actif)>
                            <label class="form-check-label" for="edit-actif-{{ $type->id }}">Actif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endcan
@endsection
