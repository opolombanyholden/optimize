@extends('layouts.app')
@section('title', $equipe->nom . ' — Équipe')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item">Annuaire</li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.annuaire.equipes.index') }}">Équipes</a></li>
    <li class="breadcrumb-item active">{{ $equipe->nom }}</li>
</ol>
@endsection

@section('content')
@php $color = $equipe->couleur ?? '#7C3AED'; @endphp
<div class="page-intranet" style="--accent: {{ $color }};">

    <div class="contact-detail-header" style="border-top-color: {{ $color }};">
        <div class="contact-detail-photo" style="background: {{ $color }}; display:flex;align-items:center;justify-content:center;">
            <i class="fas {{ $equipe->icone ?? 'fa-people-group' }}" style="color:#fff;font-size:1.5rem;"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                <span class="opp-stage-badge" style="background:{{ $color }};">{{ $equipe->membres->count() }} membre(s)</span>
            </div>
            <h1 class="contact-detail-name">{{ $equipe->nom }}</h1>
            @if($equipe->auteur)<p class="contact-detail-poste">Créée par {{ $equipe->auteur->prenoms }} {{ $equipe->auteur->name }} le {{ $equipe->created_at->format('d/m/Y') }}</p>@endif
        </div>
        <div class="contact-detail-actions">
            <a href="{{ route('intranet.annuaire.equipes.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Toutes les équipes</a>
            <button class="btn btn-intranet" style="background:{{ $color }};" data-bs-toggle="modal" data-bs-target="#modalAddMember">
                <i class="fas fa-user-plus me-1"></i> Ajouter un membre
            </button>
        </div>
    </div>

    @if($equipe->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <p style="font-size:.85rem;color:#475569;">{{ $equipe->description }}</p>
    </div>
    @endif

    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-users"></i> Membres ({{ $equipe->membres->count() }})</h4>
        @if($equipe->membres->count())
        <div class="row g-2">
            @foreach($equipe->membres as $m)
            @php
                $palette = ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5','#DC2626','#16A34A'];
                $col = $palette[$m->id % 8];
                $initials = strtoupper(substr($m->prenoms ?? $m->name, 0, 1)) . strtoupper(substr($m->name, 0, 1));
            @endphp
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 p-2" style="background:#F8FAFC;border-radius:8px;">
                    <div class="contact-avatar-letters" style="width:36px;height:36px;background:{{ $col }};font-size:.7rem;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center;border-radius:50%;flex-shrink:0;">{{ $initials }}</div>
                    <div style="flex:1;min-width:0;">
                        <a href="{{ route('intranet.annuaire.collaborateurs.show', $m) }}" style="text-decoration:none;color:inherit;">
                            <strong style="font-size:.8rem;">{{ $m->prenoms }} {{ $m->name }}</strong>
                        </a>
                        @if($m->poste)<div style="font-size:.65rem;color:#64748B;">{{ $m->poste }}</div>@endif
                    </div>
                    @if($m->pivot->role === 'animateur')<span class="opp-stage-badge" style="background:#F59E0B;font-size:.55rem;">Animateur</span>@endif
                    <form action="{{ route('intranet.annuaire.equipes.membres.detach', [$equipe, $m]) }}" method="POST" onsubmit="return confirm('Retirer ?');">
                        @csrf @method('DELETE')
                        <button class="ged-action-btn"><i class="fas fa-xmark text-danger" style="font-size:.7rem;"></i></button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-muted small mb-0">Aucun membre.</p>
        @endif
    </div>
</div>

<div class="modal fade" id="modalAddMember" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" action="{{ route('intranet.annuaire.equipes.membres.attach', $equipe) }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Ajouter un membre</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Collaborateur <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach(\App\Models\User::actif()->orderBy('name')->get() as $u)
                            @if(!$equipe->membres->contains($u->id))
                            <option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-select">
                        <option value="membre">Membre</option>
                        <option value="animateur">Animateur</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:{{ $color }};"><i class="fas fa-user-plus me-2"></i>Ajouter</button>
            </div>
        </form>
    </div>
</div>
@endsection
