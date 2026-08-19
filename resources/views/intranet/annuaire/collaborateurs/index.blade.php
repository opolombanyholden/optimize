@extends('layouts.app')
@section('title', 'Annuaire — Collaborateurs')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item">Annuaire</li>
    <li class="breadcrumb-item active">Collaborateurs</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0891B2;">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0891B2,#0E7490);"><i class="fas fa-users"></i></span>
                Annuaire — Collaborateurs
            </h1>
            <p class="page-subtitle">Annuaire interne · {{ $stats['total'] }} collaborateurs actifs</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('intranet.annuaire.services.index') }}" class="btn btn-light"><i class="fas fa-building me-1"></i> Entités</a>
            <a href="{{ route('intranet.annuaire.equipes.index') }}" class="btn btn-light"><i class="fas fa-people-group me-1"></i> Équipes</a>
        </div>
    </div>

    {{-- Quick filters (chips) --}}
    @auth
    <div class="d-flex flex-wrap gap-2 mb-3" style="font-size:.78rem;">
        <a href="{{ route('intranet.annuaire.collaborateurs.index') }}"
           class="opp-stage-badge" style="background:{{ ! $mode ? '#0891B2' : '#94A3B8' }};text-decoration:none;">
            <i class="fas fa-users me-1"></i> Tous ({{ $stats['total'] }})
        </a>
        @if($stats['mes_entites'] > 0)
        <a href="{{ route('intranet.annuaire.collaborateurs.index', ['mode' => 'mes_entites']) }}"
           class="opp-stage-badge" style="background:{{ $mode === 'mes_entites' ? '#16A34A' : '#94A3B8' }};text-decoration:none;">
            <i class="fas fa-building-user me-1"></i> Mes entités
        </a>
        @endif
        @if($stats['mes_equipes'] > 0)
        <a href="{{ route('intranet.annuaire.collaborateurs.index', ['mode' => 'mes_equipes']) }}"
           class="opp-stage-badge" style="background:{{ $mode === 'mes_equipes' ? '#7C3AED' : '#94A3B8' }};text-decoration:none;">
            <i class="fas fa-people-group me-1"></i> Mes équipes
        </a>
        @endif
    </div>
    @endauth

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #0891B2;">
                <div class="stat-label">Total collaborateurs</div>
                <div class="stat-value" style="color:#0891B2;">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #16A34A;">
                <div class="stat-label">Affectés à une entité</div>
                <div class="stat-value" style="color:#16A34A;">{{ $stats['avec_service'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #F59E0B;">
                <div class="stat-label">Sans entité</div>
                <div class="stat-value" style="color:#F59E0B;">{{ $stats['sans_service'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="courrier-stats" style="border-left:4px solid #6366F1;">
                <div class="stat-label">Entités actives</div>
                <div class="stat-value" style="color:#6366F1;">{{ $stats['services_actifs'] }}</div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="form-card mb-3">
        @if($mode)<input type="hidden" name="mode" value="{{ $mode }}">@endif
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" style="font-size:.72rem;">Rechercher</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nom, email, matricule...">
            </div>
            <div class="col-md-3">
                <label class="form-label" style="font-size:.72rem;">Entité</label>
                <select name="service" class="form-select">
                    <option value="">Toutes les entités</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ request('service') == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size:.72rem;">Type</label>
                <select name="type_entite" class="form-select">
                    <option value="">Tous types</option>
                    @foreach(\App\Models\Intranet\Service::TYPES_ENTITE as $k => $lbl)
                        <option value="{{ $k }}" {{ $typeEntite === $k ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size:.72rem;">Équipe</label>
                <select name="equipe" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($equipes as $eq)
                        <option value="{{ $eq->id }}" {{ request('equipe') == $eq->id ? 'selected' : '' }}>{{ $eq->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label" style="font-size:.72rem;">Vue</label>
                <div class="btn-group w-100">
                    <a href="{{ request()->fullUrlWithQuery(['vue' => 'cards']) }}" class="btn btn-sm {{ $vue === 'cards' ? 'btn-dark' : 'btn-light' }}"><i class="fas fa-grip"></i></a>
                    <a href="{{ request()->fullUrlWithQuery(['vue' => 'table']) }}" class="btn btn-sm {{ $vue === 'table' ? 'btn-dark' : 'btn-light' }}"><i class="fas fa-table"></i></a>
                </div>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-intranet w-100" style="background:#0891B2;"><i class="fas fa-search"></i></button>
            </div>
        </div>
    </form>

    @if($users->count())
        @if($vue === 'cards')
        {{-- Vue cards --}}
        <div class="row g-3">
            @foreach($users as $u)
            @php
                $palette = ['#7C3AED','#059669','#D97706','#0891B2','#0D9488','#4F46E5','#DC2626','#16A34A'];
                $color = $palette[$u->id % 8];
                $initials = strtoupper(substr($u->prenoms ?? $u->name, 0, 1)) . strtoupper(substr($u->name, 0, 1));
                $servicePrincipal = $u->services->where('pivot.est_principal', true)->first() ?? $u->services->first();
            @endphp
            <div class="col-md-4 col-lg-3">
                <a href="{{ route('intranet.annuaire.collaborateurs.show', $u) }}" class="form-card h-100 d-block" style="text-decoration:none;color:inherit;">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        @if($u->profile_photo_path)
                        <img src="{{ asset('storage/' . $u->profile_photo_path) }}" alt="" style="width:50px;height:50px;border-radius:50%;object-fit:cover;">
                        @else
                        <div class="contact-avatar-letters" style="width:50px;height:50px;background:{{ $color }};font-size:1rem;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center;border-radius:50%;">{{ $initials }}</div>
                        @endif
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.88rem;font-weight:700;color:#0F172A;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $u->prenoms }} {{ $u->name }}</div>
                            @if($u->poste)<div style="font-size:.7rem;color:#64748B;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $u->poste }}</div>@endif
                        </div>
                    </div>
                    @if($servicePrincipal)
                    <div style="font-size:.7rem;color:#475569;margin-bottom:.3rem;">
                        <i class="fas fa-building me-1" style="color:{{ $servicePrincipal->couleur ?? '#0D9488' }};"></i>
                        {{ $servicePrincipal->nom }}
                    </div>
                    @endif
                    <div class="d-flex flex-wrap gap-1" style="font-size:.65rem;color:#64748B;">
                        @if($u->email)<span><i class="fas fa-envelope me-1"></i>{{ Str::limit($u->email, 20) }}</span>@endif
                        @if($u->contact)<span><i class="fas fa-phone me-1"></i>{{ $u->contact }}</span>@endif
                    </div>
                </a>
            </div>
            @endforeach
        </div>
        @else
        {{-- Vue table --}}
        <div class="form-card">
            <table class="opp-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Poste</th>
                        <th>Service principal</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Matricule</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    @php $sp = $u->services->where('pivot.est_principal', true)->first() ?? $u->services->first(); @endphp
                    <tr>
                        <td><a href="{{ route('intranet.annuaire.collaborateurs.show', $u) }}"><strong>{{ $u->prenoms }} {{ $u->name }}</strong></a></td>
                        <td>{{ $u->poste ?? '—' }}</td>
                        <td>
                            @if($sp)<span class="opp-stage-badge" style="background:{{ $sp->couleur ?? '#0D9488' }};font-size:.6rem;">{{ $sp->nom }}</span>
                            @else — @endif
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->contact ?? '—' }}</td>
                        <td><code>{{ $u->matricule ?? '—' }}</code></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="mt-3">{{ $users->links() }}</div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon" style="background:#ECFEFF;"><i class="fas fa-user-slash" style="color:#0891B2;"></i></div>
            <h3>Aucun collaborateur trouvé</h3>
            @if(request('q') || request('service'))
            <a href="{{ route('intranet.annuaire.collaborateurs.index') }}" class="btn btn-light"><i class="fas fa-rotate-left me-2"></i>Réinitialiser les filtres</a>
            @endif
        </div>
    @endif
</div>
@endsection
