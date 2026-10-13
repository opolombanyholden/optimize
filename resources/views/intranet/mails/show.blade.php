@extends('layouts.app')

@section('title', $mail['sujet'].' — Mails')

@php
    $fmt = fn(array $l) => collect($l)->map(fn($a) => $a['nom'] ? $a['nom'].' <'.$a['email'].'>' : $a['email'])->implode(', ');
    $exp = $mail['de'][0] ?? ['nom' => '', 'email' => ''];
    $params = ['compte' => $compte->id, 'uid' => $mail['uid'], 'dossier' => $dossier];
    $taille = fn(int $o) => $o >= 1048576 ? number_format($o / 1048576, 1, ',', ' ').' Mo' : max(1, (int) round($o / 1024)).' Ko';
@endphp

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $dossier]) }}">Mails professionnels</a></li>
    <li class="breadcrumb-item active text-truncate" style="max-width:300px;">{{ $mail['sujet'] }}</li>
</ol>
@endsection

@include('intranet.mails._styles')

@push('styles')
<style>
.wm-read-head { padding:1.25rem 1.5rem .75rem; border-bottom:1px solid #F1F5F9; }
.wm-read-subject { font-size:1.2rem; font-weight:700; color:#0F172A; margin:0 0 .85rem; word-break:break-word; }
.wm-read-meta { display:flex; gap:.75rem; align-items:flex-start; }
.wm-read-av { width:40px; height:40px; border-radius:50%; background:#4F46E5; color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.wm-read-from { font-weight:600; color:#1E293B; font-size:.9rem; }
.wm-read-from span { font-weight:400; color:#64748B; font-size:.8rem; }
.wm-read-to { font-size:.78rem; color:#64748B; word-break:break-word; }
.wm-read-body { padding:1rem 1.5rem; }
.wm-frame { width:100%; border:0; min-height:200px; display:block; }
.wm-pj { display:flex; flex-wrap:wrap; gap:.5rem; padding:0 1.5rem 1.25rem; }
.wm-classe-panel { margin:0 1.5rem 1rem; border:1px solid #E2E8F0; border-radius:10px; padding:.75rem .9rem; }
.wm-badge { display:inline-flex; align-items:center; border-radius:999px; padding:.15rem .6rem; font-size:.74rem; font-weight:600; }
.wm-lien-chip { display:inline-flex; align-items:center; background:#EEF2FF; color:#3730A3; border-radius:999px; padding:.2rem .65rem; font-size:.78rem; text-decoration:none; max-width:100%; }
.wm-lien-chip:hover { background:#E0E7FF; color:#1E1B4B; }
.wm-note { background:#FFFBEB; border-left:3px solid #F59E0B; padding:.4rem .7rem; font-size:.82rem; color:#78350F; white-space:pre-line; border-radius:4px; }
.wm-crm-panel { margin:0 1.5rem 1.25rem; border:1px solid #E0E7FF; background:#F8FAFF; border-radius:10px; padding:.75rem .9rem; }
.wm-crm-fiches { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:.5rem; }
.wm-crm-fiche { display:flex; gap:.55rem; align-items:flex-start; background:#fff; border:1px solid #E2E8F0; border-radius:8px; padding:.5rem .6rem; font-size:.82rem; min-width:0; cursor:pointer; }
.wm-crm-fiche.deja { opacity:.75; }
.wm-crm-fiche .type { font-size:.62rem; text-transform:uppercase; letter-spacing:.04em; color:#6366F1; font-weight:700; }
.wm-pj a { display:flex; align-items:center; gap:.5rem; border:1px solid #E2E8F0; border-radius:8px; padding:.45rem .7rem; text-decoration:none; color:#1E293B; font-size:.8rem; max-width:280px; }
.wm-pj a:hover { background:#F8FAFC; border-color:#CBD5E1; }
.wm-pj .nom { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
</style>
@endpush

@section('content')
<div class="wm-wrap">
    @include('intranet.mails._side')

    <section class="wm-main">
        <div class="wm-toolbar">
            <a href="{{ route('intranet.mails.index', ['compte' => $compte->id, 'dossier' => $dossier]) }}" class="btn btn-sm btn-light" title="Retour"><i class="fas fa-arrow-left"></i></a>
            <div class="btn-group">
                <a href="{{ route('intranet.mails.create', ['compte' => $compte->id, 'mode' => 'repondre', 'uid' => $mail['uid'], 'dossier' => $dossier]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-reply me-1"></i>Répondre</a>
                <a href="{{ route('intranet.mails.create', ['compte' => $compte->id, 'mode' => 'repondre-tous', 'uid' => $mail['uid'], 'dossier' => $dossier]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-reply-all me-1"></i>Répondre à tous</a>
                <a href="{{ route('intranet.mails.create', ['compte' => $compte->id, 'mode' => 'transferer', 'uid' => $mail['uid'], 'dossier' => $dossier]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-share me-1"></i>Transférer</a>
            </div>
            <div class="ms-auto d-flex gap-1">
                <form action="{{ route('intranet.mails.marquer', $params) }}" method="POST">@csrf
                    <input type="hidden" name="dossier" value="{{ $dossier }}">
                    <input type="hidden" name="suivi" value="{{ $mail['suivi'] ? 0 : 1 }}">
                    <button class="btn btn-sm btn-outline-secondary" title="{{ $mail['suivi'] ? 'Retirer le suivi' : 'Marquer pour suivi' }}"><i class="fas fa-star" style="{{ $mail['suivi'] ? 'color:#F59E0B;' : '' }}"></i></button>
                </form>
                <form action="{{ route('intranet.mails.marquer', $params) }}" method="POST">@csrf
                    <input type="hidden" name="dossier" value="{{ $dossier }}">
                    <input type="hidden" name="lu" value="0">
                    <input type="hidden" name="retour_liste" value="1">
                    <button class="btn btn-sm btn-outline-secondary" title="Marquer comme non lu"><i class="fas fa-envelope"></i></button>
                </form>
                <form action="{{ route('intranet.mails.destroy', ['compte' => $compte->id, 'uid' => $mail['uid']]) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?');">@csrf @method('DELETE')
                    <input type="hidden" name="dossier" value="{{ $dossier }}">
                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>

        <div class="wm-read-head">
            <h1 class="wm-read-subject">{{ $mail['sujet'] }}</h1>
            <div class="wm-read-meta">
                <div class="wm-read-av">{{ strtoupper(mb_substr($exp['nom'] ?: $exp['email'], 0, 1)) }}</div>
                <div class="flex-grow-1 min-w-0">
                    <div class="wm-read-from">{{ $exp['nom'] ?: $exp['email'] }} @if($exp['nom'])<span>&lt;{{ $exp['email'] }}&gt;</span>@endif</div>
                    <div class="wm-read-to">À : {{ $fmt($mail['a']) ?: '—' }}</div>
                    @if($mail['cc'])<div class="wm-read-to">Cc : {{ $fmt($mail['cc']) }}</div>@endif
                </div>
                <div class="text-muted text-nowrap" style="font-size:.78rem;">{{ $mail['date']?->translatedFormat('j M Y, H:i') }}</div>
            </div>
        </div>

        <div class="wm-read-body">
            <div id="wmImagesBar" class="alert alert-light border d-flex align-items-center gap-2 py-2 px-3 mb-3 {{ $mail['html'] && preg_match('/<img[^>]+src=["\']?https?:/i', $mail['html']) ? '' : 'd-none' }}" style="font-size:.8rem;">
                <i class="fas fa-image text-muted"></i> Les images distantes sont masquées pour protéger votre vie privée.
                <button type="button" class="btn btn-sm btn-link p-0 ms-auto" id="wmShowImages">Afficher les images</button>
            </div>
            {{-- Corps servi par une route dédiée : iframe sandboxée, sans script, CSP stricte --}}
            <iframe class="wm-frame" id="wmFrame" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin"
                    src="{{ route('intranet.mails.corps', $params) }}" title="Contenu du message"></iframe>
        </div>

        {{-- ══ Classement : liens vers les modules, priorité, suivi ══ --}}
        @php $P = \App\Models\Intranet\MailSuivi::PRIORITES; @endphp
        <div class="wm-classe-panel">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <i class="fas fa-folder-tree" style="color:#4F46E5;"></i><strong style="font-size:.88rem;">Classement</strong>
                @if($suivi)
                    @php $pr = $P[$suivi->priorite]; @endphp
                    <span class="wm-badge" style="background:{{ $pr['fond'] }}; color:{{ $pr['couleur'] }};"><i class="fas {{ $pr['icone'] }} me-1"></i>{{ $pr['libelle'] }}</span>
                    @if($suivi->statut)<span class="wm-badge" style="background:#F1F5F9; color:#334155;">{{ \App\Models\Intranet\MailSuivi::STATUTS[$suivi->statut] }}</span>@endif
                    @if($suivi->categorie)<span class="wm-badge" style="background:#F5F3FF; color:#6D28D9;"><i class="fas fa-tag me-1"></i>{{ $suivi->categorie }}</span>@endif
                    @if($suivi->echeance)<span class="wm-badge" style="{{ $suivi->enRetard() ? 'background:#FEE2E2;color:#B91C1C;' : 'background:#ECFDF5;color:#047857;' }}"><i class="fas fa-calendar-day me-1"></i>{{ $suivi->enRetard() ? 'En retard · ' : 'Avant le ' }}{{ $suivi->echeance->translatedFormat('j M Y') }}</span>@endif
                    @if($suivi->confidentiel)<span class="wm-badge" style="background:#F1F5F9;color:#475569;"><i class="fas fa-user-lock me-1"></i>Confidentiel</span>@endif
                @else
                    <span class="text-muted" style="font-size:.78rem;">Non classé — liez ce mail à un projet, une tâche, un courrier, une opportunité, une dépense…</span>
                @endif
                <button class="btn btn-sm btn-outline-primary ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#wmClasserForm" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                    <i class="fas fa-pen me-1"></i>{{ $suivi ? 'Modifier' : 'Classer / lier' }}
                </button>
            </div>
            @if($classement['liens'])
                <div class="d-flex flex-wrap gap-1 mt-2">
                    @foreach($classement['liens'] as $l)
                        <a href="{{ $l['url'] ?? '#' }}" class="wm-lien-chip" title="{{ $l['label'] }}"><i class="fas {{ $l['icon'] }} me-1"></i><span class="opacity-75 me-1">{{ $l['label'] }} ·</span>{{ $l['title'] }}</a>
                    @endforeach
                </div>
            @endif
            @if($suivi?->note)
                <div class="wm-note mt-2"><i class="fas fa-note-sticky me-1"></i>{{ $suivi->note }}</div>
            @endif

            <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="wmClasserForm">
                <form action="{{ route('intranet.mails.classer', ['compte' => $compte->id, 'uid' => $mail['uid']]) }}" method="POST" class="mt-3 pt-3 border-top">
                    @csrf
                    <input type="hidden" name="dossier" value="{{ $dossier }}">
                    @if($errors->any())<div class="alert alert-danger py-2" style="font-size:.82rem;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
                    @include('intranet.mails._classement')
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#wmClasserForm">Annuler</button>
                        <button class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Enregistrer le classement</button>
                    </div>
                </form>
            </div>
        </div>

        @if($crm)
        @php
            $noms = collect([$mail['de'], $mail['repondre_a'], $mail['a'], $mail['cc']])->flatten(1)->pluck('nom', 'email');
            $deja = array_flip($crm['deja']);
            $premierContact = $crm['contacts']->first();
            $fiches = collect()
                ->concat($crm['contacts']->map(fn($c) => ['cle' => 'contact:'.$c->id, 'type' => 'Contact', 'label' => trim($c->prenoms.' '.$c->nom), 'info' => $c->organisation?->nom ?: $c->email, 'url' => route('intranet.contacts.show', $c), 'droit' => auth()->user()->can('update:contact')]))
                ->concat($crm['organisations']->map(fn($o) => ['cle' => 'organisation:'.$o->id, 'type' => 'Organisation', 'label' => $o->nom, 'info' => $o->email, 'url' => route('intranet.organisations.show', $o), 'droit' => auth()->user()->can('update:organisation_crm')]))
                ->concat($crm['opportunites']->map(fn($x) => ['cle' => 'opportunite:'.$x->id, 'type' => 'Opportunité', 'label' => $x->titre, 'info' => $x->etape?->nom, 'url' => route('intranet.opportunites.show', $x), 'droit' => auth()->user()->can('update:opportunite')]));
        @endphp
        <div class="wm-crm-panel">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fas fa-address-book" style="color:#4F46E5;"></i><strong style="font-size:.88rem;">CRM</strong>
                @if($fiches->isEmpty())<span class="text-muted" style="font-size:.78rem;">Aucun correspondant connu dans le CRM.</span>@endif
            </div>
            @if($fiches->isNotEmpty())
            <form action="{{ route('intranet.mails.crm', ['compte' => $compte->id, 'uid' => $mail['uid']]) }}" method="POST">@csrf
                <input type="hidden" name="dossier" value="{{ $dossier }}">
                <div class="wm-crm-fiches">
                    @foreach($fiches as $f)
                        @php $dejaLa = isset($deja[$f['cle']]); @endphp
                        <label class="wm-crm-fiche {{ $dejaLa ? 'deja' : '' }}">
                            <input type="checkbox" class="form-check-input" name="cibles[]" value="{{ $f['cle'] }}" @checked(!$dejaLa && str_starts_with($f['cle'], 'contact:')) @disabled($dejaLa || !$f['droit'])>
                            <span class="min-w-0">
                                <span class="type">{{ $f['type'] }}</span>
                                <a href="{{ $f['url'] }}" class="d-block text-truncate fw-semibold text-decoration-none" style="color:#1E293B;">{{ $f['label'] }}</a>
                                <span class="d-block text-muted text-truncate" style="font-size:.72rem;">
                                    @if($dejaLa)
                                        <i class="fas fa-check text-success me-1"></i>Déjà dans l'historique
                                    @elseif(!$f['droit'])
                                        Lecture seule
                                    @else
                                        {{ $f['info'] }}
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @if($fiches->contains(fn($f) => $f['droit'] && !isset($deja[$f['cle']])))
                    <button class="btn btn-sm btn-primary mt-2"><i class="fas fa-clock-rotate-left me-1"></i>Ajouter à l'historique CRM</button>
                @endif
            </form>
            @endif

            <div class="d-flex flex-wrap gap-2 mt-2">
                @can('create:contact')
                    @foreach($crm['inconnus']->take(3) as $email)
                        @php [$prenoms, $nom] = str_contains(trim($noms[$email] ?? ''), ' ') ? explode(' ', trim($noms[$email]), 2) : ['', trim($noms[$email] ?? '') ?: \Illuminate\Support\Str::before($email, '@')]; @endphp
                        <a href="{{ route('intranet.contacts.create', ['email' => $email, 'prenoms' => $prenoms, 'nom' => $nom]) }}" class="btn btn-sm btn-outline-secondary" title="Créer une fiche contact"><i class="fas fa-user-plus me-1"></i>{{ $noms[$email] ?: $email }}</a>
                    @endforeach
                @endcan
                @can('create:opportunite')
                    <a href="{{ route('intranet.opportunites.create', array_filter(['titre' => $mail['sujet'], 'contact_id' => $premierContact?->id, 'organisation_id' => $premierContact?->organisation_id ?? $crm['organisations']->first()?->id])) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-fire-flame-curved me-1"></i>Créer une opportunité</a>
                @endcan
            </div>
        </div>
        @endif

        @if($mail['pieces'])
        <div class="wm-pj">
            @foreach($mail['pieces'] as $p)
                <a href="{{ route('intranet.mails.piece', ['compte' => $compte->id, 'uid' => $mail['uid'], 'partie' => $p['partie'], 'dossier' => $dossier]) }}" title="Télécharger {{ $p['nom'] }}">
                    <i class="fas fa-paperclip text-muted"></i><span class="nom">{{ $p['nom'] }}</span><span class="text-muted" style="font-size:.7rem;">{{ $taille($p['taille']) }}</span>
                </a>
            @endforeach
        </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const frame = document.getElementById('wmFrame');
    // Ajuste la hauteur de l'iframe à son contenu (même origine, pas de script dans l'iframe)
    function ajuster() {
        try { frame.style.height = (frame.contentDocument.documentElement.scrollHeight + 16) + 'px'; } catch (e) {}
    }
    frame.addEventListener('load', function () { ajuster(); setTimeout(ajuster, 400); });
    document.getElementById('wmShowImages')?.addEventListener('click', function () {
        const url = new URL(frame.src); url.searchParams.set('images', '1'); frame.src = url.toString();
        document.getElementById('wmImagesBar').classList.add('d-none');
    });
})();
</script>
@endpush
