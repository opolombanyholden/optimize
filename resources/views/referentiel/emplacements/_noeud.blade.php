@foreach($nodes as $n)
    @php
        $nbStocks = $n->stocks->count();
        $nbStocksNonNuls = $n->stocks->where('quantite', '>', 0)->count();
        $peutSupprimer = $nbStocksNonNuls === 0 && $n->enfants->count() === 0;
        $motifs = [];
        if ($nbStocksNonNuls > 0) $motifs[] = "{$nbStocksNonNuls} article(s) en stock.";
        if ($n->enfants->count() > 0) $motifs[] = "{$n->enfants->count()} sous-emplacement(s).";
        $motifBlocage = 'Suppression impossible : ' . implode(' ', $motifs);
    @endphp
    <li class="list-group-item d-flex align-items-center gap-2 flex-wrap" style="padding-left: {{ $niveau * 28 + 16 }}px;">
        <i class="fas fa-{{ ['magasin' => 'warehouse', 'zone' => 'th-large', 'etagere' => 'grip-lines', 'case' => 'square'][$n->type] ?? 'folder-open' }} {{ $n->actif ? 'text-secondary' : 'text-muted' }}"></i>
        <div class="flex-grow-1 d-flex align-items-center gap-2 flex-wrap">
            <code class="text-muted small">{{ $n->code }}</code>
            <strong class="{{ $n->actif ? '' : 'text-muted text-decoration-line-through' }}">{{ $n->libelle }}</strong>
            @if($n->type)<span class="badge bg-light text-dark">{{ \App\Models\Emplacement::TYPES[$n->type] }}</span>@endif
            @unless($n->actif)<span class="badge bg-secondary">Inactif</span>@endunless
            @if($n->responsable)<small class="text-muted"><i class="fas fa-user me-1"></i>{{ $n->responsable->name }}</small>@endif
            @if($nbStocks > 0)
                <small class="text-muted"><i class="fas fa-boxes-stacked me-1"></i>{{ $nbStocks }} article(s)</small>
            @endif
            @if($n->enfants->isNotEmpty())
                <small class="text-muted"><i class="fas fa-sitemap me-1"></i>{{ $n->enfants->count() }} enfant(s)</small>
            @endif
        </div>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('referentiel.emplacements.show', $n) }}" class="btn btn-outline-primary" title="Voir la fiche">
                <i class="fas fa-eye"></i>
            </a>
            <button type="button" class="btn btn-outline-primary" data-emp-add data-parent="{{ $n->id }}" title="Ajouter un sous-emplacement">
                <i class="fas fa-folder-plus"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary"
                    data-emp-edit
                    data-id="{{ $n->id }}"
                    data-code="{{ $n->code }}"
                    data-libelle="{{ $n->libelle }}"
                    data-type="{{ $n->type }}"
                    data-parent-id="{{ $n->parent_id }}"
                    data-ordre="{{ $n->ordre }}"
                    data-responsable-id="{{ $n->responsable_id }}"
                    data-adresse="{{ $n->adresse }}"
                    data-description="{{ $n->description }}"
                    data-actif="{{ $n->actif ? '1' : '0' }}"
                    title="Modifier">
                <i class="fas fa-pen"></i>
            </button>
            <form action="{{ route('referentiel.emplacements.toggle', $n) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-outline-{{ $n->actif ? 'warning' : 'success' }}" title="{{ $n->actif ? 'Désactiver' : 'Activer' }}">
                    <i class="fas fa-{{ $n->actif ? 'toggle-on' : 'toggle-off' }}"></i>
                </button>
            </form>
            @if($peutSupprimer)
                <form action="{{ route('referentiel.emplacements.destroy', $n) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer « {{ $n->libelle }} » ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
            @else
                <button class="btn btn-outline-danger" disabled title="{{ $motifBlocage }}"><i class="fas fa-lock"></i></button>
            @endif
        </div>
    </li>
    @if($n->enfants->isNotEmpty())
        @include('referentiel.emplacements._noeud', ['nodes' => $n->enfants, 'niveau' => $niveau + 1])
    @endif
@endforeach
