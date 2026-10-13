@extends('layouts.app')

@section('title', 'Messagerie instantanée')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item active">Messagerie instantanée</li>
</ol>
@endsection

@php
    $me = auth()->user();
    $autre = $active?->autre($me);
    $initiales = fn($u) => strtoupper(mb_substr($u?->prenoms ?: $u?->name ?? '?', 0, 1).mb_substr($u?->name ?? '', 0, 1));
    $couleurs = ['#7C3AED','#059669','#D97706','#DC2626','#0891B2','#0D9488','#4F46E5','#BE185D'];
    $couleur = fn($u) => $couleurs[($u?->id ?? 0) % count($couleurs)];
@endphp

@push('styles')
<style>
.im-wrap { display:grid; grid-template-columns:320px 1fr; height:calc(100vh - 150px); min-height:480px; background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); overflow:hidden; }
.im-side { border-right:1px solid #E2E8F0; display:flex; flex-direction:column; min-height:0; }
.im-side-head { padding:1rem; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
.im-side-head h1 { font-size:1.05rem; font-weight:700; margin:0; color:#1E293B; }
.im-search { padding:.6rem 1rem; border-bottom:1px solid #F1F5F9; }
.im-search input { font-size:.82rem; }
.im-list { flex:1; overflow-y:auto; list-style:none; margin:0; padding:0; }
.im-item { display:flex; gap:.65rem; align-items:center; padding:.7rem 1rem; text-decoration:none; color:inherit; border-left:3px solid transparent; }
.im-item:hover { background:#F8FAFC; }
.im-item.active { background:#EEF2FF; border-left-color:#4F46E5; }
.im-av { position:relative; width:40px; height:40px; border-radius:50%; color:#fff; font-weight:700; font-size:.8rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.im-av .dot { position:absolute; bottom:0; right:0; width:11px; height:11px; border-radius:50%; border:2px solid #fff; background:#CBD5E1; }
.im-av .dot.on { background:#22C55E; }
.im-item-body { flex:1; min-width:0; }
.im-item-top { display:flex; justify-content:space-between; gap:.5rem; }
.im-item-name { font-weight:600; font-size:.85rem; color:#1E293B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.im-item-time { font-size:.68rem; color:#94A3B8; flex-shrink:0; }
.im-item-last { font-size:.76rem; color:#64748B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.im-item.unread .im-item-last { color:#1E293B; font-weight:600; }
.im-badge { background:#4F46E5; color:#fff; border-radius:999px; font-size:.65rem; font-weight:700; padding:.1rem .45rem; }
.im-section { font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; color:#94A3B8; font-weight:700; padding:.75rem 1rem .35rem; }

.im-main { display:flex; flex-direction:column; min-height:0; }
.im-head { padding:.75rem 1.25rem; border-bottom:1px solid #F1F5F9; display:flex; align-items:center; gap:.75rem; }
.im-head-name { font-weight:700; color:#1E293B; font-size:.95rem; }
.im-head-status { font-size:.72rem; color:#94A3B8; }
.im-head-status.on { color:#16A34A; }
.im-thread { flex:1; overflow-y:auto; padding:1.25rem; display:flex; flex-direction:column; gap:.35rem; background:#F8FAFC; }
.im-day { align-self:center; font-size:.68rem; color:#64748B; background:#E2E8F0; border-radius:999px; padding:.15rem .7rem; margin:.6rem 0; }
.im-msg { max-width:70%; display:flex; flex-direction:column; }
.im-msg.mine { align-self:flex-end; align-items:flex-end; }
.im-msg.theirs { align-self:flex-start; align-items:flex-start; }
.im-msg-text { padding:.5rem .8rem; border-radius:14px; font-size:.86rem; line-height:1.45; white-space:pre-wrap; word-break:break-word; }
.im-msg.mine .im-msg-text { background:linear-gradient(135deg,#7C3AED,#4F46E5); color:#fff; border-bottom-right-radius:4px; }
.im-msg.theirs .im-msg-text { background:#fff; color:#1E293B; border:1px solid #E2E8F0; border-bottom-left-radius:4px; }
.im-msg-meta { font-size:.62rem; color:#94A3B8; margin-top:.15rem; }
.im-composer { border-top:1px solid #F1F5F9; padding:.75rem 1rem; display:flex; gap:.5rem; align-items:flex-end; }
.im-composer textarea { resize:none; max-height:140px; font-size:.86rem; border-radius:10px; }
.im-send { width:42px; height:42px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.im-empty { margin:auto; text-align:center; color:#94A3B8; }
.im-empty i { font-size:2.5rem; margin-bottom:.75rem; color:#CBD5E1; }
.im-back { display:none; }

@media (max-width: 767.98px) {
    .im-wrap { grid-template-columns:1fr; height:calc(100vh - 130px); }
    .im-wrap.has-active .im-side { display:none; }
    .im-wrap:not(.has-active) .im-main { display:none; }
    .im-back { display:inline-flex; }
    .im-msg { max-width:85%; }
}
</style>
@endpush

@section('content')
<div class="im-wrap {{ $active ? 'has-active' : '' }}">

    {{-- ══ Conversations ══ --}}
    <aside class="im-side">
        <div class="im-side-head">
            <h1><i class="fas fa-message me-2" style="color:#4F46E5;"></i>Messagerie</h1>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#imNewModal" title="Nouvelle conversation"><i class="fas fa-pen-to-square"></i></button>
        </div>
        <div class="im-search">
            <input type="search" class="form-control form-control-sm" id="imFilter" placeholder="Rechercher une conversation…">
        </div>
        <ul class="im-list" id="imList">
            @forelse($conversations as $c)
                @php $u = $c->interlocuteur; @endphp
                <li>
                    <a href="{{ route('intranet.messagerie.show', $c) }}" class="im-item {{ $active?->id === $c->id ? 'active' : '' }} {{ $c->non_lus ? 'unread' : '' }}" data-nom="{{ mb_strtolower(trim($u?->prenoms.' '.$u?->name)) }}" data-conversation="{{ $c->id }}">
                        <div class="im-av" style="background:{{ $couleur($u) }};">{{ $initiales($u) }}<span class="dot {{ $u?->estEnLigne() ? 'on' : '' }}"></span></div>
                        <div class="im-item-body">
                            <div class="im-item-top">
                                <span class="im-item-name">{{ trim($u?->prenoms.' '.$u?->name) }}</span>
                                <span class="im-item-time">{{ $c->last_message_at?->isToday() ? $c->last_message_at->format('H:i') : $c->last_message_at?->format('d/m') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="im-item-last flex-grow-1">@if($c->dernierMessage?->sender_id === $me->id)Vous : @endif{{ $c->dernierMessage?->contenu }}</span>
                                @if($c->non_lus)<span class="im-badge">{{ $c->non_lus }}</span>@endif
                            </div>
                        </div>
                    </a>
                </li>
            @empty
                <li class="im-empty py-5 px-3">
                    <i class="fas fa-comments d-block"></i>
                    <p class="mb-3" style="font-size:.85rem;">Aucune conversation pour l'instant.</p>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#imNewModal">Démarrer une conversation</button>
                </li>
            @endforelse
        </ul>
    </aside>

    {{-- ══ Fil de discussion ══ --}}
    <section class="im-main">
        @if($active)
            <div class="im-head">
                <a href="{{ route('intranet.messagerie.index') }}" class="btn btn-sm btn-light im-back" title="Retour"><i class="fas fa-arrow-left"></i></a>
                <div class="im-av" style="background:{{ $couleur($autre) }};">{{ $initiales($autre) }}<span class="dot {{ $autre?->estEnLigne() ? 'on' : '' }}" id="imHeadDot"></span></div>
                <div>
                    <div class="im-head-name">{{ trim($autre?->prenoms.' '.$autre?->name) }}</div>
                    <div class="im-head-status {{ $autre?->estEnLigne() ? 'on' : '' }}" id="imHeadStatus">
                        {{ $autre?->estEnLigne() ? 'En ligne' : ($autre?->last_seen_at ? 'Vu '.$autre->last_seen_at->diffForHumans() : 'Hors ligne') }}
                    </div>
                </div>
                @if($autre)
                <a href="{{ route('intranet.annuaire.collaborateurs.show', $autre) }}" class="btn btn-sm btn-outline-secondary ms-auto" title="Voir la fiche"><i class="fas fa-id-card"></i></a>
                @endif
            </div>

            <div class="im-thread" id="imThread">
                @forelse($messages as $m)
                    @php $jour = $m->created_at->toDateString(); @endphp
                    @if($loop->first || $jour !== $messages[$loop->index - 1]->created_at->toDateString())
                        <div class="im-day">{{ $m->created_at->isToday() ? "Aujourd'hui" : ($m->created_at->isYesterday() ? 'Hier' : $m->created_at->translatedFormat('l j F')) }}</div>
                    @endif
                    <div class="im-msg {{ $m->sender_id === $me->id ? 'mine' : 'theirs' }}" data-id="{{ $m->id }}">
                        <div class="im-msg-text">{{ $m->contenu }}</div>
                        <div class="im-msg-meta">{{ $m->created_at->format('H:i') }}{{ $m->sender_id === $me->id && $m->lu_at ? ' · Lu' : '' }}</div>
                    </div>
                @empty
                    <div class="im-empty" id="imEmpty"><i class="far fa-comment-dots d-block"></i><p style="font-size:.85rem;">Dites bonjour à {{ $autre?->prenoms ?: $autre?->name }} 👋</p></div>
                @endforelse
            </div>

            <form class="im-composer" id="imForm" autocomplete="off">
                <textarea class="form-control" id="imInput" rows="1" maxlength="4000" placeholder="Écrire un message…" title="Entrée pour envoyer, Maj+Entrée pour aller à la ligne" required></textarea>
                <button class="btn btn-primary im-send" type="submit" title="Envoyer"><i class="fas fa-paper-plane"></i></button>
            </form>
        @else
            <div class="im-empty">
                <i class="fas fa-comments d-block"></i>
                <p class="mb-3">Sélectionnez une conversation ou démarrez-en une nouvelle.</p>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#imNewModal"><i class="fas fa-pen-to-square me-1"></i>Nouvelle conversation</button>
            </div>
        @endif
    </section>
</div>

{{-- ══ Modale nouvelle conversation ══ --}}
<div class="modal fade" id="imNewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen-to-square me-2"></i>Nouvelle conversation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom"><input type="search" class="form-control" id="imNewFilter" placeholder="Rechercher un collaborateur…"></div>
                <ul class="im-list" id="imNewList" style="max-height:420px;">
                    @foreach($collaborateurs as $u)
                    <li data-nom="{{ mb_strtolower(trim($u->prenoms.' '.$u->name.' '.$u->poste)) }}">
                        <form action="{{ route('intranet.messagerie.demarrer', $u) }}" method="POST">@csrf
                            <button class="im-item w-100 border-0 bg-transparent text-start">
                                <div class="im-av" style="background:{{ $couleur($u) }};">{{ $initiales($u) }}</div>
                                <div class="im-item-body">
                                    <div class="im-item-name">{{ trim($u->prenoms.' '.$u->name) }}</div>
                                    @if($u->poste)<div class="im-item-last">{{ $u->poste }}</div>@endif
                                </div>
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Filtres de liste (conversations + nouvelle conversation)
    function filtre(input, list) {
        if (!input || !list) return;
        input.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            list.querySelectorAll('[data-nom]').forEach(el => {
                (el.closest('li') || el).style.display = !q || el.dataset.nom.includes(q) ? '' : 'none';
            });
        });
    }
    filtre(document.getElementById('imFilter'), document.getElementById('imList'));
    filtre(document.getElementById('imNewFilter'), document.getElementById('imNewList'));

    @if($active)
    const URL_MESSAGES = @json(route('intranet.messagerie.messages', $active));
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token());
    const thread = document.getElementById('imThread');
    const form = document.getElementById('imForm');
    const input = document.getElementById('imInput');
    // Messages initiaux rendus côté serveur ; le polling reprend après le dernier
    let dernierId = {{ (int) $messages->last()?->id }}, derniereDate = @json($messages->last()?->created_at->toDateString()), luJusqua = 0;

    function jourLabel(iso) {
        const d = new Date(iso + 'T00:00:00'), auj = new Date(); auj.setHours(0,0,0,0);
        const diff = Math.round((auj - d) / 86400000);
        if (diff === 0) return "Aujourd'hui";
        if (diff === 1) return 'Hier';
        return d.toLocaleDateString('fr-FR', { weekday:'long', day:'numeric', month:'long' });
    }

    function ajouter(m) {
        if (m.id <= dernierId) return;
        document.getElementById('imEmpty')?.remove();
        if (m.date !== derniereDate) {
            const sep = document.createElement('div');
            sep.className = 'im-day'; sep.textContent = jourLabel(m.date);
            thread.appendChild(sep); derniereDate = m.date;
        }
        const el = document.createElement('div');
        el.className = 'im-msg ' + (m.mine ? 'mine' : 'theirs');
        el.dataset.id = m.id;
        const txt = document.createElement('div'); txt.className = 'im-msg-text'; txt.textContent = m.contenu;
        const meta = document.createElement('div'); meta.className = 'im-msg-meta'; meta.textContent = m.heure;
        el.append(txt, meta);
        thread.appendChild(el);
        dernierId = m.id;
    }

    function majAccuses() {
        thread.querySelectorAll('.im-msg.mine .im-msg-meta').forEach(meta => {
            const id = +meta.parentElement.dataset.id;
            const base = meta.textContent.replace(/ · Lu$/, '');
            meta.textContent = id <= luJusqua ? base + ' · Lu' : base;
        });
    }

    function charger(initial) {
        const enBas = thread.scrollHeight - thread.scrollTop - thread.clientHeight < 80;
        return fetch(URL_MESSAGES + (dernierId ? '?apres=' + dernierId : ''), { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                data.messages.forEach(ajouter);
                if (data.lu_jusqua !== luJusqua) { luJusqua = data.lu_jusqua; majAccuses(); }
                const dot = document.getElementById('imHeadDot'), st = document.getElementById('imHeadStatus');
                dot?.classList.toggle('on', data.en_ligne);
                if (st && data.en_ligne) { st.textContent = 'En ligne'; st.classList.add('on'); }
                if (initial || enBas || data.messages.some(m => m.mine)) thread.scrollTop = thread.scrollHeight;
            }).catch(() => {});
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const contenu = input.value.trim();
        if (!contenu) return;
        input.value = ''; input.style.height = '';
        fetch(@json(route('intranet.messagerie.envoyer', $active)), {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ contenu })
        }).then(r => r.ok ? r.json() : Promise.reject(r))
          .then(data => { ajouter(data.message); thread.scrollTop = thread.scrollHeight; })
          .catch(() => { input.value = contenu; alert("Le message n'a pas pu être envoyé."); });
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
    });
    input.addEventListener('input', function () { this.style.height = ''; this.style.height = Math.min(this.scrollHeight, 140) + 'px'; });

    thread.scrollTop = thread.scrollHeight;
    charger(true);
    setInterval(() => { if (!document.hidden) charger(false); }, 4000);
    input.focus();
    @endif

    // Rafraîchit la page de liste quand un nouveau message arrive ailleurs
    let totalNonLus = null;
    setInterval(() => {
        if (document.hidden) return;
        fetch(@json(route('intranet.messagerie.non-lus')), { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(d => {
                if (!d) return;
                if (totalNonLus !== null && d.total > totalNonLus && !@json((bool) $active)) location.reload();
                totalNonLus = d.total;
            }).catch(() => {});
    }, 15000);
})();
</script>
@endpush
