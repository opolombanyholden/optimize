@extends('layouts.app')
@section('title', $service->nom . ' — Service')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item">Annuaire</li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.annuaire.services.index') }}">Services</a></li>
    @if($service->parent)<li class="breadcrumb-item"><a href="{{ route('intranet.annuaire.services.show', $service->parent) }}">{{ $service->parent->nom }}</a></li>@endif
    <li class="breadcrumb-item active">{{ $service->nom }}</li>
</ol>
@endsection

@section('content')
@php $color = $service->couleur ?? '#0D9488'; @endphp
<div class="page-intranet" style="--accent: {{ $color }};">

    <div class="contact-detail-header" style="border-top-color: {{ $color }};">
        <div class="contact-detail-photo" style="background: {{ $color }}; display:flex;align-items:center;justify-content:center;">
            <i class="fas {{ $service->icone ?? 'fa-building' }}" style="color:#fff;font-size:1.5rem;"></i>
        </div>
        <div class="contact-detail-identity">
            <div class="contact-detail-badges">
                @if($service->code)<code class="opp-ref">{{ $service->code }}</code>@endif
                <span class="opp-stage-badge" style="background:{{ $color }};">{{ $service->membres->count() }} membre(s)</span>
                @if(!$service->est_actif)<span class="opp-stage-badge" style="background:#94A3B8;">Inactif</span>@endif
            </div>
            <h1 class="contact-detail-name">{{ $service->nom }}</h1>
            @if($service->parent)
            <a href="{{ route('intranet.annuaire.services.show', $service->parent) }}" class="contact-detail-orga"><i class="fas fa-arrow-up-from-bracket"></i> {{ $service->parent->nom }}</a>
            @endif
        </div>
        <div class="contact-detail-actions">
            <a href="{{ route('intranet.annuaire.services.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Tous les services</a>
            <button class="btn btn-intranet" style="background:{{ $color }};" data-bs-toggle="modal" data-bs-target="#modalAddMember">
                <i class="fas fa-user-plus me-1"></i> Ajouter un membre
            </button>
        </div>
    </div>

    @if($service->description)
    <div class="contact-detail-card mt-3">
        <h4 class="contact-detail-card-title"><i class="fas fa-align-left"></i> Description</h4>
        <p style="font-size:.85rem;color:#475569;">{{ $service->description }}</p>
    </div>
    @endif

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            {{-- Membres --}}
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-users"></i> Membres ({{ $service->membres->count() }})</h4>
                @if($service->membres->count())
                <div class="row g-2">
                    @foreach($service->membres as $m)
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
                                @if($m->pivot->poste)<div style="font-size:.65rem;color:#64748B;">{{ $m->pivot->poste }}</div>@endif
                            </div>
                            @if($m->pivot->est_principal)<span class="opp-stage-badge" style="background:#16A34A;font-size:.55rem;">Principal</span>@endif
                            <form action="{{ route('intranet.annuaire.services.membres.detach', [$service, $m]) }}" method="POST" onsubmit="return confirm('Retirer ce membre ?');">
                                @csrf @method('DELETE')
                                <button class="ged-action-btn" title="Retirer"><i class="fas fa-xmark text-danger" style="font-size:.7rem;"></i></button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted small mb-0">Aucun membre.</p>
                @endif
            </div>

            {{-- Sous-services --}}
            @if($service->sousServices->count())
            <div class="contact-detail-card mt-3">
                <h4 class="contact-detail-card-title"><i class="fas fa-sitemap"></i> Sous-services ({{ $service->sousServices->count() }})</h4>
                <div class="d-flex flex-column gap-2">
                    @foreach($service->sousServices as $sub)
                    <a href="{{ route('intranet.annuaire.services.show', $sub) }}" class="d-flex align-items-center gap-2 p-2" style="background:#F8FAFC;border-radius:8px;text-decoration:none;color:inherit;">
                        <i class="fas {{ $sub->icone ?? 'fa-building' }}" style="color:{{ $sub->couleur ?? '#0D9488' }};"></i>
                        <strong style="flex:1;font-size:.85rem;">{{ $sub->nom }}</strong>
                        @if($sub->chef)<span style="font-size:.7rem;color:#64748B;">Chef : {{ $sub->chef->prenoms }} {{ $sub->chef->name }}</span>@endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="contact-detail-card">
                <h4 class="contact-detail-card-title"><i class="fas fa-circle-info"></i> Informations</h4>
                <ul class="contact-info-list">
                    @if($service->chef)
                    <li><strong>Chef :</strong> <a href="{{ route('intranet.annuaire.collaborateurs.show', $service->chef) }}">{{ $service->chef->prenoms }} {{ $service->chef->name }}</a></li>
                    @endif
                    @if($service->email)<li><i class="fas fa-envelope"></i> <a href="mailto:{{ $service->email }}">{{ $service->email }}</a></li>@endif
                    @if($service->telephone)<li><i class="fas fa-phone"></i> {{ $service->telephone }}</li>@endif
                    @if($service->localisation)<li><i class="fas fa-location-dot"></i> {{ $service->localisation }}</li>@endif
                    <li><strong>Effectif :</strong> {{ $service->membres->count() }}</li>
                    <li><strong>Créé le :</strong> {{ $service->created_at->format('d/m/Y') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Modale ajout membre --}}
<div class="modal fade" id="modalAddMember" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content" action="{{ route('intranet.annuaire.services.membres.attach', $service) }}">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Ajouter un membre au service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Collaborateur <span class="text-danger">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach(\App\Models\User::actif()->orderBy('name')->get() as $u)
                            @if(!$service->membres->contains($u->id))
                            <option value="{{ $u->id }}">{{ $u->prenoms }} {{ $u->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Poste dans ce service</label>
                    <input type="text" name="poste" class="form-control" placeholder="Développeur, Comptable...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Date d'arrivée</label>
                    <input type="date" name="date_arrivee" class="form-control" value="{{ now()->toDateString() }}">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="est_principal" id="estPrincipal" value="1">
                    <label class="form-check-label" for="estPrincipal">Service principal du collaborateur</label>
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
