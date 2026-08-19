@foreach($nodes as $n)
    @php
        // Recomputation locale des compteurs si non préchargés (nœuds hiérarchiques enfants)
        $nbTotal = $n->articles_count ?? $n->articles()->count();
        $nbActifs = $n->articles_actifs_count ?? $n->articles()->where('statut', 1)->count();
        // Récursif : produits actifs dans tout le sous-arbre → détermine si suppression possible
        $descIds = $n->descendantsIds();
        $nbActifsRecursifs = \App\Models\Produit::whereIn('famille_id', $descIds)->where('statut', 1)->count();
        $nbEnfants = $n->enfants->count();
        $peutSupprimer = $nbActifsRecursifs === 0 && $nbEnfants === 0;
        $motifsBlocage = [];
        if ($nbActifsRecursifs > 0) $motifsBlocage[] = "{$nbActifsRecursifs} produit(s) actif(s) dans le sous-arbre.";
        if ($nbEnfants > 0) $motifsBlocage[] = "{$nbEnfants} sous-famille(s) à traiter d'abord.";
        $motifBlocage = 'Suppression impossible : ' . implode(' ', $motifsBlocage);
    @endphp
    <li class="list-group-item d-flex align-items-center gap-2 flex-wrap" style="padding-left: {{ $niveau * 28 + 16 }}px;">
        <i class="fas fa-{{ $nbEnfants ? 'folder-open' : 'folder' }} {{ $n->actif ? 'text-warning' : 'text-muted' }}"></i>
        <div class="flex-grow-1 d-flex align-items-center gap-2 flex-wrap">
            <strong class="{{ $n->actif ? '' : 'text-muted text-decoration-line-through' }}">{{ $n->libelle }}</strong>
            @if($n->code)<code class="small text-muted">{{ $n->code }}</code>@endif
            @unless($n->actif)<span class="badge bg-secondary">Inactive</span>@endunless

            <span class="badge bg-success" title="Produits actifs">{{ $nbActifs }}A</span>
            @if($nbTotal !== $nbActifs)
                <span class="badge bg-secondary" title="Total produits (actifs + inactifs)">{{ $nbTotal }}T</span>
            @endif
            @if($nbEnfants)
                <span class="badge bg-info text-white" title="Sous-familles"><i class="fas fa-sitemap me-1"></i>{{ $nbEnfants }}</span>
            @endif
            @if($n->description)
                <small class="text-muted"><i class="fas fa-info-circle"></i></small>
                <small class="text-muted text-truncate d-none d-md-inline" style="max-width:320px" title="{{ $n->description }}">{{ $n->description }}</small>
            @endif
        </div>
        <div class="btn-group btn-group-sm">
            {{-- Ajouter sous-famille --}}
            <button type="button" class="btn btn-outline-primary" data-fam-add data-parent="{{ $n->id }}" title="Ajouter une sous-famille">
                <i class="fas fa-folder-plus"></i>
            </button>
            {{-- Modifier --}}
            <button type="button" class="btn btn-outline-secondary"
                    data-fam-edit
                    data-id="{{ $n->id }}"
                    data-libelle="{{ $n->libelle }}"
                    data-code="{{ $n->code }}"
                    data-parent-id="{{ $n->parent_id }}"
                    data-ordre="{{ $n->ordre }}"
                    data-description="{{ $n->description }}"
                    data-actif="{{ $n->actif ? '1' : '0' }}"
                    title="Modifier">
                <i class="fas fa-pen"></i>
            </button>
            {{-- Activer / Désactiver --}}
            <form action="{{ route('referentiel.familles.toggle', $n) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-outline-{{ $n->actif ? 'warning' : 'success' }}" title="{{ $n->actif ? 'Désactiver' : 'Activer' }}">
                    <i class="fas fa-{{ $n->actif ? 'toggle-on' : 'toggle-off' }}"></i>
                </button>
            </form>
            {{-- Supprimer (bloqué si produits actifs dans le sous-arbre) --}}
            @if($peutSupprimer)
                <form action="{{ route('referentiel.familles.destroy', $n) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement la famille « {{ $n->libelle }} » ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            @else
                <button class="btn btn-outline-danger" disabled title="{{ $motifBlocage }}">
                    <i class="fas fa-lock"></i>
                </button>
            @endif
        </div>
    </li>
    @if($n->enfants->isNotEmpty())
        @include('referentiel.familles._noeud', ['nodes' => $n->enfants, 'niveau' => $niveau + 1])
    @endif
@endforeach
