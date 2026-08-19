{{-- ============================================================
     PARTIAL — Section Publication & Visibilité (réutilisable)
     Variables attendues :
     - $publication (Publication|null)
     - $users (Collection)
     - $groupes (Collection)
     - $ciblesUsers (array)
     - $ciblesGroupes (array)
     - $defaultVisibilite (string, optionnel) — défaut 'prive'
     ============================================================ --}}
@php
    $defaultVisibilite = $defaultVisibilite ?? 'prive';
    $vis = old('visibilite', $publication->visibilite ?? $defaultVisibilite);
@endphp

<div class="form-card">
    <div class="form-card-header"><i class="fas fa-paper-plane"></i> Visibilité</div>
    <div class="form-card-body">
        <div class="mb-3">
            <label class="form-label">Qui peut voir cette fiche ?</label>
            <select name="visibilite" class="form-select" id="visibiliteSelect">
                <option value="public"    @selected($vis === 'public')>🌐 Public — tout le monde</option>
                <option value="prive"     @selected($vis === 'prive')>🔒 Privé — cibles définies</option>
                <option value="brouillon" @selected($vis === 'brouillon')>📝 Brouillon</option>
            </select>
        </div>
    </div>
</div>

<div class="form-card" id="ciblesCard">
    <div class="form-card-header"><i class="fas fa-bullseye"></i> Cibles (mode privé)</div>
    <div class="form-card-body">
        <div class="mb-3">
            <label class="form-label">Utilisateurs ciblés</label>
            <select name="cibles_users[]" id="select-cibles-users" multiple
                    placeholder="Rechercher un collaborateur…">
                @foreach($users as $u)
                    <option value="{{ $u->id }}"
                        data-initials="{{ strtoupper(substr($u->prenoms ?? $u->name, 0, 1) . substr($u->name, 0, 1)) }}"
                        data-subtitle="{{ $u->email_interne ?? $u->email ?? '' }}"
                        @selected(in_array($u->id, old('cibles_users', $ciblesUsers ?? [])))>
                        {{ $u->prenoms }} {{ $u->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-0">
            <label class="form-label">Groupes ciblés</label>
            <select name="cibles_groupes[]" id="select-cibles-groupes" multiple
                    placeholder="Rechercher un groupe…">
                @foreach($groupes as $g)
                    <option value="{{ $g->id }}"
                        data-color="{{ $g->couleur ?? '#7C3AED' }}"
                        @selected(in_array($g->id, old('cibles_groupes', $ciblesGroupes ?? [])))>
                        {{ $g->nom }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-card">
    <div class="form-card-header"><i class="fas fa-heart"></i> Interactions</div>
    <div class="form-card-body">
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="likes_actifs" value="1"
                @checked(old('likes_actifs', $publication->likes_actifs ?? false))>
            <label class="form-check-label">Autoriser les likes</label>
        </div>
        <div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" name="commentaires_actifs" value="1"
                @checked(old('commentaires_actifs', $publication->commentaires_actifs ?? false))>
            <label class="form-check-label">Autoriser les commentaires</label>
        </div>
    </div>
</div>
