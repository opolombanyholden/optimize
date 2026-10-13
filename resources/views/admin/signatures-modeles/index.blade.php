@extends('layouts.app')
@section('title', 'Modèles de signature')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item active">Modèles de signature</li></ol>
@endsection

@push('styles')
<style>
.msm-carte { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); padding:1.1rem 1.25rem; }
.msm-apercu { border:1px solid #E2E8F0; border-radius:8px; padding:.85rem 1rem; background:#FCFCFD; overflow:hidden; font-size:13px; }
.msm-apercu p { margin:0; }
.msm-apercu img { max-width:100%; height:auto; }
.msm-stat { font-size:.78rem; color:#64748B; }
</style>
@endpush

@section('content')
<div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-signature me-2" style="color:#475569;"></i>Modèles de signature</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Signatures d'entreprise avec variables (prénom, poste, téléphone…), appliquées à tous les utilisateurs. Toute modification d'un modèle met à jour la signature de chacun.</p>
    </div>
    <a href="{{ route('admin.signatures-modeles.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Nouveau modèle</a>
</div>

@forelse($modeles as $m)
    <div class="msm-carte mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <strong style="font-size:1rem; color:#1E293B;">{{ $m->nom }}</strong>
            @if($m->actif)<span class="badge-status badge-actif">Actif</span>@else<span class="badge-status badge-brouillon">Inactif</span>@endif
            @if($m->verrouille)<span class="badge-status badge-en-attente"><i class="fas fa-lock me-1"></i>Imposé</span>@endif
            <span class="msm-stat ms-2"><i class="fas fa-users me-1"></i>{{ $m->signatures_count }} / {{ $utilisateursActifs }} utilisateur(s) · par défaut chez {{ $m->par_defaut_count }}</span>
            <div class="ms-auto d-flex flex-wrap gap-1">
                <a href="{{ route('admin.signatures-modeles.edit', $m) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen me-1"></i>Modifier</a>
                @if($m->actif)
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#appliquer-{{ $m->id }}"><i class="fas fa-users-gear me-1"></i>Appliquer à tous</button>
                @endif
                @if($m->signatures_count)
                <form action="{{ route('admin.signatures-modeles.retirer', $m) }}" method="POST" onsubmit="return confirm('Retirer ce modèle des signatures de tous les utilisateurs ?');">@csrf
                    <button class="btn btn-sm btn-outline-warning" title="Retirer de tous"><i class="fas fa-user-minus"></i></button>
                </form>
                @endif
                <form action="{{ route('admin.signatures-modeles.destroy', $m) }}" method="POST" onsubmit="return confirm('Supprimer ce modèle ? Il sera retiré des signatures de tous les utilisateurs.');">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
        {{-- Contenu compilé + nettoyé (App\Services\Mail\Signature), rendu avec les données de l'administrateur --}}
        <div class="msm-apercu">-- <br>{!! \App\Services\Mail\Signature::rendre($m->contenu, auth()->user()) !!}</div>
    </div>

    @if($m->actif)
    <div class="modal fade" id="appliquer-{{ $m->id }}" tabindex="-1">
        <div class="modal-dialog"><form action="{{ route('admin.signatures-modeles.appliquer', $m) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-users-gear me-2"></i>Appliquer « {{ $m->nom }} »</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <p style="font-size:.88rem;">Le modèle sera ajouté aux signatures des <strong>{{ $utilisateursActifs }}</strong> utilisateurs actifs, rempli avec les informations de chacun (nom, poste, téléphone…).</p>
                <div class="form-check form-switch">
                    <input type="hidden" name="par_defaut" value="0">
                    <input class="form-check-input" type="checkbox" name="par_defaut" value="1" id="defaut-{{ $m->id }}" checked>
                    <label class="form-check-label" for="defaut-{{ $m->id }}">En faire la signature par défaut de tous</label>
                </div>
                <div class="form-text">Sinon, il ne devient la signature par défaut que pour les utilisateurs qui n'en ont pas.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Appliquer</button></div>
        </form></div>
    </div>
    @endif
@empty
    <div class="card data-card"><div class="empty-state">
        <i class="fas fa-signature"></i>
        <p class="mb-3">Aucun modèle. Créez la signature officielle de l'entreprise puis appliquez-la à tous les collaborateurs.</p>
        <a href="{{ route('admin.signatures-modeles.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Créer un modèle</a>
    </div></div>
@endforelse
@endsection
