@csrf
<div class="card data-card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Intitulé <span class="text-danger">*</span></label>
                <input type="text" name="label" class="form-control" value="{{ old('label', $dysfonctionnement->label) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Priorité</label>
                <select name="priorite" class="form-select">
                    @foreach($priorites as $k => $v)
                        <option value="{{ $k }}" @selected(old('priorite', $dysfonctionnement->priorite) === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Type</label>
                <select name="type_id" class="form-select">
                    <option value="">—</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" @selected(old('type_id', $dysfonctionnement->type_id) == $t->id)>{{ $t->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Immobilisation liée</label>
                <select name="immobilisation_id" class="form-select">
                    <option value="">— Aucune</option>
                    @foreach($immobilisations as $imm)
                        <option value="{{ $imm->id }}" @selected(old('immobilisation_id', $dysfonctionnement->immobilisation_id) == $imm->id)>{{ $imm->code ? "[{$imm->code}] " : '' }}{{ $imm->designation }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Localisation</label>
                <input type="text" name="localisation" class="form-control" value="{{ old('localisation', $dysfonctionnement->localisation) }}">
            </div>
            @php
                $assignesActuels = collect(old('assignees'));
                if ($assignesActuels->isEmpty() && $dysfonctionnement->exists) {
                    $assignesActuels = $dysfonctionnement->assignationsActives->map(function ($a) {
                        $type = match ($a->assignable_type) {
                            \App\Models\Intranet\Service::class => 'service',
                            \App\Models\Organisation::class     => 'entite',
                            \App\Models\User::class             => 'user',
                            \App\Models\Intranet\Groupe::class  => 'groupe',
                            default => null,
                        };
                        return $type ? "{$type}:{$a->assignable_id}" : null;
                    })->filter()->values();
                }
            @endphp
            <div class="col-md-6">
                <label class="form-label">Services assignés</label>
                <select name="assignees[]" multiple class="form-select tomselect-assignees" placeholder="Rechercher un service…">
                    @foreach($servicesDispo as $s)
                        @php $val = "service:{$s->id}"; @endphp
                        <option value="{{ $val }}" @selected($assignesActuels->contains($val))>{{ $s->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Agent(s)</label>
                <select name="assignees[]" multiple class="form-select tomselect-assignees" placeholder="Rechercher un agent…">
                    @foreach($usersDispo as $u)
                        @php $val = "user:{$u->id}"; @endphp
                        <option value="{{ $val }}" @selected($assignesActuels->contains($val))>{{ trim(($u->prenoms ?? '').' '.$u->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $dysfonctionnement->description) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="card data-card mb-3">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-paperclip me-2"></i> Pièces jointes (photos, rapport…)</h6></div>
    <div class="card-body">
        <input type="file" name="pieces_jointes[]" class="form-control" multiple>
        @if($dysfonctionnement->exists && $dysfonctionnement->piecesJointes->isNotEmpty())
            <hr>
            @include('mg._partials.pieces-jointes', ['pieces' => $dysfonctionnement->piecesJointes])
        @endif
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    <a href="{{ route('mg.dysfonctionnements.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>

@once
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css"
          rel="stylesheet"
          integrity="sha384-piG3EtH1fBnPi68q4spy+Qgpb0dHK1D1dwk0GaHwFkvmUxYi526bBlk3xJcjEBsD"
          crossorigin="anonymous">
    @endpush
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"
            integrity="sha384-cnROoUgVILyibe3J0zhzWoJ9p2WmdnK7j/BOTSWqVDbC1pVw2d+i6Q/1ESKJKCYf"
            crossorigin="anonymous"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.tomselect-assignees').forEach(function (el) {
            new TomSelect(el, {
                plugins: ['remove_button'],
                maxOptions: 500,
                placeholder: el.getAttribute('placeholder') || 'Sélectionner…',
            });
        });
    });
    </script>
    @endpush
@endonce
