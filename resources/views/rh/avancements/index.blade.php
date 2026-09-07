@extends('layouts.app')

@section('title', 'Avancements — ' . $employee->prenoms . ' ' . $employee->noms)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rh.employees.index') }}">Employés</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rh.employees.show', $employee) }}">{{ $employee->prenoms }} {{ $employee->noms }}</a></li>
        <li class="breadcrumb-item active">Avancements</li>
    </ol>
@endsection

@section('content')
<div class="row g-3">
    {{-- Colonne gauche : profil rapide + grade actuel --}}
    <div class="col-lg-4">
        <div class="card data-card">
            <div class="card-body text-center">
                <div style="width:64px; height:64px; background:#D1FAE5; color:#059669; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:800; margin-bottom:.75rem;">
                    {{ mb_substr($employee->prenoms, 0, 1) }}{{ mb_substr($employee->noms, 0, 1) }}
                </div>
                <h1 class="h5 mb-1">{{ $employee->prenoms }} {{ $employee->noms }}</h1>
                <p class="text-muted mb-0" style="font-size:.85rem;">
                    {{ $employee->poste ?: '—' }}
                    @if($employee->departement) · {{ $employee->departement }}@endif
                </p>
                @if($employee->matricule)
                <p class="text-muted" style="font-size:.75rem;">Matricule : <code>{{ $employee->matricule }}</code></p>
                @endif
            </div>
        </div>

        <div class="card data-card mt-3">
            <div class="card-header" style="background:#F8FAFC;">
                <h6 class="mb-0" style="font-size:.85rem; font-weight:700;">
                    <i class="fas fa-medal me-2" style="color:#059669;"></i>Grade actuel
                </h6>
            </div>
            <div class="card-body">
                @if($employee->grade)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <code style="background:#D1FAE5; color:#047857; padding:.15rem .45rem; border-radius:3px;">{{ $employee->grade->code }}</code>
                        <strong>{{ $employee->grade->libelle }}</strong>
                    </div>
                    @if($employee->grade->description)
                    <p class="text-muted mb-0" style="font-size:.85rem;">{{ $employee->grade->description }}</p>
                    @endif
                @else
                    <p class="text-muted mb-0" style="font-size:.85rem; font-style:italic;">Aucun grade attribué. Enregistrez un premier avancement pour l'établir.</p>
                @endif
            </div>
        </div>

        @can('update:employee')
        <div class="card data-card mt-3">
            <div class="card-header" style="background:#F8FAFC;">
                <h6 class="mb-0" style="font-size:.85rem; font-weight:700;">
                    <i class="fas fa-plus me-2" style="color:#059669;"></i>Nouvel avancement
                </h6>
            </div>
            <form action="{{ route('rh.employees.avancements.store', $employee) }}" method="POST" class="card-body">@csrf
                <div class="mb-3">
                    <label class="form-label">Nouveau grade <span class="text-danger">*</span></label>
                    <select name="grade_id" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($grades as $g)
                            <option value="{{ $g->id }}" @selected(old('grade_id') == $g->id)>{{ $g->code }} · {{ $g->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date d'effet <span class="text-danger">*</span></label>
                    <input type="date" name="date_effet" class="form-control" required value="{{ old('date_effet', now()->toDateString()) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif</label>
                    <input type="text" name="motif" class="form-control" maxlength="255" placeholder="Ex : Promotion suite évaluation 2026">
                </div>
                <div class="mb-3">
                    <label class="form-label">Référence du document</label>
                    <input type="text" name="reference_document" class="form-control" maxlength="100" placeholder="N° note de service / décision">
                </div>
                <div class="mb-3">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire" class="form-control" rows="2" maxlength="2000"></textarea>
                </div>
                <button class="btn w-100" style="background:#059669; color:#fff;">
                    <i class="fas fa-check me-1"></i>Enregistrer l'avancement
                </button>
            </form>
        </div>
        @endcan
    </div>

    {{-- Colonne droite : timeline historique --}}
    <div class="col-lg-8">
        <div class="card data-card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:#F8FAFC;">
                <h6 class="mb-0" style="font-size:.9rem; font-weight:700;">
                    <i class="fas fa-timeline me-2" style="color:#059669;"></i>Historique d'avancement
                </h6>
                <span class="badge" style="background:#F1F5F9; color:#334155;">{{ $employee->avancements->count() }} entrée(s)</span>
            </div>

            <div class="p-3">
                @forelse($employee->avancements as $a)
                <div class="position-relative ps-4 pb-3" style="border-left:2px solid #E5E7EB; margin-left:.5rem;">
                    <span style="position:absolute; left:-9px; top:0; width:16px; height:16px; border-radius:50%; background:#059669; border:3px solid #fff; box-shadow:0 0 0 2px #059669;"></span>

                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                @if($a->gradePrecedent)
                                    <span class="badge" style="background:#F1F5F9; color:#64748B;">{{ $a->gradePrecedent->code }}</span>
                                    <i class="fas fa-arrow-right text-muted" style="font-size:.75rem;"></i>
                                @endif
                                <strong style="color:#0F172A;">{{ $a->grade->libelle }}</strong>
                                <code style="background:#D1FAE5; color:#047857; padding:.1rem .3rem; border-radius:3px; font-size:.75rem;">{{ $a->grade->code }}</code>
                            </div>
                            <div class="text-muted" style="font-size:.75rem; margin-top:.2rem;">
                                Prise d'effet : <strong>{{ $a->date_effet->format('d/m/Y') }}</strong>
                                @if($a->decideur) · Décidé par {{ $a->decideur->name }}@endif
                                @if($a->reference_document) · Réf. {{ $a->reference_document }}@endif
                            </div>
                        </div>
                        @can('delete:employee')
                        <form action="{{ route('rh.employees.avancements.destroy', [$employee, $a]) }}" method="POST" onsubmit="return confirm('Supprimer cette entrée d\'avancement ? Le grade actuel sera recalculé à partir de l\'entrée précédente.');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </form>
                        @endcan
                    </div>

                    @if($a->motif)
                    <div style="font-size:.85rem; margin-top:.35rem;"><strong>Motif :</strong> {{ $a->motif }}</div>
                    @endif
                    @if($a->commentaire)
                    <div class="text-muted" style="font-size:.82rem; margin-top:.25rem;">{{ $a->commentaire }}</div>
                    @endif
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="fas fa-timeline d-block mb-2" style="font-size:1.75rem; opacity:.4;"></i>
                    Aucun avancement enregistré pour cet employé.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
