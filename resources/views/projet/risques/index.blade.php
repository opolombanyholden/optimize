@extends('layouts.app')
@section('title', $projet->nom . ' — Risques')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Risques</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'risques'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-shield-halved"></i></span>
                Risques — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Registre des risques et matrice probabilité / impact</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalRisque">
            <i class="fas fa-plus me-2"></i> Nouveau risque
        </button>
    </div>

    {{-- Matrice 5x5 --}}
    <div class="form-card mb-4">
        <h5 class="mb-3"><i class="fas fa-th me-2" style="color:#0D9488;"></i> Matrice Probabilité / Impact</h5>
        @php
            $niveauColors = ['critique' => '#DC2626', 'eleve' => '#F59E0B', 'moyen' => '#0891B2', 'faible' => '#16A34A'];
            $matrice = [];
            foreach($risques as $r) {
                $key = $r->probabilite . '-' . $r->impact;
                $matrice[$key] = ($matrice[$key] ?? 0) + 1;
            }
            $cellColors = [
                '5-5'=>'#DC2626','5-4'=>'#DC2626','4-5'=>'#DC2626','4-4'=>'#DC2626','5-3'=>'#F59E0B','3-5'=>'#F59E0B',
                '4-3'=>'#F59E0B','3-4'=>'#F59E0B','5-2'=>'#F59E0B','2-5'=>'#F59E0B','3-3'=>'#F59E0B',
                '5-1'=>'#0891B2','1-5'=>'#0891B2','4-2'=>'#0891B2','2-4'=>'#0891B2','4-1'=>'#0891B2','1-4'=>'#0891B2','3-2'=>'#0891B2','2-3'=>'#0891B2',
                '3-1'=>'#16A34A','1-3'=>'#16A34A','2-2'=>'#16A34A','2-1'=>'#16A34A','1-2'=>'#16A34A','1-1'=>'#16A34A',
            ];
        @endphp
        <div class="table-responsive">
            <table style="border-collapse:collapse; text-align:center; width:100%; max-width:500px; margin:0 auto;">
                <thead>
                    <tr>
                        <th style="padding:6px; font-size:.75rem; width:60px;">P \ I</th>
                        @for($i=1; $i<=5; $i++)
                        <th style="padding:6px; font-size:.75rem;">{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @for($p=5; $p>=1; $p--)
                    <tr>
                        <td style="padding:6px; font-weight:600; font-size:.8rem;">{{ $p }}</td>
                        @for($i=1; $i<=5; $i++)
                        @php $k = $p.'-'.$i; $count = $matrice[$k] ?? 0; @endphp
                        <td style="padding:8px; background:{{ $cellColors[$k] ?? '#E5E7EB' }}20; border:1px solid #E5E7EB; border-radius:4px; min-width:50px;">
                            @if($count)
                            <span style="display:inline-block;width:28px;height:28px;line-height:28px;border-radius:50%;background:{{ $cellColors[$k] ?? '#6B7280' }};color:#fff;font-weight:600;font-size:.8rem;">{{ $count }}</span>
                            @endif
                        </td>
                        @endfor
                    </tr>
                    @endfor
                </tbody>
            </table>
            <div class="d-flex justify-content-center gap-3 mt-2" style="font-size:.75rem;">
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#DC2626;"></span> Critique</span>
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#F59E0B;"></span> Élevé</span>
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#0891B2;"></span> Moyen</span>
                <span><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#16A34A;"></span> Faible</span>
            </div>
        </div>
    </div>

    @if($risques->count())
    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Probabilité</th>
                    <th>Impact</th>
                    <th>Score</th>
                    <th>Niveau</th>
                    <th>Stratégie</th>
                    <th>Responsable</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($risques as $risque)
                @php
                    $score = $risque->probabilite * $risque->impact;
                    $niveau = $score >= 16 ? 'critique' : ($score >= 9 ? 'eleve' : ($score >= 4 ? 'moyen' : 'faible'));
                @endphp
                <tr>
                    <td><strong>{{ $risque->titre }}</strong></td>
                    <td>{{ $risque->categorie ?? '—' }}</td>
                    <td>{{ $risque->probabilite }}/5</td>
                    <td>{{ $risque->impact }}/5</td>
                    <td><strong>{{ $score }}</strong></td>
                    <td><span class="opp-stage-badge" style="background:{{ $niveauColors[$niveau] }};">{{ ucfirst($niveau) }}</span></td>
                    <td>{{ $risque->strategie ?? '—' }}</td>
                    <td>{{ $risque->responsable?->prenoms ?? '—' }}</td>
                    <td><span class="opp-stage-badge" style="background:{{ match($risque->statut) {
                        'identifie' => '#6366F1', 'en_cours' => '#F59E0B', 'resolu' => '#16A34A', 'survenu' => '#DC2626', default => '#6B7280'
                    } }};">{{ ucfirst(str_replace('_', ' ', $risque->statut)) }}</span></td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><button class="dropdown-item" onclick="editRisque({{ $risque->id }}, {{ json_encode([
                                    'titre' => $risque->titre, 'description' => $risque->description, 'categorie' => $risque->categorie,
                                    'probabilite' => $risque->probabilite, 'impact' => $risque->impact, 'strategie' => $risque->strategie,
                                    'plan_reponse' => $risque->plan_reponse, 'responsable_id' => $risque->responsable_id, 'statut' => $risque->statut
                                ]) }})">
                                    <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form action="{{ route('projet.risques.destroy', [$projet, $risque]) }}" method="POST" onsubmit="return confirm('Supprimer ce risque ?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                </form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-shield-halved" style="color:#0D9488;"></i></div>
        <h3>Aucun risque identifié</h3>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalRisque">
            <i class="fas fa-plus me-2"></i> Identifier un risque
        </button>
    </div>
    @endif
</div>

{{-- Modale risque --}}
<div class="modal fade" id="modalRisque" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="risqueForm" method="POST" class="modal-content" action="{{ route('projet.risques.store', $projet) }}">
            @csrf
            <input type="hidden" name="_method" id="risqueMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="risqueModalTitle">Nouveau risque</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="risqueTitre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" id="risqueDesc" rows="2" class="form-control"></textarea></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Catégorie</label>
                        <select name="categorie" id="risqueCategorie" class="form-select">
                            <option value="">— Aucune —</option>
                            <option value="technique">Technique</option>
                            <option value="organisationnel">Organisationnel</option>
                            <option value="externe">Externe</option>
                            <option value="gestion_projet">Gestion projet</option>
                            <option value="financier">Financier</option>
                            <option value="juridique">Juridique</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Probabilité <span class="text-danger">*</span></label>
                        <select name="probabilite" id="risqueProbabilite" class="form-select" required>
                            @for($i=1; $i<=5; $i++)
                            <option value="{{ $i }}">{{ $i }} — {{ ['Très faible','Faible','Moyenne','Élevée','Très élevée'][$i-1] }}</option>
                            @endfor
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Impact <span class="text-danger">*</span></label>
                        <select name="impact" id="risqueImpact" class="form-select" required>
                            @for($i=1; $i<=5; $i++)
                            <option value="{{ $i }}">{{ $i }} — {{ ['Négligeable','Mineur','Modéré','Majeur','Critique'][$i-1] }}</option>
                            @endfor
                        </select></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">Stratégie</label>
                        <select name="strategie" id="risqueStrategie" class="form-select">
                            <option value="">— Aucune —</option>
                            <option value="eviter">Éviter</option>
                            <option value="transferer">Transférer</option>
                            <option value="attenuer">Atténuer</option>
                            <option value="accepter">Accepter</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Responsable</label>
                        <select name="responsable_id" id="risqueResponsable" class="form-select">
                            <option value="">— Aucun —</option>
                            @foreach($utilisateurs ?? [] as $u)
                                <option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>
                            @endforeach
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Statut</label>
                        <select name="statut" id="risqueStatut" class="form-select">
                            <option value="identifie">Identifié</option>
                            <option value="en_cours">En cours</option>
                            <option value="resolu">Résolu</option>
                            <option value="survenu">Survenu</option>
                        </select></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Plan de réponse</label>
                    <textarea name="plan_reponse" id="risquePlan" rows="2" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-shield-halved me-2"></i> <span id="risqueSubmitText">Enregistrer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editRisque(id, data) {
    document.getElementById('risqueModalTitle').textContent = 'Modifier le risque';
    document.getElementById('risqueTitre').value = data.titre;
    document.getElementById('risqueDesc').value = data.description || '';
    document.getElementById('risqueCategorie').value = data.categorie || '';
    document.getElementById('risqueProbabilite').value = data.probabilite;
    document.getElementById('risqueImpact').value = data.impact;
    document.getElementById('risqueStrategie').value = data.strategie || '';
    document.getElementById('risqueResponsable').value = data.responsable_id || '';
    document.getElementById('risqueStatut').value = data.statut;
    document.getElementById('risquePlan').value = data.plan_reponse || '';
    document.getElementById('risqueMethod').value = 'PUT';
    document.getElementById('risqueSubmitText').textContent = 'Enregistrer';
    document.getElementById('risqueForm').action = '/projet/{{ $projet->id }}/risques/' + id;
    new bootstrap.Modal(document.getElementById('modalRisque')).show();
}

document.getElementById('modalRisque')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('risqueModalTitle').textContent = 'Nouveau risque';
    ['risqueTitre','risqueDesc','risquePlan'].forEach(id => document.getElementById(id).value = '');
    ['risqueCategorie','risqueStrategie','risqueResponsable'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('risqueProbabilite').value = '1';
    document.getElementById('risqueImpact').value = '1';
    document.getElementById('risqueStatut').value = 'identifie';
    document.getElementById('risqueMethod').value = 'POST';
    document.getElementById('risqueSubmitText').textContent = 'Enregistrer';
    document.getElementById('risqueForm').action = '{{ route("projet.risques.store", $projet) }}';
});
</script>
@endpush
@endsection
