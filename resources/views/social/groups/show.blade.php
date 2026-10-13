@extends('layouts.app')

@section('title', $group->nom.' — Discussion')

@php
    $moi = auth()->user();
    $estAdmin = $group->estAdmin($moi);
    $couleursPersonnes = ['#7C3AED', '#0891B2', '#D97706', '#DB2777', '#059669', '#4F46E5', '#DC2626', '#0D9488'];
    $couleurDe = fn($id) => $couleursPersonnes[((int) $id) % count($couleursPersonnes)];
    $initiales = fn($u) => strtoupper(mb_substr($u?->prenoms ?: ($u?->name ?? '?'), 0, 1).mb_substr($u?->name ?? '', 0, 1));
    $photoDe = fn($u) => $u?->profile_photo_path ? (preg_match('#^https?://#', $u->profile_photo_path) ? $u->profile_photo_path : asset('storage/'.ltrim($u->profile_photo_path, '/'))) : null;
    $jourLibelle = function ($d) {
        if ($d->isToday()) return "Aujourd'hui";
        if ($d->isYesterday()) return 'Hier';
        return ucfirst($d->translatedFormat($d->isCurrentYear() ? 'l j F' : 'l j F Y'));
    };
    $messages->load(['piecesJointes', 'shared']);
    $enLigne = $membres->filter(fn($u) => $u->estEnLigne())->count();
    // Médias et fichiers partagés (panneau latéral)
    $pjsPartagees = $messages->flatMap(fn($m) => $m->piecesJointes)->reverse();
    $imagesPartagees = $pjsPartagees->where('categorie', 'image')->take(9);
    $fichiersPartages = $pjsPartagees->whereNotIn('categorie', ['image'])->take(6);
@endphp

@include('social.groups._styles')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet"
      integrity="sha384-piG3EtH1fBnPi68q4spy+Qgpb0dHK1D1dwk0GaHwFkvmUxYi526bBlk3xJcjEBsD" crossorigin="anonymous">
<style>
/* ── Mise en page : discussion plein écran sur mobile, panneau latéral à partir de lg ── */
.sc { --teinte: {{ $group->teinte }}; display: grid; grid-template-columns: 1fr; gap: 1rem; height: calc(100dvh - 118px); min-height: 460px; max-width: 1180px; margin: 0 auto; }
.sc-chat { display: flex; flex-direction: column; min-height: 0; background: #fff; border-radius: 22px; overflow: hidden; box-shadow: 0 1px 2px rgba(30,27,75,.06), 0 16px 40px -28px rgba(30,27,75,.45); }

/* En-tête teinté de la couleur du groupe */
.sc-tete { display: flex; align-items: center; gap: .65rem; padding: .7rem .75rem; background: linear-gradient(120deg, color-mix(in srgb, var(--teinte) 14%, #fff), #fff 70%); border-bottom: 1px solid var(--sg-trait); flex-shrink: 0; }
.sc-rond { width: 38px; height: 38px; border-radius: 50%; border: 0; background: transparent; color: var(--sg-encre); display: inline-flex; align-items: center; justify-content: center; text-decoration: none; flex-shrink: 0; transition: background .15s; }
.sc-rond:hover { background: rgba(30,27,75,.06); color: var(--sg-encre); }
.sc-ident { flex: 1; min-width: 0; display: flex; align-items: center; gap: .7rem; border: 0; background: transparent; text-align: left; padding: 0; color: inherit; }
.sc-nom { font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1rem; color: var(--sg-encre); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sc-meta { font-size: .74rem; color: var(--sg-gris); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sc-meta .on { color: var(--sg-menthe); font-weight: 600; }

/* Fil de messages */
.sc-fil { flex: 1; overflow-y: auto; padding: 1rem .8rem .5rem; display: flex; flex-direction: column; gap: 2px;
    background: radial-gradient(circle at 1px 1px, color-mix(in srgb, var(--teinte) 14%, transparent) 1px, transparent 0) 0 0 / 22px 22px, #FBFAFE; overscroll-behavior: contain; }
.sc-jour { align-self: center; margin: .9rem 0 .5rem; font-size: .7rem; font-weight: 600; color: var(--sg-gris); background: #fff; border: 1px solid var(--sg-trait); border-radius: 999px; padding: .2rem .75rem; }
.sc-msg { display: flex; gap: .5rem; max-width: 86%; align-items: flex-end; }
.sc-msg.debut { margin-top: .55rem; }
.sc-msg-av { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; color: #fff; font-size: .68rem; font-weight: 700; display: flex; align-items: center; justify-content: center; object-fit: cover; visibility: hidden; }
.sc-msg.fin .sc-msg-av { visibility: visible; }
.sc-bulle { background: #fff; border: 1px solid #EEEBF7; border-radius: 18px; padding: .5rem .8rem .35rem; min-width: 0; box-shadow: 0 1px 1px rgba(30,27,75,.04); }
.sc-msg:not(.fin) .sc-bulle { border-bottom-left-radius: 6px; }
.sc-msg:not(.debut) .sc-bulle { border-top-left-radius: 6px; }
.sc-auteur { font-size: .72rem; font-weight: 700; margin-bottom: .1rem; display: none; }
.sc-msg.debut .sc-auteur { display: block; }
.sc-texte { font-size: .9rem; color: #1F2937; white-space: pre-wrap; word-break: break-word; line-height: 1.45; }
.sc-heure { font-size: .63rem; color: #A3A1BC; text-align: right; margin-top: .15rem; font-variant-numeric: tabular-nums; }
.sc-msg.moi { align-self: flex-end; flex-direction: row-reverse; }
.sc-msg.moi .sc-msg-av, .sc-msg.moi .sc-auteur { display: none; }
.sc-msg.moi .sc-bulle { background: linear-gradient(135deg, var(--sg-violet), var(--sg-indigo)); border: 0; color: #fff; border-radius: 18px; }
.sc-msg.moi:not(.fin) .sc-bulle { border-bottom-right-radius: 6px; }
.sc-msg.moi:not(.debut) .sc-bulle { border-top-right-radius: 6px; }
.sc-msg.moi .sc-texte { color: #fff; }
.sc-msg.moi .sc-heure { color: rgba(255,255,255,.7); }
.sc-msg.nouveau { animation: sc-arrivee .25s ease-out; }
@keyframes sc-arrivee { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) { .sc-msg.nouveau { animation: none; } }

/* Pièces jointes et contenus partagés dans les bulles */
.gc-msg-pjs { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: .3rem; margin-top: .35rem; }
.gc-msg-pj-img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; cursor: zoom-in; display: block; }
.gc-msg-pj-file { grid-column: 1 / -1; display: flex; align-items: center; gap: .5rem; padding: .45rem .6rem; background: rgba(30,27,75,.05); border-radius: 12px; text-decoration: none; color: inherit; font-size: .78rem; }
.sc-msg.moi .gc-msg-pj-file { background: rgba(255,255,255,.16); color: #fff; }
.gc-msg-pj-audio, .gc-msg-pj-video { grid-column: 1 / -1; width: 100%; max-width: 280px; border-radius: 12px; }
.gc-msg-pj-video { max-height: 220px; background: #000; }
.gc-msg-shared { display: flex; gap: .6rem; padding: .55rem .65rem; margin-top: .35rem; border-radius: 14px; background: color-mix(in srgb, var(--teinte) 8%, #fff); border: 1px solid color-mix(in srgb, var(--teinte) 22%, #fff); text-decoration: none; color: var(--sg-encre); }
.sc-msg.moi .gc-msg-shared { background: rgba(255,255,255,.95); border-color: transparent; }
.gc-msg-shared-icon { width: 34px; height: 34px; border-radius: 10px; background: var(--teinte); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.gc-msg-shared-body { min-width: 0; }
.gc-msg-shared-label { font-size: .6rem; text-transform: uppercase; letter-spacing: .06em; font-weight: 700; color: var(--teinte); }
.gc-msg-shared-title { font-size: .82rem; font-weight: 600; }
.gc-msg-shared-excerpt { font-size: .72rem; color: var(--sg-gris); }

.sc-vide { margin: auto; text-align: center; padding: 2rem 1rem; color: var(--sg-gris); }
.sc-vide .sg-pastille { margin-bottom: .9rem; }
.sc-vide strong { display: block; font-family: 'Sora', sans-serif; color: var(--sg-encre); font-size: 1.05rem; margin-bottom: .3rem; }

/* Saisie */
.sc-saisie { border-top: 1px solid var(--sg-trait); padding: .55rem .6rem calc(.55rem + env(safe-area-inset-bottom)); background: #fff; flex-shrink: 0; }
.gc-composer-preview { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .5rem; }
.gc-preview-item { position: relative; display: inline-flex; align-items: center; gap: .4rem; flex-shrink: 0; max-width: 200px; background: var(--sg-brume); border-radius: 12px; padding: .35rem .5rem; font-size: .74rem; color: var(--sg-encre); }
.gc-preview-item img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; }
.gc-preview-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gc-preview-rm { border: 0; background: #fff; width: 20px; height: 20px; border-radius: 50%; color: var(--sg-gris); line-height: 1; padding: 0; flex-shrink: 0; }
.gc-preview-share { border: 1px solid color-mix(in srgb, var(--teinte) 30%, #fff); }
.sc-ligne-saisie { display: flex; align-items: flex-end; gap: .4rem; }
.sc-plus { transition: transform .2s; }
.sc-saisie.outils-ouverts .sc-plus { transform: rotate(45deg); background: var(--sg-brume); color: var(--sg-violet); }
.sc-ligne-saisie { position: relative; }
.sc-outils { display: none; position: absolute; left: 0; bottom: calc(100% + 10px); z-index: 20; gap: .35rem; flex-wrap: wrap; background: #fff; border: 1px solid var(--sg-trait); border-radius: 18px; padding: .5rem; box-shadow: 0 16px 36px -18px rgba(30,27,75,.45); }
.sc-saisie.outils-ouverts .sc-outils { display: flex; }
.sc-outil { border: 0; background: var(--sg-brume); color: var(--sg-violet); border-radius: 12px; padding: .45rem .7rem; font-size: .76rem; font-weight: 600; display: inline-flex; align-items: center; gap: .35rem; }
.sc-outil:hover { background: #EDE9FE; }
.gc-composer-input-wrap { flex: 1; position: relative; min-width: 0; }
.gc-composer-input-wrap textarea { width: 100%; resize: none; border: 1.5px solid var(--sg-trait); background: #F8F7FC; border-radius: 22px; padding: .6rem 1rem; font-size: .92rem; line-height: 1.4; max-height: 140px; color: var(--sg-encre); transition: border-color .15s, background .15s; display: block; }
.gc-composer-input-wrap textarea:focus { outline: 0; border-color: var(--sg-violet); background: #fff; }
.gc-composer-send { width: 44px; height: 44px; border-radius: 50%; border: 0; flex-shrink: 0; background: linear-gradient(135deg, var(--sg-violet), var(--sg-indigo)); color: #fff; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 18px -8px var(--sg-indigo); transition: transform .15s; }
.gc-composer-send:hover { transform: scale(1.05); }
.gc-composer-send:disabled { opacity: .5; transform: none; }

/* Suggestions @_ (partage rapide) */
.gc-mention-popup { position: absolute; bottom: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--sg-trait); border-radius: 14px; max-height: 260px; overflow-y: auto; z-index: 100; box-shadow: 0 16px 32px -16px rgba(30,27,75,.35); }
.gc-mention-header { padding: .4rem .8rem; font-size: .66rem; text-transform: uppercase; font-weight: 700; color: var(--sg-gris); letter-spacing: .05em; background: #FBFAFE; }
.gc-mention-item { display: block; padding: .5rem .8rem; cursor: pointer; font-size: .82rem; color: var(--sg-encre); }
.gc-mention-item:hover, .gc-mention-item.is-active { background: var(--sg-brume); color: var(--sg-violet); }
.gc-mention-key { color: var(--sg-violet); font-weight: 600; margin-right: .35rem; }
.gc-mention-excerpt { display: block; font-size: .7rem; color: var(--sg-gris); }
.gc-mention-empty { padding: 1rem; color: #9CA3AF; font-size: .82rem; text-align: center; }

/* Panneau latéral (coulissant sous lg) */
.sc-cote .offcanvas-body { display: flex; flex-direction: column; gap: .9rem; }
.sc-carte { background: #fff; border-radius: 20px; padding: 1rem 1.1rem; border: 1px solid var(--sg-trait); }
.sc-carte-titre { font-size: .7rem; text-transform: uppercase; letter-spacing: .07em; font-weight: 700; color: var(--sg-gris); margin-bottom: .65rem; display: flex; justify-content: space-between; align-items: center; }
.sc-apropos { text-align: center; }
.sc-apropos .sg-pastille { margin: .2rem auto .7rem; }
.sc-apropos h2 { font-size: 1.1rem; margin: 0 0 .25rem; }
.sc-apropos p { font-size: .82rem; color: var(--sg-gris); margin: 0; }
.sc-membre { display: flex; align-items: center; gap: .6rem; padding: .35rem 0; font-size: .84rem; color: var(--sg-encre); }
.sc-membre-av { position: relative; width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0; color: #fff; font-size: .7rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
.sc-membre-av img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.sc-membre-av .pt { position: absolute; right: -1px; bottom: -1px; width: 11px; height: 11px; border-radius: 50%; border: 2px solid #fff; background: #CBD5E1; }
.sc-membre-av .pt.on { background: var(--sg-menthe); }
.sc-role { margin-left: auto; font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #B45309; background: #FEF3C7; border-radius: 999px; padding: .1rem .5rem; }
.sc-galerie { display: grid; grid-template-columns: repeat(3, 1fr); gap: .3rem; }
.sc-galerie img { width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 10px; }
.sc-fichier { display: flex; align-items: center; gap: .55rem; padding: .4rem 0; font-size: .8rem; color: var(--sg-encre); text-decoration: none; }
.sc-fichier i { width: 30px; height: 30px; border-radius: 9px; background: var(--sg-brume); color: var(--sg-violet); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sc-fichier span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Modales */
.sg-modale .modal-content { border: 0; border-radius: 24px; }
.sg-modale .modal-header, .sg-modale .modal-footer { border: 0; }
.gc-share-results { margin-top: .75rem; max-height: 400px; overflow-y: auto; }
.gc-share-result { display: flex; gap: .65rem; padding: .6rem .5rem; border-radius: 12px; cursor: pointer; }
.gc-share-result:hover { background: var(--sg-brume); }
.gc-share-result-icon { width: 36px; height: 36px; border-radius: 10px; background: var(--sg-brume); color: var(--sg-violet); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.gc-share-result-label { font-size: .64rem; text-transform: uppercase; letter-spacing: .05em; color: var(--sg-gris); font-weight: 700; }
.gc-share-result-title { font-size: .86rem; font-weight: 600; color: var(--sg-encre); }
.gc-share-result-excerpt { font-size: .74rem; color: var(--sg-gris); }
.gc-share-empty { padding: 1.5rem 0; color: #9CA3AF; text-align: center; font-size: .85rem; }
.gc-rec-bar { height: 6px; background: var(--sg-brume); border-radius: 999px; margin: .6rem 0; overflow: hidden; }
#gcRecBarFill { height: 100%; width: 0%; background: linear-gradient(90deg, #F43F5E, #DC2626); transition: width .3s linear; }

/* Smartphone : discussion pleine largeur, coins droits (convention de l'application) */
@media (max-width: 575.98px) {
    .sc { height: calc(100dvh - 60px); gap: 0; }
    .sc-chat { border-radius: 0; box-shadow: none; }
}
@media (min-width: 768px) {
    .sc-plus { display: none; }
    .sc-outils { display: flex; position: static; padding: 0; border: 0; box-shadow: none; background: transparent; gap: 0; flex-wrap: nowrap; }
    .sc-outil span { display: none; }
    .sc-outil { padding: 0; width: 38px; height: 44px; background: transparent; color: var(--sg-gris); justify-content: center; }
    .sc-outil:hover { color: var(--sg-violet); background: var(--sg-brume); }
    .sc-ligne-saisie { align-items: flex-end; }
    .sc-saisie { padding: .7rem 1rem; }
    .sc-fil { padding: 1.25rem 1.5rem .75rem; }
    .sc-msg { max-width: 72%; }
}
@media (min-width: 992px) {
    .sc { grid-template-columns: 1fr 300px; height: calc(100dvh - 150px); }
    .sc-cote.offcanvas-lg { background: transparent; }
    .sc-cote .offcanvas-body { padding: 0; overflow-y: auto; }
    .sc-btn-membres { display: none; }
}
</style>
@endpush

@section('content')
<div class="sc">
    <section class="sc-chat" aria-label="Discussion {{ $group->nom }}">
        <header class="sc-tete">
            <a href="{{ route('social.groups.index') }}" class="sc-rond" title="Tous les groupes" aria-label="Retour aux groupes"><i class="fas fa-arrow-left"></i></a>
            <button type="button" class="sc-ident" data-bs-toggle="offcanvas" data-bs-target="#scCote" aria-controls="scCote" title="Infos du groupe">
                @include('social.groups._pastille', ['g' => $group, 'taille' => 'sm'])
                <span class="min-w-0">
                    <span class="sc-nom d-block">{{ $group->nom }}</span>
                    <span class="sc-meta d-block">
                        {{ $membres->count() }} membre{{ $membres->count() > 1 ? 's' : '' }}
                        @if($enLigne) · <span class="on">{{ $enLigne }} en ligne</span>@endif
                        @if($group->is_public) · <i class="fas fa-globe"></i> Public @endif
                    </span>
                </span>
            </button>
            @if($estAdmin)
                <button type="button" class="sc-rond" data-bs-toggle="modal" data-bs-target="#gcInviteModal" title="Ajouter des membres" aria-label="Ajouter des membres"><i class="fas fa-user-plus"></i></button>
            @endif
            <div class="dropdown">
                <button type="button" class="sc-rond" data-bs-toggle="dropdown" aria-label="Plus d'actions"><i class="fas fa-ellipsis-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end" style="border-radius:14px;">
                    <li><button class="dropdown-item sc-btn-membres" type="button" data-bs-toggle="offcanvas" data-bs-target="#scCote"><i class="fas fa-users me-2"></i>Membres et fichiers</button></li>
                    <li>
                        <form action="{{ route('social.groups.leave', $group) }}" method="POST" onsubmit="return confirm('Quitter ce groupe ?');">@csrf
                            <button class="dropdown-item"><i class="fas fa-right-from-bracket me-2"></i>Quitter le groupe</button>
                        </form>
                    </li>
                    @if($estAdmin)
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('social.groups.destroy', $group) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce groupe et ses messages ?');">@csrf @method('DELETE')
                            <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Supprimer le groupe</button>
                        </form>
                    </li>
                    @endif
                </ul>
            </div>
        </header>

        <div class="sc-fil" id="gcMessages" data-group-id="{{ $group->id }}" data-last-id="{{ $messages->last()?->id ?? 0 }}" aria-live="polite">
            @php $prec = null; @endphp
            @forelse($messages as $i => $msg)
                @php
                    $suiv = $messages[$i + 1] ?? null;
                    $nouveauJour = !$prec || !$prec->created_at->isSameDay($msg->created_at);
                    $debut = $nouveauJour || $prec->sender_id !== $msg->sender_id || $prec->created_at->diffInMinutes($msg->created_at) > 5;
                    $fin = !$suiv || $suiv->sender_id !== $msg->sender_id || !$suiv->created_at->isSameDay($msg->created_at) || $msg->created_at->diffInMinutes($suiv->created_at) > 5;
                    $estMoi = $msg->sender_id === $moi->id;
                    $shared = $msg->shared_type && $msg->shared_id && $msg->shared ? \App\Services\Social\ShareableResolver::normalize($msg->shared) : null;
                    $prec = $msg;
                @endphp
                @if($nouveauJour)<div class="sc-jour">{{ $jourLibelle($msg->created_at) }}</div>@endif
                <div class="sc-msg {{ $estMoi ? 'moi' : '' }} {{ $debut ? 'debut' : '' }} {{ $fin ? 'fin' : '' }}" data-msg-id="{{ $msg->id }}" data-sender="{{ $msg->sender_id }}" data-ts="{{ $msg->created_at->timestamp }}">
                    @if($photoDe($msg->sender))
                        <img class="sc-msg-av" src="{{ $photoDe($msg->sender) }}" alt="">
                    @else
                        <span class="sc-msg-av" style="background:{{ $couleurDe($msg->sender_id) }};">{{ $initiales($msg->sender) }}</span>
                    @endif
                    <div class="sc-bulle">
                        <div class="sc-auteur" style="color:{{ $couleurDe($msg->sender_id) }};">{{ trim($msg->sender?->prenoms.' '.$msg->sender?->name) }}</div>
                        @if($msg->contenu)<div class="sc-texte">{{ $msg->contenu }}</div>@endif
                        @if($msg->piecesJointes->count())
                            <div class="gc-msg-pjs">
                                @foreach($msg->piecesJointes as $pj)
                                    @if($pj->categorie === 'image')
                                        <a href="{{ $pj->url }}" target="_blank" rel="noopener"><img src="{{ $pj->url }}" class="gc-msg-pj-img" alt="{{ $pj->nom_original }}" loading="lazy"></a>
                                    @elseif($pj->categorie === 'audio')
                                        <audio class="gc-msg-pj-audio" controls preload="metadata" src="{{ $pj->url }}"></audio>
                                    @elseif($pj->categorie === 'video')
                                        <video class="gc-msg-pj-video" controls preload="metadata" src="{{ $pj->url }}"></video>
                                    @else
                                        <a href="{{ $pj->url }}" target="_blank" rel="noopener" class="gc-msg-pj-file"><i class="fas {{ $pj->icone }}"></i><span class="text-truncate">{{ $pj->nom_original ?? 'Fichier' }}</span></a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        @if($shared)
                            <a href="{{ $shared['url'] ?? '#' }}" class="gc-msg-shared" target="_blank" rel="noopener">
                                <span class="gc-msg-shared-icon"><i class="fas {{ $shared['icon'] }}"></i></span>
                                <span class="gc-msg-shared-body">
                                    <span class="gc-msg-shared-label d-block">{{ $shared['label'] }}</span>
                                    <span class="gc-msg-shared-title d-block">{{ $shared['title'] }}</span>
                                    @if(!empty($shared['excerpt']))<span class="gc-msg-shared-excerpt d-block">{{ \Illuminate\Support\Str::limit($shared['excerpt'], 100) }}</span>@endif
                                </span>
                            </a>
                        @endif
                        <div class="sc-heure">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @empty
                <div class="sc-vide gc-empty">
                    @include('social.groups._pastille', ['g' => $group, 'taille' => 'lg'])
                    <strong>Bienvenue dans {{ $group->nom }}</strong>
                    Écrivez le premier message, partagez un fichier ou un contenu de l'ERP.
                </div>
            @endforelse
        </div>

        <form class="sc-saisie" id="gcComposer" enctype="multipart/form-data"
              data-post-url="{{ route('social.groups.postMessage', $group) }}"
              data-search-url="{{ route('social.shareable.search') }}"
              data-mention-url="{{ route('social.mention.autocomplete') }}">
            @csrf
            <div class="gc-composer-preview" id="gcPreview" hidden></div>
            <div class="sc-ligne-saisie">
                <button type="button" class="sc-rond sc-plus" id="scPlus" aria-label="Joindre ou partager" aria-expanded="false" aria-controls="scOutils"><i class="fas fa-plus"></i></button>
                <div class="sc-outils" id="scOutils">
                    <button type="button" class="sc-outil" id="gcAttachBtn" title="Joindre un fichier"><i class="fas fa-paperclip"></i><span>Fichier</span></button>
                    <button type="button" class="sc-outil" data-bs-toggle="modal" data-bs-target="#gcShareModal" title="Partager un contenu de l'ERP"><i class="fas fa-share-nodes"></i><span>Contenu ERP</span></button>
                    <button type="button" class="sc-outil" id="gcVoiceBtn" title="Note vocale (1 min max)"><i class="fas fa-microphone"></i><span>Vocal</span></button>
                    <button type="button" class="sc-outil" id="gcVideoBtn" title="Note vidéo (1 min max)"><i class="fas fa-video"></i><span>Vidéo</span></button>
                </div>
                <input type="file" name="pieces_jointes[]" id="gcFiles" multiple hidden
                       accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                <input type="hidden" name="shared_type" id="gcSharedType">
                <input type="hidden" name="shared_id" id="gcSharedId">
                <div class="gc-composer-input-wrap">
                    <textarea name="contenu" id="gcMsgInput" rows="1" placeholder="Écrire un message…" title="Entrée pour envoyer · Maj+Entrée pour aller à la ligne · @_ pour partager un contenu de l'ERP" maxlength="2000" autocomplete="off" aria-label="Votre message"></textarea>
                    <div class="gc-mention-popup" id="gcMentionPopup" hidden></div>
                </div>
                <button type="submit" title="Envoyer (Entrée)" aria-label="Envoyer" class="gc-composer-send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </form>
    </section>

    {{-- Panneau latéral : à propos, membres, médias et fichiers (coulissant sous lg) --}}
    <aside class="sc-cote offcanvas-lg offcanvas-end" tabindex="-1" id="scCote" aria-labelledby="scCoteTitre">
        <div class="offcanvas-header d-lg-none">
            <h5 class="offcanvas-title sg-titre" id="scCoteTitre">Infos du groupe</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#scCote" aria-label="Fermer"></button>
        </div>
        <div class="offcanvas-body">
            <div class="sc-carte sc-apropos">
                @include('social.groups._pastille', ['g' => $group, 'taille' => 'lg'])
                <h2 class="sg-titre">{{ $group->nom }}</h2>
                <p>{{ $group->description ?: ($group->is_public ? 'Groupe public, ouvert à tous les collaborateurs.' : 'Groupe privé, sur invitation.') }}</p>
                <p class="mt-2" style="font-size:.72rem;">Créé {{ $group->created_at->translatedFormat('j F Y') }}@if($group->auteur) par {{ trim($group->auteur->prenoms.' '.$group->auteur->name) }}@endif</p>
            </div>

            <div class="sc-carte">
                <div class="sc-carte-titre">
                    <span>Membres · {{ $membres->count() }}</span>
                    @if($estAdmin)<button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#gcInviteModal" style="font-size:.72rem;"><i class="fas fa-user-plus me-1"></i>Ajouter</button>@endif
                </div>
                @foreach($membres->sortByDesc(fn($u) => [$u->estEnLigne(), ($u->pivot->role ?? '') === 'admin']) as $u)
                    <div class="sc-membre">
                        <span class="sc-membre-av" style="background:{{ $couleurDe($u->id) }};">
                            @if($photoDe($u))<img src="{{ $photoDe($u) }}" alt="">@else{{ $initiales($u) }}@endif
                            <span class="pt {{ $u->estEnLigne() ? 'on' : '' }}" title="{{ $u->estEnLigne() ? 'En ligne' : 'Hors ligne' }}"></span>
                        </span>
                        <span class="text-truncate">{{ trim($u->prenoms.' '.$u->name) }}{{ $u->id === $moi->id ? ' (vous)' : '' }}</span>
                        @if(($u->pivot->role ?? 'member') === 'admin')<span class="sc-role">Admin</span>@endif
                    </div>
                @endforeach
            </div>

            @if($imagesPartagees->count() || $fichiersPartages->count())
            <div class="sc-carte">
                <div class="sc-carte-titre"><span>Médias et fichiers</span></div>
                @if($imagesPartagees->count())
                    <div class="sc-galerie mb-2">
                        @foreach($imagesPartagees as $pj)<a href="{{ $pj->url }}" target="_blank" rel="noopener"><img src="{{ $pj->url }}" alt="{{ $pj->nom_original }}" loading="lazy"></a>@endforeach
                    </div>
                @endif
                @foreach($fichiersPartages as $pj)
                    <a href="{{ $pj->url }}" target="_blank" rel="noopener" class="sc-fichier"><i class="fas {{ $pj->icone }}"></i><span>{{ $pj->nom_original ?? 'Fichier' }}</span></a>
                @endforeach
            </div>
            @endif
        </div>
    </aside>
</div>

@if($estAdmin)
<div class="modal fade sg-modale" id="gcInviteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <form action="{{ route('social.groups.invite', $group) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title sg-titre">Ajouter des membres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Collaborateurs à ajouter</label>
                <select name="members[]" multiple class="form-select gc-invite-select" required>
                    @foreach($usersDispo as $u)
                    <option value="{{ $u->id }}">{{ trim(($u->prenoms ?? '').' '.$u->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="sg-btn sg-btn-doux" data-bs-dismiss="modal">Annuler</button>
                <button class="sg-btn sg-btn-primaire"><i class="fas fa-user-plus"></i>Ajouter</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modale : note vocale / vidéo (60 s max) --}}
<div class="modal fade sg-modale" id="gcRecordModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sg-titre" id="gcRecTitle"><i class="fas fa-microphone me-2"></i>Note vocale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" id="gcRecClose" aria-label="Fermer"></button>
            </div>
            <div class="modal-body text-center">
                <video id="gcRecPreview" muted playsinline autoplay hidden style="width:100%; max-height:280px; background:#000; border-radius:16px; margin-bottom:.75rem;"></video>
                <div id="gcRecTimer" class="sg-titre" style="font-size:2rem; font-variant-numeric:tabular-nums;">0:00 / 1:00</div>
                <div class="gc-rec-bar"><div id="gcRecBarFill"></div></div>
                <div id="gcRecStatus" class="text-muted mt-2" style="font-size:.85rem;">Appuyez sur Enregistrer pour commencer</div>
            </div>
            <div class="modal-footer justify-content-center flex-wrap">
                <button type="button" class="sg-btn" id="gcRecStart" style="background:#DC2626;color:#fff;"><i class="fas fa-circle"></i>Enregistrer</button>
                <button type="button" class="sg-btn sg-btn-doux" id="gcRecStop" hidden><i class="fas fa-stop"></i>Arrêter</button>
                <button type="button" class="sg-btn sg-btn-doux" id="gcRecReset" hidden><i class="fas fa-rotate-left"></i>Recommencer</button>
                <button type="button" class="sg-btn sg-btn-primaire" id="gcRecUse" hidden><i class="fas fa-paperclip"></i>Joindre au message</button>
            </div>
        </div>
    </div>
</div>

{{-- Modale : partager un contenu de l'ERP --}}
<div class="modal fade sg-modale" id="gcShareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title sg-titre"><i class="fas fa-share-nodes me-2" style="color:var(--sg-violet);"></i>Partager un contenu de l'ERP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <select id="gcShareModule" class="form-select" style="max-width:220px; border-radius:999px;">
                        @foreach(\App\Services\Social\ShareableResolver::MODULES as $key => $m)
                            <option value="{{ $key }}">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="gcShareSearch" class="form-control flex-grow-1" style="border-radius:999px; min-width:200px;" placeholder="Rechercher (2 caractères minimum)…" autocomplete="off">
                </div>
                <div id="gcShareResults" class="gc-share-results"></div>
                <details class="mt-2" style="font-size:.78rem;">
                    <summary style="cursor:pointer; color:var(--sg-violet); font-weight:600;">Astuce : partager directement depuis le message</summary>
                    <div style="padding:.6rem .8rem; background:var(--sg-brume); border-radius:12px; margin-top:.4rem;">
                        Tapez <code>@_module&amp;@_section&amp;@_titre</code>, par exemple <code>@_projet&amp;@_taches&amp;@_Livrer maquette</code>.
                        Les suggestions apparaissent au fur et à mesure.
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"
        integrity="sha384-cnROoUgVILyibe3J0zhzWoJ9p2WmdnK7j/BOTSWqVDbC1pVw2d+i6Q/1ESKJKCYf"
        crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var CURRENT_USER_ID = {{ (int) auth()->id() }};
    var CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
    var messagesEl = document.getElementById('gcMessages');
    var composer = document.getElementById('gcComposer');
    var input = composer.querySelector('[name="contenu"]');
    var postUrl = composer.dataset.postUrl;
    var groupId = messagesEl.dataset.groupId;
    var lastId = parseInt(messagesEl.dataset.lastId || '0', 10);

    // Scroll bas au chargement
    messagesEl.scrollTop = messagesEl.scrollHeight;

    function initialesFrom(name) {
        var parts = (name || '').trim().split(/\s+/);
        return ((parts[0] || '?')[0] + ((parts[1] || '')[0] || '')).toUpperCase();
    }
    var COULEURS = @json($couleursPersonnes);
    function couleurDe(id) { return COULEURS[Math.abs(parseInt(id, 10) || 0) % COULEURS.length]; }
    function jourLibelle(iso) {
        var d = new Date(iso + 'T00:00:00'), auj = new Date(); auj.setHours(0, 0, 0, 0);
        var diff = Math.round((auj - d) / 86400000);
        if (diff === 0) return "Aujourd'hui";
        if (diff === 1) return 'Hier';
        var t = d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
        return t.charAt(0).toUpperCase() + t.slice(1);
    }
    function dernierMessage() { var all = messagesEl.querySelectorAll('.sc-msg'); return all[all.length - 1] || null; }

    function appendMessage(m) {
        var isMine = m.sender_id === CURRENT_USER_ID;
        var auj = new Date();
        var jour = m.jour || (auj.getFullYear() + '-' + String(auj.getMonth() + 1).padStart(2, '0') + '-' + String(auj.getDate()).padStart(2, '0'));
        var ts = m.ts || Math.floor(Date.now() / 1000);
        var prec = dernierMessage();
        var memeJour = !!prec && prec.dataset.jour === jour;
        if (!memeJour) {
            var sep = document.createElement('div'); sep.className = 'sc-jour'; sep.textContent = jourLibelle(jour);
            messagesEl.appendChild(sep);
        }
        // Même auteur, même jour, moins de 5 min d'écart : la bulle rejoint le groupe précédent
        var suite = memeJour && String(prec.dataset.sender) === String(m.sender_id) && (ts - parseInt(prec.dataset.ts || '0', 10)) <= 300;
        if (suite) prec.classList.remove('fin');

        var wrap = document.createElement('div');
        wrap.className = 'sc-msg nouveau fin' + (isMine ? ' moi' : '') + (suite ? '' : ' debut');
        wrap.dataset.msgId = m.id; wrap.dataset.sender = m.sender_id; wrap.dataset.ts = ts; wrap.dataset.jour = jour;

        var av = document.createElement('span'); av.className = 'sc-msg-av';
        av.style.background = couleurDe(m.sender_id); av.textContent = initialesFrom(m.sender);
        wrap.appendChild(av);

        var body = document.createElement('div'); body.className = 'sc-bulle';
        var sender = document.createElement('div'); sender.className = 'sc-auteur'; sender.style.color = couleurDe(m.sender_id); sender.textContent = m.sender;
        body.appendChild(sender);
        if (m.contenu) {
            var text = document.createElement('div'); text.className = 'sc-texte'; text.textContent = m.contenu;
            body.appendChild(text);
        }
        if (Array.isArray(m.pieces) && m.pieces.length) {
            var pjs = document.createElement('div'); pjs.className = 'gc-msg-pjs';
            m.pieces.forEach(function (p) {
                if (p.categorie === 'image') {
                    var la = document.createElement('a'); la.href = p.url; la.target = '_blank'; la.rel = 'noopener';
                    var img = document.createElement('img'); img.className = 'gc-msg-pj-img'; img.src = p.url; img.loading = 'lazy'; img.alt = p.nom || '';
                    la.appendChild(img); pjs.appendChild(la);
                } else if (p.categorie === 'audio') {
                    var au = document.createElement('audio'); au.className = 'gc-msg-pj-audio'; au.controls = true; au.preload = 'metadata'; au.src = p.url;
                    pjs.appendChild(au);
                } else if (p.categorie === 'video') {
                    var vi = document.createElement('video'); vi.className = 'gc-msg-pj-video'; vi.controls = true; vi.preload = 'metadata'; vi.src = p.url;
                    pjs.appendChild(vi);
                } else {
                    var a = document.createElement('a'); a.className = 'gc-msg-pj-file'; a.href = p.url; a.target = '_blank'; a.rel = 'noopener';
                    var fi = document.createElement('i'); fi.className = 'fas fa-file'; a.appendChild(fi);
                    var span = document.createElement('span'); span.className = 'text-truncate'; span.textContent = p.nom || 'Fichier';
                    a.appendChild(span); pjs.appendChild(a);
                }
            });
            body.appendChild(pjs);
        }
        if (m.shared) {
            var sh = document.createElement('a'); sh.className = 'gc-msg-shared'; sh.href = m.shared.url || '#'; sh.target = '_blank'; sh.rel = 'noopener';
            var ic = document.createElement('span'); ic.className = 'gc-msg-shared-icon';
            var ici = document.createElement('i'); ici.className = 'fas ' + (m.shared.icon || 'fa-link'); ic.appendChild(ici);
            var b = document.createElement('span'); b.className = 'gc-msg-shared-body';
            var lbl = document.createElement('span'); lbl.className = 'gc-msg-shared-label d-block'; lbl.textContent = m.shared.label;
            var ttl = document.createElement('span'); ttl.className = 'gc-msg-shared-title d-block'; ttl.textContent = m.shared.title;
            b.appendChild(lbl); b.appendChild(ttl);
            if (m.shared.excerpt) { var ex = document.createElement('span'); ex.className = 'gc-msg-shared-excerpt d-block'; ex.textContent = m.shared.excerpt.substring(0, 100); b.appendChild(ex); }
            sh.appendChild(ic); sh.appendChild(b); body.appendChild(sh);
        }
        var date = document.createElement('div'); date.className = 'sc-heure'; date.textContent = m.date;
        body.appendChild(date);
        wrap.appendChild(body);
        messagesEl.appendChild(wrap);
        var empty = messagesEl.querySelector('.gc-empty'); if (empty) empty.remove();
    }

    // Messages rendus côté serveur : jour (pour l'enchaînement avec les messages reçus ensuite)
    messagesEl.querySelectorAll('.sc-msg').forEach(function (el) {
        var d = new Date(parseInt(el.dataset.ts, 10) * 1000);
        el.dataset.jour = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    });

    // ── Saisie : outils (+), zone qui s'agrandit, Entrée = envoyer ──
    var saisie = composer;
    var plus = document.getElementById('scPlus');
    plus.addEventListener('click', function () {
        var ouvert = saisie.classList.toggle('outils-ouverts');
        plus.setAttribute('aria-expanded', ouvert);
    });
    document.querySelectorAll('.sc-outil').forEach(function (b) {
        b.addEventListener('click', function () { saisie.classList.remove('outils-ouverts'); plus.setAttribute('aria-expanded', 'false'); });
    });
    function ajuster() { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 140) + 'px'; }
    input.addEventListener('input', ajuster);
    input.addEventListener('keydown', function (e) {
        var popup = document.getElementById('gcMentionPopup');
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing && (!popup || popup.hidden)) {
            e.preventDefault();
            composer.requestSubmit();
        }
    });

    // ── Pièces jointes : preview + suppression avant envoi ────────
    var filesInput = document.getElementById('gcFiles');
    var previewEl = document.getElementById('gcPreview');
    var stagedFiles = new DataTransfer();
    document.getElementById('gcAttachBtn').addEventListener('click', function () { filesInput.click(); });
    filesInput.addEventListener('change', function () {
        Array.from(filesInput.files).forEach(function (f) { stagedFiles.items.add(f); });
        filesInput.files = stagedFiles.files;
        renderPreview();
    });
    function renderPreview() {
        previewEl.innerHTML = '';
        var hasContent = false;
        Array.from(stagedFiles.files).forEach(function (f, idx) {
            hasContent = true;
            var item = document.createElement('div'); item.className = 'gc-preview-item';
            if (f.type.startsWith('image/')) {
                var img = document.createElement('img'); img.src = URL.createObjectURL(f);
                item.appendChild(img);
            } else {
                var ic = document.createElement('i'); ic.className = 'fas fa-file';
                item.appendChild(ic);
            }
            var name = document.createElement('span'); name.className = 'gc-preview-name'; name.textContent = f.name;
            var rm = document.createElement('button'); rm.type = 'button'; rm.className = 'gc-preview-rm';
            rm.innerHTML = '&times;';
            rm.addEventListener('click', function () {
                var dt = new DataTransfer();
                Array.from(stagedFiles.files).forEach(function (f2, i) { if (i !== idx) dt.items.add(f2); });
                stagedFiles = dt; filesInput.files = stagedFiles.files; renderPreview();
            });
            item.appendChild(name); item.appendChild(rm);
            previewEl.appendChild(item);
        });
        // Partage en cours
        if (stagedShared) {
            hasContent = true;
            var s = document.createElement('div'); s.className = 'gc-preview-item gc-preview-share';
            var ic = document.createElement('i'); ic.className = 'fas ' + stagedShared.icon;
            var name = document.createElement('span'); name.className = 'gc-preview-name'; name.textContent = '[' + stagedShared.label + '] ' + stagedShared.title;
            var rm = document.createElement('button'); rm.type = 'button'; rm.className = 'gc-preview-rm';
            rm.innerHTML = '&times;';
            rm.addEventListener('click', function () { stagedShared = null; document.getElementById('gcSharedType').value=''; document.getElementById('gcSharedId').value=''; renderPreview(); });
            s.appendChild(ic); s.appendChild(name); s.appendChild(rm);
            previewEl.appendChild(s);
        }
        previewEl.hidden = !hasContent;
    }

    // ── Modale partage ERP : recherche AJAX ──────────────────────
    var stagedShared = null;
    var searchInput = document.getElementById('gcShareSearch');
    var moduleSelect = document.getElementById('gcShareModule');
    var searchResults = document.getElementById('gcShareResults');
    var searchUrl = composer.dataset.searchUrl;
    var searchTimer = null;
    function runShareSearch() {
        clearTimeout(searchTimer);
        var q = searchInput.value.trim();
        if (q.length < 2) { searchResults.innerHTML = '<div class="gc-share-empty">Tapez au moins 2 caractères…</div>'; return; }
        var module = moduleSelect ? moduleSelect.value : 'all';
        searchTimer = setTimeout(function () {
            fetch(searchUrl + '?q=' + encodeURIComponent(q) + '&module=' + encodeURIComponent(module),
                { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function (data) {
                    searchResults.innerHTML = '';
                    if (!data.results || !data.results.length) {
                        searchResults.innerHTML = '<div class="gc-share-empty"><i class="fas fa-magnifying-glass me-1"></i>Aucun résultat.</div>';
                        return;
                    }
                    data.results.forEach(function (r) {
                        var row = document.createElement('div'); row.className = 'gc-share-result';
                        var ricon = document.createElement('div'); ricon.className = 'gc-share-result-icon';
                        var rii = document.createElement('i'); rii.className = 'fas ' + r.icon; ricon.appendChild(rii); row.appendChild(ricon);
                        var body = document.createElement('div'); body.className = 'gc-share-result-body';
                        var lbl = document.createElement('div'); lbl.className = 'gc-share-result-label'; lbl.textContent = r.label;
                        var t = document.createElement('div'); t.className = 'gc-share-result-title'; t.textContent = r.title;
                        body.appendChild(lbl); body.appendChild(t);
                        if (r.excerpt) { var ex = document.createElement('div'); ex.className = 'gc-share-result-excerpt'; ex.textContent = r.excerpt; body.appendChild(ex); }
                        row.appendChild(body);
                        row.addEventListener('click', function () {
                            stagedShared = r;
                            document.getElementById('gcSharedType').value = r.type;
                            document.getElementById('gcSharedId').value = r.id;
                            renderPreview();
                            bootstrap.Modal.getInstance(document.getElementById('gcShareModal'))?.hide();
                        });
                        searchResults.appendChild(row);
                    });
                })
                .catch(function (err) {
                    console.error('[Share] search error:', err);
                    searchResults.innerHTML = '<div class="gc-share-empty" style="color:#DC2626;">Erreur lors de la recherche : ' + (err.message || 'inconnue') + '</div>';
                });
        }, 300);
    }
    searchInput?.addEventListener('input', runShareSearch);
    moduleSelect?.addEventListener('change', runShareSearch);

    // ── Enregistrement note vocale/vidéo (MediaRecorder, max 60s) ─
    (function () {
        var MAX_SECONDS = 60;
        var voiceBtn = document.getElementById('gcVoiceBtn');
        var videoBtn = document.getElementById('gcVideoBtn');
        var modalEl = document.getElementById('gcRecordModal');
        if (!modalEl || !voiceBtn) return;
        var modal = null; // Bootstrap Modal instance
        var titleEl = document.getElementById('gcRecTitle');
        var previewEl = document.getElementById('gcRecPreview');
        var timerEl = document.getElementById('gcRecTimer');
        var barEl = document.getElementById('gcRecBarFill');
        var statusEl = document.getElementById('gcRecStatus');
        var startBtn = document.getElementById('gcRecStart');
        var stopBtn = document.getElementById('gcRecStop');
        var useBtn = document.getElementById('gcRecUse');
        var resetBtn = document.getElementById('gcRecReset');

        var currentMode = 'audio';
        var mediaStream = null;
        var recorder = null;
        var chunks = [];
        var startAt = 0;
        var timerInterval = null;
        var autoStopTimer = null;
        var recordedFile = null;

        function fmt(s) { s = Math.max(0, Math.floor(s)); return Math.floor(s/60) + ':' + String(s%60).padStart(2, '0'); }
        function setStatus(txt) { statusEl.textContent = txt; }
        function tick() {
            var elapsed = (performance.now() - startAt) / 1000;
            timerEl.textContent = fmt(elapsed) + ' / 1:00';
            barEl.style.width = Math.min(100, (elapsed/MAX_SECONDS)*100) + '%';
        }
        function reset() {
            if (recorder && recorder.state !== 'inactive') { try { recorder.stop(); } catch(_){} }
            if (mediaStream) { mediaStream.getTracks().forEach(t => t.stop()); mediaStream = null; }
            clearInterval(timerInterval); clearTimeout(autoStopTimer);
            chunks = []; recordedFile = null;
            previewEl.hidden = true; previewEl.srcObject = null; previewEl.src = '';
            timerEl.textContent = '0:00 / 1:00'; barEl.style.width = '0%';
            startBtn.hidden = false; stopBtn.hidden = true; useBtn.hidden = true; resetBtn.hidden = true;
            setStatus('Appuyez sur Enregistrer pour commencer');
        }
        async function openRecorder(mode) {
            currentMode = mode;
            titleEl.innerHTML = mode === 'video'
                ? '<i class="fas fa-video me-2"></i>Note vidéo'
                : '<i class="fas fa-microphone me-2"></i>Note vocale';
            reset();
            modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
        async function startRecording() {
            try {
                var constraints = currentMode === 'video' ? { audio: true, video: true } : { audio: true };
                mediaStream = await navigator.mediaDevices.getUserMedia(constraints);
                if (currentMode === 'video') {
                    previewEl.srcObject = mediaStream; previewEl.hidden = false;
                }
                // Choix du MIME supporté par le navigateur (Chrome/Firefox = webm, Safari = mp4)
                var mimeCandidates = currentMode === 'video'
                    ? ['video/webm;codecs=vp8,opus', 'video/webm', 'video/mp4']
                    : ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4'];
                var chosenMime = mimeCandidates.find(function (m) { return MediaRecorder.isTypeSupported(m); }) || '';
                recorder = new MediaRecorder(mediaStream, chosenMime ? { mimeType: chosenMime } : undefined);
                chunks = [];
                recorder.ondataavailable = function (e) { if (e.data && e.data.size) chunks.push(e.data); };
                recorder.onstop = function () {
                    if (mediaStream) { mediaStream.getTracks().forEach(t => t.stop()); mediaStream = null; }
                    clearInterval(timerInterval); clearTimeout(autoStopTimer);
                    var mime = recorder.mimeType || (currentMode === 'video' ? 'video/webm' : 'audio/webm');
                    var ext = mime.includes('mp4') ? (currentMode === 'video' ? 'mp4' : 'm4a') : 'webm';
                    var blob = new Blob(chunks, { type: mime });
                    var name = (currentMode === 'video' ? 'video' : 'voix') + '-' + Date.now() + '.' + ext;
                    recordedFile = new File([blob], name, { type: mime });
                    // Affiche la lecture pour vérifier
                    previewEl.srcObject = null;
                    previewEl.src = URL.createObjectURL(blob);
                    previewEl.hidden = currentMode !== 'video';
                    previewEl.controls = true;
                    previewEl.muted = false;
                    setStatus('Enregistrement terminé — écoutez ou joignez au message');
                    stopBtn.hidden = true; useBtn.hidden = false; resetBtn.hidden = false;
                };
                recorder.start(200);
                startAt = performance.now();
                timerInterval = setInterval(tick, 250);
                autoStopTimer = setTimeout(function () {
                    if (recorder.state !== 'inactive') recorder.stop();
                }, MAX_SECONDS * 1000);
                startBtn.hidden = true; stopBtn.hidden = false;
                setStatus('Enregistrement en cours…');
            } catch (err) {
                console.error(err);
                alert('Impossible d\'accéder au ' + (currentMode === 'video' ? 'micro/caméra' : 'micro') + ' : ' + err.message);
                reset();
            }
        }
        function stopRecording() {
            if (recorder && recorder.state !== 'inactive') recorder.stop();
        }
        function useRecording() {
            if (!recordedFile) return;
            stagedFiles.items.add(recordedFile);
            filesInput.files = stagedFiles.files;
            renderPreview();
            modal.hide();
            reset();
        }

        voiceBtn.addEventListener('click', function () { openRecorder('audio'); });
        videoBtn?.addEventListener('click', function () { openRecorder('video'); });
        startBtn.addEventListener('click', startRecording);
        stopBtn.addEventListener('click', stopRecording);
        useBtn.addEventListener('click', useRecording);
        resetBtn.addEventListener('click', function () { reset(); });
        modalEl.addEventListener('hidden.bs.modal', reset);
    })();

    // ── Autocomplete `@_module&@_section&@_titre` ────────────────
    (function () {
        var mentionUrl = composer.dataset.mentionUrl;
        var popup = document.getElementById('gcMentionPopup');
        var msgInput = document.getElementById('gcMsgInput');
        if (!popup || !msgInput || !mentionUrl) return;

        var currentItems = [];
        var activeIdx = 0;
        var currentContext = null; // { step, module, section, queryStart, queryEnd, prefix }

        function hide() {
            popup.hidden = true;
            popup.innerHTML = '';
            currentContext = null;
        }
        function render(items, headerText) {
            currentItems = items;
            activeIdx = 0;
            popup.innerHTML = '';
            if (headerText) {
                var h = document.createElement('div'); h.className = 'gc-mention-header'; h.textContent = headerText;
                popup.appendChild(h);
            }
            if (!items.length) {
                var e = document.createElement('div'); e.className = 'gc-mention-empty';
                e.textContent = 'Aucun élément.'; popup.appendChild(e);
            } else {
                items.forEach(function (it, idx) {
                    var el = document.createElement('div'); el.className = 'gc-mention-item'; el.dataset.idx = idx;
                    if (it.key) {
                        var k = document.createElement('span'); k.className = 'gc-mention-key'; k.textContent = it.key;
                        el.appendChild(k);
                        el.appendChild(document.createTextNode(it.label));
                    } else {
                        el.textContent = it.title || it.label || '';
                        if (it.excerpt) { var ex = document.createElement('span'); ex.className = 'gc-mention-excerpt'; ex.textContent = it.excerpt; el.appendChild(ex); }
                    }
                    if (idx === 0) el.classList.add('is-active');
                    el.addEventListener('mousedown', function (ev) { ev.preventDefault(); pick(idx); });
                    popup.appendChild(el);
                });
            }
            popup.hidden = false;
        }
        function highlight(newIdx) {
            var items = popup.querySelectorAll('.gc-mention-item');
            if (!items.length) return;
            activeIdx = (newIdx + items.length) % items.length;
            items.forEach(function (el, i) { el.classList.toggle('is-active', i === activeIdx); });
            items[activeIdx]?.scrollIntoView({block: 'nearest'});
        }
        function pick(idx) {
            var it = currentItems[idx]; if (!it) return;
            var val = msgInput.value;
            var caret = msgInput.selectionStart;
            var before = val.substring(0, currentContext.queryStart);
            var after = val.substring(caret);
            var inserted;
            if (currentContext.step === 'modules') {
                inserted = it.key + '&@_';
            } else if (currentContext.step === 'sections') {
                inserted = it.key + '&@_';
            } else {
                inserted = it.title;
            }
            msgInput.value = before + inserted + after;
            var newCaret = before.length + inserted.length;
            msgInput.focus();
            msgInput.setSelectionRange(newCaret, newCaret);
            hide();
            // Après insertion, re-évalue le contexte pour éventuellement enchaîner
            handleInput();
        }
        function fetchAndRender(url, header) {
            fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) { render(data.items || [], header); })
                .catch(function () { hide(); });
        }

        function handleInput() {
            var val = msgInput.value;
            var caret = msgInput.selectionStart;
            var textBeforeCaret = val.substring(0, caret);

            // Détecte le dernier `@_` NON précédé d'un modèle terminé et suit sa progression
            // Chaîne cherchée : @_MOD&@_SEC&@_TITRE — les 3 étapes s'enchaînent
            // On repère la dernière séquence commencée par `@_`
            var m;
            // Step 3 : `@_mod&@_sec&@_(query)`  — content search
            m = textBeforeCaret.match(/@_([a-z0-9_-]+)&@_([a-z0-9_-]+)&@_([^\s]*)$/i);
            if (m) {
                currentContext = { step: 'contents', module: m[1].toLowerCase(), section: m[2].toLowerCase(), queryStart: caret - m[3].length };
                fetchAndRender(mentionUrl + '?step=contents&module=' + encodeURIComponent(m[1]) + '&section=' + encodeURIComponent(m[2]) + '&q=' + encodeURIComponent(m[3]), 'Contenus : ' + m[1] + ' → ' + m[2]);
                return;
            }
            // Step 2 : `@_mod&@_(sec)` — sections list
            m = textBeforeCaret.match(/@_([a-z0-9_-]+)&@_([a-z0-9_-]*)$/i);
            if (m) {
                currentContext = { step: 'sections', module: m[1].toLowerCase(), queryStart: caret - m[2].length };
                fetchAndRender(mentionUrl + '?step=sections&module=' + encodeURIComponent(m[1]), 'Sections : ' + m[1]);
                return;
            }
            // Step 1 : `@_(mod)` — modules list
            m = textBeforeCaret.match(/@_([a-z0-9_-]*)$/i);
            if (m) {
                currentContext = { step: 'modules', queryStart: caret - m[1].length };
                fetchAndRender(mentionUrl + '?step=modules', 'Modules');
                return;
            }
            hide();
        }

        msgInput.addEventListener('input', handleInput);
        msgInput.addEventListener('keydown', function (e) {
            if (popup.hidden) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); highlight(activeIdx + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(activeIdx - 1); }
            else if (e.key === 'Enter' || e.key === 'Tab') { e.preventDefault(); pick(activeIdx); }
            else if (e.key === 'Escape') { hide(); }
        });
        msgInput.addEventListener('blur', function () { setTimeout(hide, 150); });
    })();

    // ── Envoi (FormData multipart) ───────────────────────────────
    composer.addEventListener('submit', function (e) {
        e.preventDefault();
        var value = input.value.trim();
        if (!value && stagedFiles.files.length === 0 && !stagedShared) return;
        var fd = new FormData(composer);
        var btnEnvoi = composer.querySelector('.gc-composer-send');
        btnEnvoi.disabled = true;
        input.value = ''; ajuster();
        fetch(postUrl, {
            method: 'POST', body: fd, credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data.ok) {
                appendMessage(data.message);
                lastId = Math.max(lastId, data.message.id);
                messagesEl.scrollTop = messagesEl.scrollHeight;
                stagedFiles = new DataTransfer(); filesInput.value = '';
                stagedShared = null;
                document.getElementById('gcSharedType').value = ''; document.getElementById('gcSharedId').value = '';
                renderPreview();
            } else if (data && !data.ok) {
                input.value = fd.get('contenu') || '';
                alert("Le message n'a pas pu être envoyé.");
            }
        }).catch(function () { input.value = fd.get('contenu') || ''; alert("Le message n'a pas pu être envoyé."); })
          .finally(function () { btnEnvoi.disabled = false; input.focus(); });
    });

    // Polling nouveaux messages toutes les 5s
    var fetchUrl = "{{ route('social.groups.fetchMessages', $group) }}";
    setInterval(function () {
        fetch(fetchUrl + '?after=' + lastId, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (!data.messages || !data.messages.length) return;
            var wasAtBottom = messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 100;
            data.messages.forEach(function (m) {
                if (m.id > lastId) {
                    appendMessage(m);
                    lastId = m.id;
                }
            });
            if (wasAtBottom) messagesEl.scrollTop = messagesEl.scrollHeight;
        }).catch(function () {});
    }, 5000);

    // Tom Select sur modale invitation
    var inv = document.querySelector('.gc-invite-select');
    if (inv) new TomSelect(inv, { plugins:['remove_button'], maxOptions:500, dropdownParent: 'body' });
});
</script>

@endpush
