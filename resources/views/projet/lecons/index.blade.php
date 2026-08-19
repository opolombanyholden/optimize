@extends('layouts.app')
@section('title', $projet->nom . ' — Leçons apprises')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Leçons apprises</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'lecons'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-lightbulb"></i></span>
                Leçons apprises — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Capitalisation des retours d'expérience du projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalLecon">
            <i class="fas fa-plus me-2"></i> Nouvelle leçon
        </button>
    </div>

    @if($lecons->count())
    <div class="row g-3">
        @foreach($lecons as $lecon)
        @php
            $typeColors = ['positive' => '#16A34A', 'negative' => '#DC2626', 'suggestion' => '#F59E0B'];
            $typeIcons = ['positive' => 'fa-thumbs-up', 'negative' => 'fa-thumbs-down', 'suggestion' => 'fa-lightbulb'];
            $typeLabels = ['positive' => 'Positive', 'negative' => 'Négative', 'suggestion' => 'Suggestion'];
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="contact-detail-card" style="border-top:3px solid {{ $typeColors[$lecon->type] ?? '#0D9488' }};">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="opp-stage-badge" style="background:{{ $typeColors[$lecon->type] ?? '#6B7280' }}; font-size:.72rem;">
                        <i class="fas {{ $typeIcons[$lecon->type] ?? 'fa-lightbulb' }} me-1"></i>
                        {{ $typeLabels[$lecon->type] ?? ucfirst($lecon->type) }}
                    </span>
                    <div class="dropdown">
                        <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                            <li><button class="dropdown-item" onclick="editLecon({{ $lecon->id }}, {{ json_encode([
                                'titre' => $lecon->titre, 'description' => $lecon->description, 'type' => $lecon->type,
                                'categorie' => $lecon->categorie, 'impact' => $lecon->impact, 'recommandation' => $lecon->recommandation,
                                'phase' => $lecon->phase
                            ]) }})">
                                <i class="fas fa-pen-to-square"></i> Modifier</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><form action="{{ route('projet.lecons.destroy', [$projet, $lecon]) }}" method="POST" onsubmit="return confirm('Supprimer cette leçon ?');">
                                @csrf @method('DELETE')
                                <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                            </form></li>
                        </ul>
                    </div>
                </div>

                <h5 style="font-size:.92rem; margin-bottom:.5rem;">{{ $lecon->titre }}</h5>

                @if($lecon->description)
                <p style="font-size:.82rem; color:#6B7280; margin-bottom:.5rem;">{{ Str::limit($lecon->description, 120) }}</p>
                @endif

                @if($lecon->categorie)
                <div class="mb-2" style="font-size:.78rem;">
                    <i class="fas fa-tag me-1" style="color:#0D9488;"></i> {{ $lecon->categorie }}
                </div>
                @endif

                @if($lecon->impact)
                <div class="mb-2" style="font-size:.78rem;">
                    <i class="fas fa-bolt me-1" style="color:#F59E0B;"></i> <strong>Impact:</strong> {{ Str::limit($lecon->impact, 80) }}
                </div>
                @endif

                @if($lecon->recommandation)
                <div style="font-size:.78rem; padding:8px; background:#F0FDFA; border-radius:6px; border-left:3px solid #0D9488;">
                    <i class="fas fa-arrow-right me-1" style="color:#0D9488;"></i> <strong>Recommandation:</strong> {{ Str::limit($lecon->recommandation, 100) }}
                </div>
                @endif

                <div class="mt-2 text-muted" style="font-size:.72rem;">
                    @if($lecon->phase)<i class="fas fa-sitemap me-1"></i> {{ $lecon->phase }} · @endif
                    <i class="fas fa-calendar me-1"></i> {{ $lecon->created_at->format('d/m/Y') }}
                    @if($lecon->auteur) · <i class="fas fa-user me-1"></i> {{ $lecon->auteur->prenoms }}@endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-lightbulb" style="color:#0D9488;"></i></div>
        <h3>Aucune leçon apprise</h3>
        <p>Documentez les retours d'expérience pour les futurs projets.</p>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalLecon">
            <i class="fas fa-plus me-2"></i> Ajouter une leçon
        </button>
    </div>
    @endif
</div>

{{-- Modale leçon --}}
<div class="modal fade" id="modalLecon" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="leconForm" method="POST" class="modal-content" action="{{ route('projet.lecons.store', $projet) }}">
            @csrf
            <input type="hidden" name="_method" id="leconMethod" value="POST">
            <div class="modal-header"><h5 class="modal-title" id="leconModalTitle">Nouvelle leçon apprise</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="titre" id="leconTitre" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" id="leconDesc" rows="3" class="form-control" required></textarea></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="leconType" class="form-select" required>
                            <option value="positive">Positive</option>
                            <option value="negative">Négative</option>
                            <option value="suggestion">Suggestion</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Catégorie</label>
                        <select name="categorie" id="leconCategorie" class="form-select">
                            <option value="">— Aucune —</option>
                            <option value="technique">Technique</option>
                            <option value="gestion">Gestion</option>
                            <option value="communication">Communication</option>
                            <option value="qualite">Qualité</option>
                            <option value="processus">Processus</option>
                            <option value="equipe">Équipe</option>
                            <option value="autre">Autre</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Phase</label>
                        <input type="text" name="phase" id="leconPhase" class="form-control" placeholder="Phase concernée"></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Impact</label>
                    <textarea name="impact" id="leconImpact" rows="2" class="form-control" placeholder="Quel a été l'impact sur le projet ?"></textarea></div>
                <div class="mb-3"><label class="form-label">Recommandation</label>
                    <textarea name="recommandation" id="leconRecommandation" rows="2" class="form-control" placeholder="Que recommanderiez-vous pour les prochains projets ?"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-lightbulb me-2"></i> <span id="leconSubmitText">Enregistrer</span></button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editLecon(id, data) {
    document.getElementById('leconModalTitle').textContent = 'Modifier la leçon';
    document.getElementById('leconTitre').value = data.titre;
    document.getElementById('leconDesc').value = data.description || '';
    document.getElementById('leconType').value = data.type;
    document.getElementById('leconCategorie').value = data.categorie || '';
    document.getElementById('leconPhase').value = data.phase || '';
    document.getElementById('leconImpact').value = data.impact || '';
    document.getElementById('leconRecommandation').value = data.recommandation || '';
    document.getElementById('leconMethod').value = 'PUT';
    document.getElementById('leconSubmitText').textContent = 'Enregistrer';
    document.getElementById('leconForm').action = '/projet/{{ $projet->id }}/lecons/' + id;
    new bootstrap.Modal(document.getElementById('modalLecon')).show();
}

document.getElementById('modalLecon')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('leconModalTitle').textContent = 'Nouvelle leçon apprise';
    ['leconTitre','leconDesc','leconPhase','leconImpact','leconRecommandation'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('leconType').value = 'positive';
    document.getElementById('leconCategorie').value = '';
    document.getElementById('leconMethod').value = 'POST';
    document.getElementById('leconSubmitText').textContent = 'Enregistrer';
    document.getElementById('leconForm').action = '{{ route("projet.lecons.store", $projet) }}';
});
</script>
@endpush
@endsection
