{{--
    Filtre de scope de visibilité — 3 checkboxes multi-sélection.
    À inclure DANS un <form method="GET"> d'une vue liste.

    Params optionnels :
      $scopesFilterLabel  → titre affiché (défaut : "Visibilité")
      $scopesFilterHelp   → texte d'aide (défaut : "…")
--}}
@php
    $sel = (array) request('scopes', []);
    $scopesFilterLabel = $scopesFilterLabel ?? 'Visibilité';
@endphp
<div class="scopes-filter d-flex flex-wrap align-items-center gap-3" style="background:#F8FAFC; border:1px solid #E5E7EB; border-radius:6px; padding:.55rem .8rem;">
    <div class="d-flex align-items-center gap-2 flex-shrink-0" style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748B;">
        <i class="fas fa-filter"></i>{{ $scopesFilterLabel }}
    </div>

    <div class="form-check form-check-inline mb-0">
        <input class="form-check-input" type="checkbox" name="scopes[]" value="mes" id="sfMes" @checked(in_array('mes', $sel)) onchange="this.form.submit()">
        <label class="form-check-label" for="sfMes" style="font-size:.85rem;">
            <i class="fas fa-user me-1" style="color:#0A66C2;"></i>Mes données
        </label>
    </div>
    <div class="form-check form-check-inline mb-0">
        <input class="form-check-input" type="checkbox" name="scopes[]" value="groupes" id="sfGroupes" @checked(in_array('groupes', $sel)) onchange="this.form.submit()">
        <label class="form-check-label" for="sfGroupes" style="font-size:.85rem;">
            <i class="fas fa-user-group me-1" style="color:#7C3AED;"></i>Mes groupes
        </label>
    </div>
    <div class="form-check form-check-inline mb-0">
        <input class="form-check-input" type="checkbox" name="scopes[]" value="publiques" id="sfPubliques" @checked(in_array('publiques', $sel)) onchange="this.form.submit()">
        <label class="form-check-label" for="sfPubliques" style="font-size:.85rem;">
            <i class="fas fa-globe me-1" style="color:#059669;"></i>Publiques
        </label>
    </div>

    @if(!empty($sel))
        <a href="{{ url()->current().'?'.http_build_query(request()->except('scopes')) }}"
           class="btn btn-sm btn-link text-muted p-0" style="font-size:.72rem; margin-left:.35rem;"
           title="Réinitialiser le filtre de visibilité">
            <i class="fas fa-xmark"></i> Tout voir
        </a>
    @endif

    <span class="text-muted flex-grow-1 text-end" style="font-size:.7rem;">
        Aucune case cochée = tout est visible selon vos droits
    </span>
</div>
