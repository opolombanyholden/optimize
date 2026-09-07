@extends('layouts.app')

@section('title', 'Grades')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
        <li class="breadcrumb-item">Référentiels</li>
        <li class="breadcrumb-item active">Grades</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-medal me-2" style="color:#059669;"></i>Grades</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Grades utilisés pour classer les employés (ex : Agent d'exécution, Agent de maîtrise, Cadre). Chaque grade regroupe des critères à remplir.</p>
    </div>
    @can('update:employee')
    <button type="button" class="btn" style="background:#059669; color:#fff;" data-bs-toggle="modal" data-bs-target="#createGradeModal">
        <i class="fas fa-plus me-1"></i> Nouveau grade
    </button>
    @endcan
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th style="width:8%; text-align:center;">Ordre</th>
                    <th style="width:12%;">Code</th>
                    <th style="width:22%;">Libellé</th>
                    <th style="width:16%;">Avancement</th>
                    <th style="width:10%; text-align:center;">Critères</th>
                    <th style="width:10%; text-align:center;">Employés</th>
                    <th style="width:8%; text-align:center;">Actif</th>
                    <th style="width:14%; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $g)
                <tr>
                    <td class="text-center"><span class="badge" style="background:#F1F5F9; color:#334155;">{{ $g->ordre }}</span></td>
                    <td><code style="background:#D1FAE5; color:#047857; padding:.15rem .4rem; border-radius:3px;">{{ $g->code }}</code></td>
                    <td><strong>{{ $g->libelle }}</strong>@if($g->description)<div class="text-muted" style="font-size:.72rem;">{{ \Illuminate\Support\Str::limit($g->description, 55) }}</div>@endif</td>
                    <td style="font-size:.82rem;">
                        @if($g->avancement_automatique)
                            <span class="badge" style="background:#D1FAE5; color:#059669;"><i class="fas fa-wand-magic-sparkles"></i> Auto</span>
                            @if($g->duree_max_mois)<div class="text-muted" style="font-size:.7rem; margin-top:.15rem;">Max {{ $g->duree_max_mois }} mois</div>@endif
                        @else
                            <span class="badge" style="background:#F1F5F9; color:#64748B;"><i class="fas fa-hand"></i> Manuel</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($g->criteres_count > 0)
                            <span class="badge" style="background:#E0E7FF; color:#4338CA;">{{ $g->criteres_count }}</span>
                        @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($g->employees_count > 0)
                            <span class="badge" style="background:#D1FAE5; color:#059669;">{{ $g->employees_count }}</span>
                        @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($g->actif)<span class="badge bg-success">Oui</span>@else<span class="badge bg-secondary">Non</span>@endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('rh.grades.show', $g) }}" class="btn btn-sm btn-outline-primary" title="Critères">
                            <i class="fas fa-list-check"></i>
                        </a>
                        @can('update:employee')
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editGradeModal-{{ $g->id }}" title="Modifier">
                            <i class="fas fa-pen"></i>
                        </button>
                        @endcan
                        @can('delete:employee')
                        <form action="{{ route('rh.grades.destroy', $g) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer le grade « {{ $g->libelle }} » ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" @disabled($g->employees_count > 0) title="{{ $g->employees_count > 0 ? 'Grade utilisé — non supprimable' : 'Supprimer' }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">
                    Aucun grade enregistré. Cliquez sur <strong>« Nouveau grade »</strong> pour commencer.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modale CRÉATION --}}
@can('update:employee')
<div class="modal fade" id="createGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rh.grades.store') }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-medal me-2" style="color:#059669;"></i>Nouveau grade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required maxlength="30" placeholder="AM">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" class="form-control" required maxlength="100" placeholder="Agent de maîtrise">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ordre</label>
                        <input type="number" name="ordre" class="form-control" min="0" max="999" value="0">
                        <div class="form-text">Hiérarchie affichée (0 = plus bas).</div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="actif" value="1" id="createActifG" checked>
                            <label class="form-check-label" for="createActifG">Actif</label>
                        </div>
                    </div>

                    <hr class="my-2">
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="avancement_automatique" value="1" id="createAutoG" onchange="document.getElementById('createAutoBlock').style.display=this.checked?'flex':'none';">
                            <label class="form-check-label" for="createAutoG"><strong>Avancement automatique</strong> — l'employé sera automatiquement proposé au grade suivant après la durée max</label>
                        </div>
                    </div>
                    <div class="col-12 row g-3" id="createAutoBlock" style="display:none;">
                        <div class="col-md-6">
                            <label class="form-label">Durée max avant avancement (mois)</label>
                            <input type="number" name="duree_max_mois" class="form-control" min="1" max="600" placeholder="36">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grade suivant (cible)</label>
                            <select name="grade_suivant_id" class="form-select">
                                <option value="">— Suivant par ordre (auto) —</option>
                                @foreach($grades as $gs)
                                    <option value="{{ $gs->id }}">{{ $gs->code }} · {{ $gs->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn" style="background:#059669; color:#fff;">Créer</button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- Modales ÉDITION --}}
@can('update:employee')
    @foreach($grades as $g)
    <div class="modal fade" id="editGradeModal-{{ $g->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('rh.grades.update', $g) }}" method="POST" class="modal-content">@csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2" style="color:#059669;"></i>Modifier « {{ $g->libelle }} »</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" value="{{ $g->code }}" required maxlength="30">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="libelle" class="form-control" value="{{ $g->libelle }}" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" maxlength="2000">{{ $g->description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ordre</label>
                            <input type="number" name="ordre" class="form-control" min="0" max="999" value="{{ $g->ordre }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="actif" value="1" id="editActifG-{{ $g->id }}" @checked($g->actif)>
                                <label class="form-check-label" for="editActifG-{{ $g->id }}">Actif</label>
                            </div>
                        </div>

                        <hr class="my-2">
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="avancement_automatique" value="1" id="editAutoG-{{ $g->id }}" @checked($g->avancement_automatique)
                                    onchange="document.getElementById('editAutoBlock-{{ $g->id }}').style.display=this.checked?'flex':'none';">
                                <label class="form-check-label" for="editAutoG-{{ $g->id }}"><strong>Avancement automatique</strong> après une durée max au grade</label>
                            </div>
                        </div>
                        <div class="col-12 row g-3" id="editAutoBlock-{{ $g->id }}" style="display:{{ $g->avancement_automatique ? 'flex' : 'none' }};">
                            <div class="col-md-6">
                                <label class="form-label">Durée max (mois)</label>
                                <input type="number" name="duree_max_mois" class="form-control" min="1" max="600" value="{{ $g->duree_max_mois }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Grade suivant (cible)</label>
                                <select name="grade_suivant_id" class="form-select">
                                    <option value="">— Suivant par ordre (auto) —</option>
                                    @foreach($grades as $gs)
                                        @if($gs->id !== $g->id)
                                        <option value="{{ $gs->id }}" @selected($g->grade_suivant_id == $gs->id)>{{ $gs->code }} · {{ $gs->libelle }}</option>
                                        @endif
                                    @endforeach
                                </select>
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
