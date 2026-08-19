@extends('layouts.app')
@section('title', $projet->nom . ' — Feuilles de temps')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">Feuilles de temps</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'feuilles-temps'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-clock-rotate-left"></i></span>
                Feuilles de temps — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Saisie et approbation du temps passé</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalTemps">
            <i class="fas fa-plus me-2"></i> Saisir du temps
        </button>
    </div>

    <div class="courrier-stats mb-4">
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#0D9488;"><i class="fas fa-clock"></i></div>
            <div><div class="csc-value">{{ number_format($stats['total_heures'], 1) }}h</div><div class="csc-label">Total heures</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#16A34A;"><i class="fas fa-check"></i></div>
            <div><div class="csc-value">{{ number_format($stats['heures_approuvees'], 1) }}h</div><div class="csc-label">Approuvées</div></div>
        </div>
        <div class="courrier-stat-card">
            <div class="csc-icon" style="background:#F59E0B;"><i class="fas fa-hourglass-half"></i></div>
            <div><div class="csc-value">{{ $stats['en_attente'] }}</div><div class="csc-label">En attente</div></div>
        </div>
    </div>

    @if($feuilles->count())
    <div class="form-card">
        <table class="opp-table">
            <thead><tr><th>Date</th><th>Collaborateur</th><th>Tâche</th><th>Heures</th><th>Description</th><th>Statut</th><th></th></tr></thead>
            <tbody>
                @foreach($feuilles as $f)
                <tr>
                    <td>{{ $f->date->format('d/m/Y') }}</td>
                    <td>{{ $f->utilisateur?->prenoms }} {{ $f->utilisateur?->name }}</td>
                    <td>{{ $f->tache?->titre ?? '—' }}</td>
                    <td><strong>{{ $f->heures }}h</strong></td>
                    <td>{{ Str::limit($f->description, 40) }}</td>
                    <td>
                        <span class="opp-stage-badge" style="background:{{ match($f->statut) { 'approuve' => '#16A34A', 'rejete' => '#DC2626', default => '#F59E0B' } }};">
                            {{ ucfirst($f->statut) }}
                        </span>
                    </td>
                    <td>
                        @if($f->statut === 'soumis')
                        <div class="d-flex gap-1">
                            <form action="{{ route('projet.feuilles-temps.approuver', [$projet, $f]) }}" method="POST">@csrf
                                <button class="btn-action process btn-sm" style="padding:.2rem .5rem;font-size:.7rem;"><i class="fas fa-check"></i></button>
                            </form>
                            <form action="{{ route('projet.feuilles-temps.rejeter', [$projet, $f]) }}" method="POST">@csrf
                                <button class="btn-action btn-sm" style="padding:.2rem .5rem;font-size:.7rem;border-color:#DC2626;color:#DC2626;"><i class="fas fa-xmark"></i></button>
                            </form>
                        </div>
                        @elseif($f->statut === 'approuve' && $f->approbateur)
                            <small class="text-muted">{{ $f->approbateur->prenoms }}</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 d-flex justify-content-center">{{ $feuilles->links() }}</div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-clock-rotate-left" style="color:#0D9488;"></i></div>
        <h3>Aucune saisie de temps</h3>
    </div>
    @endif
</div>

<div class="modal fade" id="modalTemps" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('projet.feuilles-temps.store', $projet) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Saisir du temps</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" required value="{{ now()->format('Y-m-d') }}"></div>
                    <div class="col-md-6"><label class="form-label">Heures <span class="text-danger">*</span></label>
                        <input type="number" step="0.25" name="heures" class="form-control" required min="0.25" max="24"></div>
                </div>
                <div class="mt-3"><label class="form-label">Tâche</label>
                    <select name="tache_id" class="form-select">
                        <option value="">— Aucune —</option>
                        @foreach($projet->taches as $t)
                            <option value="{{ $t->id }}">{{ $t->titre }}</option>
                        @endforeach
                    </select></div>
                <div class="mt-3"><label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-control" placeholder="Travail effectué…"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-clock me-2"></i> Enregistrer</button></div>
        </form>
    </div>
</div>
@endsection
