@foreach($nodes as $n)
    @php
        $descIds = $n->descendantsIds();
        $nbDysfonc = \App\Models\Dysfonctionnement::whereIn('thematique_id', $descIds)->count();
        $nbInterv  = \App\Models\Intervention::whereIn('thematique_id', $descIds)->count();
        $nbEnfants = $n->enfants->count();
        $peutSupprimer = $nbEnfants === 0 && ($nbDysfonc + $nbInterv) === 0;
        $motifs = [];
        if ($nbEnfants > 0) $motifs[] = "{$nbEnfants} sous-thématique(s) à traiter d'abord.";
        if ($nbDysfonc > 0) $motifs[] = "{$nbDysfonc} dysfonctionnement(s) rattaché(s).";
        if ($nbInterv > 0)  $motifs[] = "{$nbInterv} intervention(s) rattachée(s).";
        $motifBlocage = 'Suppression impossible : ' . implode(' ', $motifs);
    @endphp
    <li class="list-group-item d-flex align-items-center gap-2 flex-wrap" style="padding-left: {{ $niveau * 28 + 16 }}px;">
        <span class="d-inline-block rounded-circle" style="width:12px;height:12px;background:{{ $n->couleur ?: '#6c757d' }};"></span>
        <div class="flex-grow-1 d-flex align-items-center gap-2 flex-wrap">
            <strong class="{{ $n->actif ? '' : 'text-muted text-decoration-line-through' }}">{{ $n->libelle }}</strong>
            @unless($n->actif)<span class="badge bg-secondary">Inactive</span>@endunless

            @if($nbDysfonc > 0)
                <span class="badge bg-warning text-dark" title="Dysfonctionnements"><i class="fas fa-triangle-exclamation me-1"></i>{{ $nbDysfonc }}</span>
            @endif
            @if($nbInterv > 0)
                <span class="badge bg-info text-white" title="Interventions"><i class="fas fa-tools me-1"></i>{{ $nbInterv }}</span>
            @endif
            @if($nbEnfants)
                <span class="badge bg-primary text-white" title="Sous-thématiques"><i class="fas fa-sitemap me-1"></i>{{ $nbEnfants }}</span>
            @endif
            @if($n->description)
                <small class="text-muted text-truncate d-none d-md-inline" style="max-width:320px" title="{{ $n->description }}">— {{ $n->description }}</small>
            @endif
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" data-th-add data-parent="{{ $n->id }}" title="Ajouter une sous-thématique">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary"
                    data-th-edit
                    data-id="{{ $n->id }}"
                    data-libelle="{{ $n->libelle }}"
                    data-couleur="{{ $n->couleur }}"
                    data-parent-id="{{ $n->parent_id }}"
                    data-ordre="{{ $n->ordre }}"
                    data-description="{{ $n->description }}"
                    data-actif="{{ $n->actif ? '1' : '0' }}"
                    title="Modifier">
                <i class="fas fa-pen"></i>
            </button>
            <form action="{{ route('referentiel.mg-thematiques.toggle', $n) }}" method="POST" class="d-inline">@csrf
                <button class="btn btn-outline-{{ $n->actif ? 'warning' : 'success' }}" title="{{ $n->actif ? 'Désactiver' : 'Activer' }}">
                    <i class="fas fa-{{ $n->actif ? 'toggle-on' : 'toggle-off' }}"></i>
                </button>
            </form>
            @if($peutSupprimer)
                <form action="{{ route('referentiel.mg-thematiques.destroy', $n) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement la thématique « {{ $n->libelle }} » ?');">
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
        @include('referentiel.mg-thematiques._noeud', ['nodes' => $n->enfants, 'niveau' => $niveau + 1])
    @endif
@endforeach
