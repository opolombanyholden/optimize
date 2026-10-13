{{-- Colonne gauche du webmail : compte, nouveau message, dossiers --}}
<aside class="wm-side">
    <a href="{{ route('intranet.mails.create', ['compte' => $compte->id]) }}" class="btn btn-primary w-100 mb-3"><i class="fas fa-pen me-1"></i> Nouveau message</a>

    @if($comptes->count() > 1)
    <form method="GET" action="{{ route('intranet.mails.index') }}">
        <select name="compte" class="form-select" onchange="this.form.submit()" aria-label="Compte mail">
            @foreach($comptes as $c)
                <option value="{{ $c->id }}" @selected($c->id === $compte->id)>{{ $c->email }}</option>
            @endforeach
        </select>
    </form>
    @else
        <div class="text-muted text-truncate" style="font-size:.78rem;" title="{{ $compte->email }}"><i class="fas fa-at me-1"></i>{{ $compte->email }}</div>
    @endif

    <ul class="wm-folders">
        <li><a href="{{ route('intranet.mails.a-traiter', ['compte' => $compte->id]) }}" class="{{ request()->routeIs('intranet.mails.a-traiter') ? 'active' : '' }}">
            <i class="fas fa-list-check"></i><span>À traiter</span>
            @if(!empty($nbATraiter))<span class="wm-compteur">{{ $nbATraiter }}</span>@endif
        </a></li>
        @forelse($dossiers as $d)
            <li><a href="{{ route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $d['nom']]) }}" class="{{ $d['nom'] === ($dossier ?? null) ? 'active' : '' }}" title="{{ $d['nom'] }}">
                <i class="fas {{ $d['icone'] }}"></i><span class="text-truncate">{{ $d['libelle'] }}</span>
            </a></li>
        @empty
            <li><a href="{{ route('intranet.mails.index', ['compte' => $compte->id]) }}" class="active"><i class="fas fa-inbox"></i>Boîte de réception</a></li>
        @endforelse
    </ul>

    <hr class="my-3">
    <a href="{{ route('mon-compte.index') }}#comptes-mail" class="d-block text-muted text-decoration-none" style="font-size:.78rem;"><i class="fas fa-gear me-1"></i>Gérer mes comptes mail</a>
</aside>
