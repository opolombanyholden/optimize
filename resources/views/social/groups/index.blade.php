@extends('layouts.app')

@section('title', 'Discussions de groupe')

@php
    $totalNonLus = (int) $nonLus->sum();
    $quand = function ($d) {
        if (!$d) return '';
        if ($d->isToday()) return $d->format('H:i');
        if ($d->isYesterday()) return 'Hier';
        if ($d->gt(now()->subDays(6))) return ucfirst($d->translatedFormat('D'));
        return $d->format('d/m');
    };
@endphp

@include('social.groups._styles')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"
      integrity="sha384-piG3EtH1fBnPi68q4spy+Qgpb0dHK1D1dwk0GaHwFkvmUxYi526bBlk3xJcjEBsD" crossorigin="anonymous">
<style>
.sg-page { max-width: 880px; margin: 0 auto; padding-bottom: 2rem; }
/* Smartphone : convention de l'application = sections pleine largeur, coins droits ; les textes gardent une marge */
@media (max-width: 575.98px) {
    .sg-tete, .sg-recherche, .sg-onglets, .sg-decouvrir { margin-left: .9rem; margin-right: .9rem; }
    .sg-tete { padding-top: .9rem; }
    .sg-liste { border-radius: 0; }
    .sg-decouvrir { margin-right: 0; padding-right: .9rem; }
}

/* En-tête */
.sg-tete { display: flex; align-items: flex-start; gap: .75rem; margin-bottom: 1rem; }
.sg-tete h1 { font-size: 1.45rem; margin: 0; line-height: 1.15; }
.sg-tete-sous { font-size: .82rem; color: var(--sg-gris); margin-top: .25rem; }
.sg-tete-sous strong { color: var(--sg-menthe); }
.sg-tete .sg-btn { margin-left: auto; flex-shrink: 0; }
.sg-btn-libelle { display: none; }

/* Recherche */
.sg-recherche { position: relative; margin-bottom: .9rem; }
.sg-recherche i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #A5A3C2; }
.sg-recherche input { width: 100%; border: 1.5px solid var(--sg-trait); background: #fff; border-radius: 999px; padding: .7rem 1rem .7rem 2.6rem; font-size: .9rem; color: var(--sg-encre); transition: border-color .15s, box-shadow .15s; }
.sg-recherche input:focus { outline: 0; border-color: var(--sg-violet); box-shadow: 0 0 0 4px var(--sg-brume); }

/* Onglets */
.sg-onglets { display: inline-flex; background: #fff; border: 1px solid var(--sg-trait); border-radius: 999px; padding: 4px; margin-bottom: 1rem; }
.sg-onglet { border: 0; background: transparent; border-radius: 999px; padding: .45rem 1rem; font-size: .82rem; font-weight: 600; color: var(--sg-gris); display: inline-flex; align-items: center; gap: .4rem; }
.sg-onglet.actif { background: var(--sg-encre); color: #fff; }
.sg-onglet .n { font-size: .7rem; opacity: .7; }

/* Liste des groupes (style messagerie) */
.sg-liste { background: #fff; border-radius: 20px; box-shadow: 0 1px 2px rgba(30,27,75,.06), 0 12px 32px -24px rgba(30,27,75,.35); overflow: hidden; }
.sg-ligne { display: flex; align-items: center; gap: .95rem; padding: .95rem 1rem; text-decoration: none; color: inherit; border-bottom: 1px solid #F3F1FA; position: relative; transition: background .15s; }
.sg-ligne:last-child { border-bottom: 0; }
.sg-ligne:hover { background: #FBFAFF; color: inherit; }
.sg-ligne-corps { flex: 1; min-width: 0; }
.sg-ligne-haut { display: flex; align-items: baseline; gap: .5rem; }
.sg-ligne-nom { font-family: 'Sora', sans-serif; font-weight: 600; font-size: .95rem; color: var(--sg-encre); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sg-ligne-heure { margin-left: auto; font-size: .72rem; color: #A3A1BC; flex-shrink: 0; font-variant-numeric: tabular-nums; }
.sg-ligne-bas { display: flex; align-items: center; gap: .5rem; margin-top: .2rem; }
.sg-ligne-apercu { flex: 1; min-width: 0; font-size: .82rem; color: var(--sg-gris); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sg-ligne-apercu b { color: #475569; font-weight: 600; }
.sg-nonlus { min-width: 22px; height: 22px; border-radius: 999px; background: var(--sg-menthe); color: #fff; font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; padding: 0 .45rem; flex-shrink: 0; }
.sg-ligne.a-lire .sg-ligne-apercu { color: var(--sg-encre); font-weight: 600; }
.sg-ligne.a-lire .sg-ligne-heure { color: var(--sg-menthe); font-weight: 700; }
.sg-public { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #047857; background: #ECFDF5; border-radius: 999px; padding: .1rem .45rem; flex-shrink: 0; }

/* À découvrir : défilement horizontal sur mobile, grille sur grand écran */
.sg-decouvrir { display: grid; grid-auto-flow: column; grid-auto-columns: 78%; gap: .8rem; overflow-x: auto; scroll-snap-type: x mandatory; padding: .2rem .2rem 1rem; margin: 0 -.2rem; scrollbar-width: none; }
.sg-decouvrir::-webkit-scrollbar { display: none; }
.sg-carte { scroll-snap-align: start; background: #fff; border-radius: 20px; padding: 1.1rem; display: flex; flex-direction: column; gap: .7rem; text-decoration: none; color: inherit; border: 1px solid var(--sg-trait); position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.sg-carte::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 64px; background: linear-gradient(180deg, color-mix(in srgb, var(--teinte) 16%, #fff), transparent); }
.sg-carte > * { position: relative; }
.sg-carte:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -20px rgba(30,27,75,.45); color: inherit; }
.sg-carte-nom { font-family: 'Sora', sans-serif; font-weight: 600; font-size: 1rem; color: var(--sg-encre); margin: 0; }
.sg-carte-desc { font-size: .8rem; color: var(--sg-gris); margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.4em; }
.sg-carte-pied { display: flex; align-items: center; justify-content: space-between; margin-top: auto; font-size: .76rem; color: var(--sg-gris); }
.sg-carte .sg-btn { padding: .4rem .85rem; font-size: .78rem; }

/* Vides */
.sg-vide { text-align: center; padding: 2.5rem 1.25rem; }
.sg-vide-icone { width: 72px; height: 72px; border-radius: 30%; margin: 0 auto 1rem; background: var(--sg-brume); color: var(--sg-violet); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
.sg-vide p { color: var(--sg-gris); font-size: .88rem; max-width: 360px; margin: .35rem auto 1.1rem; }
.sg-aucun { display: none; padding: 1.5rem; text-align: center; color: var(--sg-gris); font-size: .85rem; }

/* Modale de création */
.sg-modale .modal-content { border: 0; border-radius: 24px; }
.sg-modale .modal-header { border: 0; padding: 1.25rem 1.25rem .25rem; }
.sg-modale .modal-footer { border: 0; padding: .5rem 1.25rem 1.25rem; }
.sg-apercu { display: flex; align-items: center; gap: .9rem; background: var(--sg-brume); border-radius: 16px; padding: .85rem; margin-bottom: 1rem; }
.sg-apercu-av { width: 56px; height: 56px; border-radius: 30%; background: linear-gradient(145deg, #8B5CF6, var(--sg-violet)); color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.15rem; overflow: hidden; flex-shrink: 0; cursor: pointer; position: relative; }
.sg-apercu-av img { width: 100%; height: 100%; object-fit: cover; }
.sg-apercu-av .bi { position: absolute; right: 2px; bottom: 2px; width: 20px; height: 20px; border-radius: 50%; background: #fff; color: var(--sg-violet); font-size: .6rem; display: flex; align-items: center; justify-content: center; }
.sg-apercu-nom { font-family: 'Sora', sans-serif; font-weight: 600; color: var(--sg-encre); }
.sg-apercu-sous { font-size: .76rem; color: var(--sg-gris); }
.sg-visibilite { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.sg-visibilite label { border: 1.5px solid var(--sg-trait); border-radius: 14px; padding: .65rem .75rem; cursor: pointer; font-size: .8rem; color: var(--sg-gris); display: flex; gap: .55rem; align-items: flex-start; }
.sg-visibilite label strong { display: block; color: var(--sg-encre); font-size: .84rem; }
.sg-visibilite input { margin-top: .15rem; accent-color: var(--sg-violet); }
.sg-visibilite label:has(input:checked) { border-color: var(--sg-violet); background: var(--sg-brume); }

@media (min-width: 576px) {
    .sg-btn-libelle { display: inline; }
    .sg-tete h1 { font-size: 1.75rem; }
    .sg-decouvrir { grid-auto-flow: row; grid-auto-columns: auto; grid-template-columns: repeat(2, 1fr); overflow: visible; padding: 0; margin: 0; }
}
@media (min-width: 992px) {
    .sg-decouvrir { grid-template-columns: repeat(3, 1fr); }
    .sg-ligne { padding: 1rem 1.25rem; }
}
</style>
@endpush

@section('content')
<div class="sg-page">
    <header class="sg-tete">
        <div>
            <h1 class="sg-titre">Discussions de groupe</h1>
            <div class="sg-tete-sous">
                {{ $mesGroupes->count() }} groupe{{ $mesGroupes->count() > 1 ? 's' : '' }}
                @if($totalNonLus) · <strong>{{ $totalNonLus }} message{{ $totalNonLus > 1 ? 's' : '' }} non lu{{ $totalNonLus > 1 ? 's' : '' }}</strong>@endif
            </div>
        </div>
        <button type="button" class="sg-btn sg-btn-primaire" data-bs-toggle="modal" data-bs-target="#gpCreateModal" aria-label="Créer un groupe">
            <i class="fas fa-plus"></i><span class="sg-btn-libelle">Créer un groupe</span>
        </button>
    </header>

    @if($mesGroupes->count() > 3 || $groupesPublics->count())
    <div class="sg-recherche">
        <i class="fas fa-magnifying-glass"></i>
        <input type="search" id="sgFiltre" placeholder="Rechercher un groupe…" aria-label="Rechercher un groupe" autocomplete="off">
    </div>
    @endif

    @if($groupesPublics->count())
    <div class="sg-onglets" role="tablist">
        <button type="button" class="sg-onglet actif" data-onglet="mes" role="tab" aria-selected="true">Mes groupes <span class="n">{{ $mesGroupes->count() }}</span></button>
        <button type="button" class="sg-onglet" data-onglet="decouvrir" role="tab" aria-selected="false"><i class="fas fa-compass"></i>À découvrir <span class="n">{{ $groupesPublics->count() }}</span></button>
    </div>
    @endif

    {{-- ══ Mes groupes ══ --}}
    <section data-panneau="mes">
        @if($mesGroupes->count())
            <div class="sg-liste">
                @foreach($mesGroupes as $g)
                    @php
                        $dm = $g->dernierMessage;
                        $n = (int) ($nonLus[$g->id] ?? 0);
                        $auteurDm = $dm ? ($dm->sender_id === auth()->id() ? 'Vous' : ($dm->sender?->prenoms ?: $dm->sender?->name)) : null;
                        $texteDm = $dm ? ($dm->contenu ?: ($dm->shared_type ? 'a partagé un contenu' : 'a envoyé une pièce jointe')) : null;
                    @endphp
                    <a href="{{ route('social.groups.show', $g) }}" class="sg-ligne {{ $n ? 'a-lire' : '' }}" data-nom="{{ mb_strtolower($g->nom.' '.$g->description) }}">
                        @include('social.groups._pastille', ['g' => $g, 'actif' => $dm && $dm->created_at->gt(now()->subMinutes(10))])
                        <div class="sg-ligne-corps">
                            <div class="sg-ligne-haut">
                                <span class="sg-ligne-nom">{{ $g->nom }}</span>
                                @if($g->is_public)<span class="sg-public">Public</span>@endif
                                <span class="sg-ligne-heure">{{ $quand($dm?->created_at ?? $g->created_at) }}</span>
                            </div>
                            <div class="sg-ligne-bas">
                                <span class="sg-ligne-apercu">
                                    @if($dm)<b>{{ $auteurDm }} :</b> {{ \Illuminate\Support\Str::limit($texteDm, 90) }}
                                    @else{{ $g->members_count }} membre{{ $g->members_count > 1 ? 's' : '' }} · aucun message pour l'instant
                                    @endif
                                </span>
                                @if($n)<span class="sg-nonlus" aria-label="{{ $n }} non lu(s)">{{ $n > 99 ? '99+' : $n }}</span>@endif
                            </div>
                        </div>
                    </a>
                @endforeach
                <div class="sg-aucun" data-aucun>Aucun groupe ne correspond à cette recherche.</div>
            </div>
        @else
            <div class="sg-liste sg-vide">
                <div class="sg-vide-icone"><i class="fas fa-people-group"></i></div>
                <div class="sg-titre" style="font-size:1.1rem;">Lancez votre premier groupe</div>
                <p>Réunissez une équipe, un projet ou un service autour d'une discussion, avec fichiers, notes vocales et contenus de l'ERP.</p>
                <button type="button" class="sg-btn sg-btn-primaire" data-bs-toggle="modal" data-bs-target="#gpCreateModal"><i class="fas fa-plus"></i>Créer un groupe</button>
            </div>
        @endif
    </section>

    {{-- ══ À découvrir (groupes publics) ══ --}}
    @if($groupesPublics->count())
    <section data-panneau="decouvrir" hidden>
        <div class="sg-decouvrir">
            @foreach($groupesPublics as $g)
                <a href="{{ route('social.groups.show', $g) }}" class="sg-carte" style="--teinte: {{ $g->teinte }};" data-nom="{{ mb_strtolower($g->nom.' '.$g->description) }}">
                    @include('social.groups._pastille', ['g' => $g, 'taille' => 'lg', 'actif' => $g->dernierMessage && $g->dernierMessage->created_at->gt(now()->subMinutes(10))])
                    <div>
                        <p class="sg-carte-nom">{{ $g->nom }}</p>
                        <p class="sg-carte-desc">{{ $g->description ?: 'Groupe ouvert à tous les collaborateurs.' }}</p>
                    </div>
                    <div class="sg-carte-pied">
                        <span><i class="fas fa-users me-1"></i>{{ $g->members_count }} · <i class="fas fa-message ms-1 me-1"></i>{{ $g->messages_count }}</span>
                        <span class="sg-btn sg-btn-doux"><i class="fas fa-right-to-bracket"></i>Rejoindre</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
    @endif
</div>

{{-- ══ Modale : créer un groupe (plein écran sur mobile) ══ --}}
<div class="modal fade sg-modale" id="gpCreateModal" tabindex="-1" aria-labelledby="gpCreateTitre">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form action="{{ route('social.groups.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title sg-titre" id="gpCreateTitre">Créer un groupe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="sg-apercu">
                    <label class="sg-apercu-av" for="gpAvatar" title="Choisir une image">
                        <span id="gpApercuIni">G</span>
                        <span class="bi"><i class="fas fa-camera"></i></span>
                    </label>
                    <input type="file" name="avatar" id="gpAvatar" accept="image/jpeg,image/png,image/webp" hidden>
                    <div class="min-w-0">
                        <div class="sg-apercu-nom text-truncate" id="gpApercuNom">Nouveau groupe</div>
                        <div class="sg-apercu-sous" id="gpApercuSous">Vous · administrateur</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="gpNom">Nom du groupe <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="gpNom" class="form-control" required maxlength="100" placeholder="Ex. Équipe projet portail, Service RH…">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="gpDesc">À quoi sert ce groupe ? <span class="text-muted small">(facultatif)</span></label>
                    <textarea name="description" id="gpDesc" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Membres</label>
                    <select name="members[]" multiple class="form-select gp-members-select" placeholder="Ajouter des collaborateurs…">
                        @foreach($collaborateurs as $u)
                            <option value="{{ $u->id }}">{{ trim(($u->prenoms ?? '').' '.$u->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sg-visibilite">
                    <label><input type="radio" name="is_public" value="0" checked><span><strong><i class="fas fa-lock me-1"></i>Privé</strong>Sur invitation uniquement</span></label>
                    <label><input type="radio" name="is_public" value="1"><span><strong><i class="fas fa-globe me-1"></i>Public</strong>Tout collaborateur peut rejoindre</span></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="sg-btn sg-btn-doux" data-bs-dismiss="modal">Annuler</button>
                <button class="sg-btn sg-btn-primaire"><i class="fas fa-check"></i>Créer le groupe</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"
        integrity="sha384-cnROoUgVILyibe3J0zhzWoJ9p2WmdnK7j/BOTSWqVDbC1pVw2d+i6Q/1ESKJKCYf" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Onglets Mes groupes / À découvrir
    const onglets = document.querySelectorAll('[data-onglet]');
    onglets.forEach(function (b) {
        b.addEventListener('click', function () {
            onglets.forEach(x => { x.classList.toggle('actif', x === b); x.setAttribute('aria-selected', x === b); });
            document.querySelectorAll('[data-panneau]').forEach(p => p.hidden = p.dataset.panneau !== b.dataset.onglet);
        });
    });

    // Recherche instantanée dans les deux listes
    const filtre = document.getElementById('sgFiltre');
    filtre?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('[data-panneau]').forEach(function (p) {
            let visibles = 0;
            p.querySelectorAll('[data-nom]').forEach(el => { const ok = !q || el.dataset.nom.includes(q); el.hidden = !ok; visibles += ok; });
            const aucun = p.querySelector('[data-aucun]'); if (aucun) aucun.style.display = visibles ? 'none' : 'block';
        });
    });

    // Modale : aperçu vivant (nom, initiales, image, nombre de membres)
    const modal = document.getElementById('gpCreateModal');
    const nom = document.getElementById('gpNom'), ini = document.getElementById('gpApercuIni'), titre = document.getElementById('gpApercuNom');
    const sous = document.getElementById('gpApercuSous'), avatar = document.getElementById('gpAvatar');
    nom.addEventListener('input', function () {
        const v = this.value.trim();
        titre.textContent = v || 'Nouveau groupe';
        ini.textContent = (v.slice(0, 2) || 'G').toUpperCase();
    });
    avatar.addEventListener('change', function () {
        const f = this.files[0]; if (!f) return;
        const av = ini.parentElement; av.querySelector('img')?.remove();
        const img = document.createElement('img'); img.alt = ''; img.src = URL.createObjectURL(f);
        av.prepend(img); ini.hidden = true;
    });
    modal.addEventListener('shown.bs.modal', function () {
        const sel = modal.querySelector('.gp-members-select');
        if (sel && !sel.tomselect) {
            const ts = new TomSelect(sel, { plugins: ['remove_button'], maxOptions: 500, dropdownParent: 'body' });
            ts.on('change', () => { const n = ts.items.length; sous.textContent = 'Vous' + (n ? ' + ' + n + ' membre' + (n > 1 ? 's' : '') : ' · administrateur'); });
        }
        nom.focus();
    });
});
</script>
@endpush
