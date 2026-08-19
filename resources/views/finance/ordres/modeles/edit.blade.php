@extends('layouts.app')
@section('title', 'Modèle : ' . $modele->libelle)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.referentiels.ordres-modeles.index') }}">Modèles d'ordre</a></li>
    <li class="breadcrumb-item active">{{ $modele->libelle }}</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <h1 class="h3 mb-1">{{ $modele->libelle }}</h1>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <code>{{ $modele->code }}</code>
            <span class="badge bg-{{ $modele->sens_couleur }}">{{ \App\Models\Finance\OrdreModele::SENS[$modele->sens] ?? '—' }}</span>
            @if(!$modele->actif)<span class="badge bg-secondary">Inactif</span>@endif
        </div>
    </div>
</div>

{{-- ═════════ Paramètres du modèle ═════════ --}}
<form action="{{ route('finance.referentiels.ordres-modeles.update', $modele) }}" method="POST">
    @csrf @method('PUT')
    @include('finance.ordres.modeles._form')
</form>

{{-- ═════════ Champs du formulaire ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i> Champs du formulaire</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-champ">
            <i class="fas fa-plus me-1"></i> Ajouter un champ
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="table-champs">
            <thead class="table-light">
                <tr>
                    <th style="width:32px;"></th>
                    <th>Code</th>
                    <th>Label affiché</th>
                    <th>Type de saisie</th>
                    <th>Mapping Grand Livre</th>
                    <th class="text-center">Largeur</th>
                    <th class="text-center">Obligatoire</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="tbody-champs">
            @forelse($modele->champs as $c)
                <tr data-id="{{ $c->id }}">
                    <td class="text-muted drag-handle" style="cursor:grab;"><i class="fas fa-grip-vertical"></i></td>
                    <td><code>{{ $c->code_champ }}</code></td>
                    <td><strong>{{ $c->label_personnalise }}</strong>
                        @if($c->placeholder)<br><small class="text-muted">{{ $c->placeholder }}</small>@endif
                    </td>
                    <td><span class="badge bg-secondary">{{ $typesSaisie[$c->type_saisie] ?? $c->type_saisie }}</span></td>
                    <td>
                        @if($c->mapping_gl)
                            <small><code>{{ $c->mapping_gl }}</code></small>
                            <br><small class="text-muted">{{ $champsGlDispo[$c->mapping_gl] ?? '—' }}</small>
                        @else
                            <em class="text-muted small">—</em>
                        @endif
                    </td>
                    <td class="text-center">col-{{ $c->largeur_col }}</td>
                    <td class="text-center">
                        @if($c->obligatoire)
                            <i class="fas fa-check-circle text-success"></i>
                        @else
                            <i class="fas fa-minus text-muted"></i>
                        @endif
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modal-edit-champ-{{ $c->id }}">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form action="{{ route('finance.referentiels.ordres-modeles.champs.destroy', [$modele, $c]) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Supprimer ce champ ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun champ. Ajoutez le premier avec le bouton ci-dessus.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═════════ Signataires ═════════ --}}
<div class="card data-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-signature me-2"></i> Signataires</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-signataire">
            <i class="fas fa-plus me-1"></i> Ajouter un signataire
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:80px;" class="text-center">Ordre</th>
                    <th>Rôle</th>
                    <th>Signataire par défaut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($modele->signataires as $s)
                <tr>
                    <td class="text-center"><strong>{{ $s->ordre }}</strong></td>
                    <td><strong>{{ $s->role_libelle }}</strong></td>
                    <td>
                        @if($s->userParDefaut)
                            {{ $s->userParDefaut->name }}
                            @if($s->userParDefaut->prenoms) {{ $s->userParDefaut->prenoms }}@endif
                        @else
                            <em class="text-muted">— (à saisir lors de la signature)</em>
                        @endif
                    </td>
                    <td class="text-end">
                        <form action="{{ route('finance.referentiels.ordres-modeles.signataires.destroy', [$modele, $s]) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Retirer ce signataire ?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Aucun signataire.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═════════ MODALES : ajout champ ═════════ --}}
<div class="modal fade" id="modal-champ" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('finance.referentiels.ordres-modeles.champs.store', $modele) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus me-2"></i> Nouveau champ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                @include('finance.ordres.modeles._champ_form', ['champ' => null])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

{{-- ═════════ MODALES : édition champs ═════════ --}}
@foreach($modele->champs as $c)
<div class="modal fade" id="modal-edit-champ-{{ $c->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('finance.referentiels.ordres-modeles.champs.update', [$modele, $c]) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-pen me-2"></i> Modifier « {{ $c->label_personnalise }} »</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                @include('finance.ordres.modeles._champ_form', ['champ' => $c])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endforeach

{{-- ═════════ MODALE : ajout signataire ═════════ --}}
<div class="modal fade" id="modal-signataire" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('finance.referentiels.ordres-modeles.signataires.store', $modele) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-signature me-2"></i> Nouveau signataire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Rôle affiché <span class="text-danger">*</span></label>
                    <input type="text" name="role_libelle" class="form-control" required placeholder="L'AGENT COMPTABLE">
                </div>
                <div class="mb-3">
                    <label class="form-label">Signataire par défaut (optionnel)</label>
                    <select name="user_id_par_defaut" class="form-select">
                        <option value="">— Aucun (à saisir lors de la signature) —</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} {{ $u->prenoms }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ordre d'apparition</label>
                    <input type="number" name="ordre" class="form-control" placeholder="Auto — laissez vide">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"
        integrity="sha384-BbwUmvSGL5r8yUnwR6cO0lbc/QNZaC0KyOJKh0YbNTNutdlBgFsjJZFTRNwPY87b"
        crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tbody-champs');
    if (tbody && window.Sortable) {
        Sortable.create(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                const order = Array.from(tbody.querySelectorAll('tr[data-id]')).map(r => r.dataset.id);
                fetch(@json(route('finance.referentiels.ordres-modeles.champs.reorder', $modele)), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ordre: order }),
                });
            }
        });
    }
});
</script>
@endpush
@endsection
