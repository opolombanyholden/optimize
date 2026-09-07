@extends('layouts.app')

@section('title', $group->nom.' — Discussion')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css"
      rel="stylesheet"
      integrity="sha384-piG3EtH1fBnPi68q4spy+Qgpb0dHK1D1dwk0GaHwFkvmUxYi526bBlk3xJcjEBsD"
      crossorigin="anonymous">
<style>
.gc-wrapper { display:grid; grid-template-columns:1fr 240px; gap:1rem; max-width:1000px; margin:0 auto; height:calc(100vh - 180px); }
@media(max-width:900px) { .gc-wrapper { grid-template-columns:1fr; height:auto; } .gc-side { display:none; } }
.gc-chat { background:#F8FAFC; display:flex; flex-direction:column; border:1px solid #E2E8F0; overflow:hidden; }
.gc-header { background:#fff; padding:.75rem 1rem; display:flex; align-items:center; gap:.75rem; border-bottom:1px solid #E2E8F0; flex-shrink:0; }
.gc-avatar { width:40px; height:40px; border-radius:50%; object-fit:cover; background:#0A66C2; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; }
.gc-header-name { font-weight:700; color:#0F172A; margin:0; font-size:.95rem; }
.gc-header-meta { font-size:.72rem; color:#94A3B8; }
.gc-actions { margin-left:auto; display:flex; gap:.35rem; }
.gc-actions .btn { padding:.3rem .55rem; font-size:.75rem; }

.gc-messages { flex:1; overflow-y:auto; padding:1rem; display:flex; flex-direction:column; gap:.5rem; background-color:#F8FAFC; background-image:radial-gradient(#E2E8F0 1px, transparent 1px); background-size:20px 20px; }
.gc-msg { display:flex; gap:.5rem; max-width:75%; }
.gc-msg-avatar { width:30px; height:30px; border-radius:50%; background:#0A66C2; color:#fff; font-size:.7rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.gc-msg-body { background:#fff; padding:.55rem .85rem; border:1px solid #E0DFDC; }
.gc-msg-sender { font-size:.7rem; color:#0A66C2; font-weight:700; margin-bottom:.15rem; }
.gc-msg-text { font-size:.85rem; color:#334155; white-space:pre-wrap; word-break:break-word; }
.gc-msg-date { font-size:.62rem; color:#94A3B8; margin-top:.2rem; text-align:right; }
.gc-msg.is-mine { align-self:flex-end; }
.gc-msg.is-mine .gc-msg-avatar { display:none; }
.gc-msg.is-mine .gc-msg-body { background:#DCFCE7; }
.gc-msg.is-mine .gc-msg-sender { display:none; }
.gc-empty { text-align:center; margin:auto; color:#94A3B8; }

.gc-composer { background:#fff; padding:.55rem .85rem; border-top:1px solid #E0DFDC; flex-shrink:0; }
.gc-composer-row { display:flex; gap:.4rem; align-items:center; }
.gc-composer input[type=text] { flex:1; border:1px solid #E0DFDC; border-radius:0; padding:.5rem 1rem; font-size:.85rem; }
.gc-composer input[type=text]:focus { outline:0; border-color:#0A66C2; }
.gc-composer-icon { background:transparent; border:0; color:#666; width:36px; height:36px; display:flex; align-items:center; justify-content:center; font-size:.95rem; transition:background .12s, color .12s; }
.gc-composer-icon:hover { background:#E7F0F9; color:#0A66C2; }
.gc-composer-send { background:#0A66C2; color:#fff; border:0; width:38px; height:38px; border-radius:50%; }
.gc-composer-send:hover { background:#004182; color:#fff; }

/* Preview pièces jointes + partage sélectionné (avant envoi) */
.gc-composer-preview { display:flex; flex-wrap:wrap; gap:.4rem; padding:.35rem 0 .55rem; border-bottom:1px dashed #E0DFDC; margin-bottom:.4rem; }
.gc-preview-item { position:relative; border:1px solid #E0DFDC; background:#FAFAF9; padding:.35rem .55rem; display:inline-flex; align-items:center; gap:.4rem; font-size:.72rem; color:#191919; max-width:220px; }
.gc-preview-item img { width:40px; height:40px; object-fit:cover; }
.gc-preview-item .gc-preview-name { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.gc-preview-item .gc-preview-rm { background:transparent; border:0; color:#666; padding:0 .2rem; }
.gc-preview-item .gc-preview-rm:hover { color:#DC2626; }
.gc-preview-share { border-left:3px solid #0A66C2; background:#E7F0F9; max-width:100%; flex:1; }

/* Résultats de recherche de partage */
.gc-share-results { margin-top:.75rem; max-height:400px; overflow-y:auto; }
.gc-share-result { display:flex; align-items:flex-start; gap:.65rem; padding:.6rem .5rem; border-bottom:1px solid #EDEBE8; cursor:pointer; }
.gc-share-result:hover { background:#FAFAF9; }
.gc-share-result:last-child { border-bottom:0; }
.gc-share-result-icon { width:36px; height:36px; background:#E7F0F9; color:#0A66C2; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.gc-share-result-body { flex:1; min-width:0; }
.gc-share-result-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.05em; color:#666; font-weight:700; }
.gc-share-result-title { font-size:.85rem; font-weight:600; color:#191919; margin:.1rem 0; }
.gc-share-result-excerpt { font-size:.75rem; color:#666; }
.gc-share-empty { padding:1.5rem 0; color:#9CA3AF; text-align:center; font-size:.85rem; }

/* Rendu dans les messages : partage + pièces jointes */
.gc-msg-shared { display:flex; gap:.55rem; padding:.55rem .7rem; background:#F4F2EE; border-left:3px solid #0A66C2; margin-top:.4rem; text-decoration:none; color:#191919; }
.gc-msg-shared:hover { background:#EDEBE8; color:#191919; }
.gc-msg-shared-icon { width:32px; height:32px; background:#E7F0F9; color:#0A66C2; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.gc-msg-shared-body { flex:1; min-width:0; }
.gc-msg-shared-label { font-size:.6rem; text-transform:uppercase; letter-spacing:.05em; color:#666; font-weight:700; }
.gc-msg-shared-title { font-size:.82rem; font-weight:600; color:#191919; margin-top:.1rem; }
.gc-msg-shared-excerpt { font-size:.72rem; color:#666; margin-top:.15rem; }

.gc-msg-pjs { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.4rem; }
.gc-msg-pj-img { width:120px; height:120px; object-fit:cover; border:1px solid #E0DFDC; cursor:zoom-in; }
.gc-msg-pj-file { display:inline-flex; align-items:center; gap:.4rem; padding:.35rem .55rem; background:#F4F2EE; border:1px solid #E0DFDC; text-decoration:none; color:#191919; font-size:.75rem; max-width:220px; }
.gc-msg-pj-file:hover { background:#EDEBE8; color:#0A66C2; }
.gc-msg-pj-file i { color:#666; }

/* ── Autocomplete popup ─────────────────────────────── */
.gc-composer-input-wrap { flex:1; position:relative; }
.gc-composer-input-wrap input { width:100%; }
.gc-mention-popup {
    position:absolute; bottom:calc(100% + 4px); left:0; right:0;
    background:#fff; border:1px solid #E0DFDC; max-height:260px; overflow-y:auto;
    z-index:100;
}
.gc-mention-header { padding:.35rem .7rem; background:#F4F2EE; font-size:.68rem; text-transform:uppercase; font-weight:700; color:#666; letter-spacing:.05em; }
.gc-mention-item { display:block; padding:.5rem .75rem; border-bottom:1px solid #EDEBE8; cursor:pointer; font-size:.82rem; color:#191919; }
.gc-mention-item:last-child { border-bottom:0; }
.gc-mention-item:hover, .gc-mention-item.is-active { background:#E7F0F9; color:#0A66C2; }
.gc-mention-item .gc-mention-key { color:#0A66C2; font-weight:600; margin-right:.35rem; }
.gc-mention-item .gc-mention-excerpt { display:block; font-size:.7rem; color:#666; margin-top:.1rem; }
.gc-mention-empty { padding:1rem; color:#9CA3AF; font-size:.82rem; text-align:center; }

/* Enregistrement audio/vidéo */
.gc-rec-bar { height:6px; background:#EDEBE8; margin:.6rem 0; overflow:hidden; }
#gcRecBarFill { height:100%; width:0%; background:#DC2626; transition:width .3s linear; }
.gc-msg-pj-audio { display:block; width:100%; max-width:280px; margin-top:.35rem; }
.gc-msg-pj-video { display:block; width:100%; max-width:280px; max-height:200px; margin-top:.35rem; background:#000; }

.gc-side { display:flex; flex-direction:column; gap:.75rem; }
.gc-side-card { background:#fff; border:1px solid #E2E8F0; padding:.85rem 1rem; }
.gc-side-title { font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; font-weight:700; color:#94A3B8; margin-bottom:.55rem; }
.gc-member { display:flex; align-items:center; gap:.5rem; padding:.3rem 0; font-size:.8rem; color:#334155; }
.gc-member img, .gc-member .gc-member-fb { width:26px; height:26px; border-radius:50%; flex-shrink:0; }
.gc-member-fb { display:flex; align-items:center; justify-content:center; background:#0A66C2; color:#fff; font-size:.65rem; font-weight:700; }
.gc-member-role { font-size:.6rem; padding:.05rem .35rem; border-radius:0; margin-left:auto; text-transform:uppercase; font-weight:700; background:#F1F5F9; color:#64748B; }
.gc-member-role.admin { background:#FEF3C7; color:#F5B800; }
</style>
@endpush

@section('content')
<div class="gc-wrapper">
    {{-- Chat --}}
    <div class="gc-chat">
        <div class="gc-header">
            @if($group->avatar_url)
                <img src="{{ $group->avatar_url }}" alt="" class="gc-avatar" style="background:none;">
            @else
                <span class="gc-avatar">{{ strtoupper(mb_substr($group->nom, 0, 2)) }}</span>
            @endif
            <div style="min-width:0;">
                <h6 class="gc-header-name">{{ $group->nom }}</h6>
                <div class="gc-header-meta">
                    {{ $membres->count() }} membre{{ $membres->count() > 1 ? 's' : '' }}
                    @if($group->is_public) · <span style="color:#059669;"><i class="fas fa-globe"></i> Public</span>@endif
                </div>
            </div>
            <div class="gc-actions">
                <a href="{{ route('social.groups.index') }}" class="btn btn-sm btn-outline-secondary" title="Retour"><i class="fas fa-arrow-left"></i></a>
                @if($group->estAdmin(auth()->user()))
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#gcInviteModal" title="Inviter"><i class="fas fa-user-plus"></i></button>
                @endif
                <form action="{{ route('social.groups.leave', $group) }}" method="POST" onsubmit="return confirm('Quitter ce groupe ?');" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-warning" title="Quitter"><i class="fas fa-right-from-bracket"></i></button>
                </form>
                @if($group->estAdmin(auth()->user()))
                <form action="{{ route('social.groups.destroy', $group) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ce groupe ?');" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
        </div>

        <div class="gc-messages" id="gcMessages" data-group-id="{{ $group->id }}" data-last-id="{{ $messages->last()?->id ?? 0 }}">
            @php $messages->load(['piecesJointes', 'shared']); @endphp
            @forelse($messages as $msg)
            @php
                $isMine = $msg->sender_id === auth()->id();
                $shared = $msg->shared_type && $msg->shared_id && $msg->shared
                    ? \App\Services\Social\ShareableResolver::normalize($msg->shared) : null;
                $pjs = $msg->piecesJointes;
            @endphp
            <div class="gc-msg {{ $isMine ? 'is-mine' : '' }}" data-msg-id="{{ $msg->id }}">
                @if(!$isMine)
                <div class="gc-msg-avatar">{{ strtoupper(mb_substr($msg->sender?->prenoms ?? '',0,1).mb_substr($msg->sender?->name ?? '',0,1)) }}</div>
                @endif
                <div class="gc-msg-body">
                    <div class="gc-msg-sender">{{ $msg->sender?->prenoms }} {{ $msg->sender?->name }}</div>
                    @if($msg->contenu)<div class="gc-msg-text">{{ $msg->contenu }}</div>@endif
                    @if($pjs->count())
                    <div class="gc-msg-pjs">
                        @foreach($pjs as $pj)
                            @if($pj->categorie === 'image')
                                <img src="{{ $pj->url }}" class="gc-msg-pj-img" alt="" loading="lazy" onclick="window.open('{{ $pj->url }}', '_blank')">
                            @elseif($pj->categorie === 'audio')
                                <audio class="gc-msg-pj-audio" controls preload="metadata" src="{{ $pj->url }}"></audio>
                            @elseif($pj->categorie === 'video')
                                <video class="gc-msg-pj-video" controls preload="metadata" src="{{ $pj->url }}"></video>
                            @else
                                <a href="{{ $pj->url }}" target="_blank" class="gc-msg-pj-file"><i class="fas {{ $pj->icone }}"></i>{{ \Illuminate\Support\Str::limit($pj->nom_original ?? 'Fichier', 22) }}</a>
                            @endif
                        @endforeach
                    </div>
                    @endif
                    @if($shared)
                    <a href="{{ $shared['url'] ?? '#' }}" class="gc-msg-shared" target="_blank">
                        <div class="gc-msg-shared-icon"><i class="fas {{ $shared['icon'] }}"></i></div>
                        <div class="gc-msg-shared-body">
                            <div class="gc-msg-shared-label">{{ $shared['label'] }}</div>
                            <div class="gc-msg-shared-title">{{ $shared['title'] }}</div>
                            @if(!empty($shared['excerpt']))<div class="gc-msg-shared-excerpt">{{ \Illuminate\Support\Str::limit($shared['excerpt'], 100) }}</div>@endif
                        </div>
                    </a>
                    @endif
                    <div class="gc-msg-date">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
            @empty
            <div class="gc-empty">
                <i class="fas fa-comment-dots d-block mb-2" style="font-size:2rem; opacity:.4;"></i>
                <p class="mb-0">Aucun message. Soyez le premier à écrire !</p>
            </div>
            @endforelse
        </div>

        <form class="gc-composer" id="gcComposer" enctype="multipart/form-data"
              data-post-url="{{ route('social.groups.postMessage', $group) }}"
              data-search-url="{{ route('social.shareable.search') }}"
              data-mention-url="{{ route('social.mention.autocomplete') }}">
            @csrf
            <div class="gc-composer-preview" id="gcPreview" hidden></div>
            <div class="gc-composer-row">
                <button type="button" class="gc-composer-icon" id="gcAttachBtn" title="Joindre une pièce">
                    <i class="fas fa-paperclip"></i>
                </button>
                <button type="button" class="gc-composer-icon" data-bs-toggle="modal" data-bs-target="#gcShareModal" title="Partager un contenu ERP">
                    <i class="fas fa-share-nodes"></i>
                </button>
                <button type="button" class="gc-composer-icon" id="gcVoiceBtn" title="Enregistrer une note vocale (max 1 min)">
                    <i class="fas fa-microphone"></i>
                </button>
                <button type="button" class="gc-composer-icon" id="gcVideoBtn" title="Enregistrer une note vidéo (max 1 min)">
                    <i class="fas fa-video"></i>
                </button>
                <input type="file" name="pieces_jointes[]" id="gcFiles" multiple hidden
                       accept="image/*,video/*,application/pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                <input type="hidden" name="shared_type" id="gcSharedType">
                <input type="hidden" name="shared_id"   id="gcSharedId">
                <div class="gc-composer-input-wrap">
                    <input type="text" name="contenu" id="gcMsgInput" placeholder="Écrire un message… (astuce : @_ pour partager un contenu)" maxlength="2000" autocomplete="off">
                    <div class="gc-mention-popup" id="gcMentionPopup" hidden></div>
                </div>
                <button type="submit" title="Envoyer" class="gc-composer-send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </form>
    </div>

    {{-- Sidebar membres --}}
    <aside class="gc-side">
        <div class="gc-side-card">
            <div class="gc-side-title">Membres ({{ $membres->count() }})</div>
            @foreach($membres as $u)
            @php
                $ph = $u->profile_photo_path;
                $phU = $ph && preg_match('#^https?://#', $ph) ? $ph : ($ph ? asset('storage/'.ltrim($ph,'/')) : null);
                $ini = strtoupper(mb_substr($u->prenoms ?? '',0,1).mb_substr($u->name ?? '',0,1));
                $isAdmin = ($u->pivot->role ?? 'member') === 'admin';
            @endphp
            <div class="gc-member">
                @if($phU)<img src="{{ $phU }}" alt="">@else<span class="gc-member-fb">{{ $ini }}</span>@endif
                <span>{{ $u->prenoms }} {{ $u->name }}</span>
                @if($isAdmin)<span class="gc-member-role admin">Admin</span>@endif
            </div>
            @endforeach
        </div>
    </aside>
</div>

@if($group->estAdmin(auth()->user()))
<div class="modal fade" id="gcInviteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('social.groups.invite', $group) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title">Ajouter des membres</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Personnes à ajouter</label>
                <select name="members[]" multiple class="form-select gc-invite-select" required>
                    @foreach($usersDispo as $u)
                    <option value="{{ $u->id }}">{{ trim(($u->prenoms ?? '').' '.$u->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn text-white" style="background:#0A66C2;"><i class="fas fa-user-plus me-1"></i>Ajouter</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Modale : enregistrement note vocale / vidéo (max 60s) --}}
<div class="modal fade" id="gcRecordModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#0A66C2; color:#fff; border:0;">
                <h5 class="modal-title" id="gcRecTitle"><i class="fas fa-microphone me-2"></i>Note vocale</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="gcRecClose"></button>
            </div>
            <div class="modal-body text-center">
                <video id="gcRecPreview" muted playsinline autoplay hidden style="width:100%; max-height:280px; background:#000; margin-bottom:.75rem;"></video>
                <div id="gcRecTimer" style="font-size:1.8rem; font-weight:700; color:#191919; font-variant-numeric:tabular-nums;">0:00 / 1:00</div>
                <div class="gc-rec-bar"><div id="gcRecBarFill"></div></div>
                <div id="gcRecStatus" class="text-muted mt-2" style="font-size:.85rem;">Cliquez sur ● pour commencer</div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" id="gcRecStart"><i class="fas fa-circle me-1" style="color:#fff;"></i>Enregistrer</button>
                <button type="button" class="btn btn-warning" id="gcRecStop" hidden><i class="fas fa-stop me-1"></i>Arrêter</button>
                <button type="button" class="btn btn-outline-secondary" id="gcRecReset" hidden><i class="fas fa-rotate-left me-1"></i>Recommencer</button>
                <button type="button" class="btn text-white" id="gcRecUse" style="background:#0A66C2;" hidden><i class="fas fa-paper-plane me-1"></i>Joindre au message</button>
            </div>
        </div>
    </div>
</div>

{{-- Modale : partage de contenu ERP dans le chat --}}
<div class="modal fade" id="gcShareModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#0A66C2; color:#fff; border:0;">
                <h5 class="modal-title"><i class="fas fa-share-nodes me-2"></i>Partager un contenu ERP</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Filtrez par module et recherchez le contenu à partager.</p>
                <details class="mb-2" style="font-size:.78rem;">
                    <summary style="cursor:pointer; color:#0A66C2; font-weight:600;">Astuce : partage rapide en tapant dans le message</summary>
                    <div style="padding:.5rem .75rem; background:#F4F2EE; border:1px solid #E0DFDC; margin-top:.35rem;">
                        Tapez dans votre message : <code style="background:#E7F0F9; padding:.1rem .35rem;">@_module&@_section&@_titre</code>
                        <br><strong>Exemples :</strong>
                        <ul style="margin:.4rem 0 0; padding-left:1.2rem;">
                            <li><code>@_intranet&@_annonces&@_Réunion CODIR</code></li>
                            <li><code>@_projet&@_taches&@_Livrer maquette</code></li>
                            <li><code>@_appro&@_commandes&@_CMD-2026-0042</code></li>
                            <li><code>@_finance&@_factures&@_F-2026-118</code></li>
                        </ul>
                        <div class="text-muted mt-1">Modules : <code>intranet, social, finance, appro, mg, rh, projet, strategie</code></div>
                    </div>
                </details>
                <div class="d-flex gap-2 mb-2">
                    <select id="gcShareModule" class="form-select form-select-sm" style="max-width:220px;">
                        @foreach(\App\Services\Social\ShareableResolver::MODULES as $key => $m)
                            <option value="{{ $key }}">{{ $m['label'] }}</option>
                        @endforeach
                    </select>
                    <input type="text" id="gcShareSearch" class="form-control" placeholder="Tapez au moins 2 caractères…" autocomplete="off">
                </div>
                <div id="gcShareResults" class="gc-share-results"></div>
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
    var input = composer.querySelector('input[name="contenu"]');
    var postUrl = composer.dataset.postUrl;
    var groupId = messagesEl.dataset.groupId;
    var lastId = parseInt(messagesEl.dataset.lastId || '0', 10);

    // Scroll bas au chargement
    messagesEl.scrollTop = messagesEl.scrollHeight;

    function initialesFrom(name) {
        var parts = (name || '').trim().split(/\s+/);
        return ((parts[0] || '?')[0] + (parts[1] || '')[0] || '').toUpperCase();
    }
    function appendMessage(m) {
        var isMine = m.sender_id === CURRENT_USER_ID;
        var wrap = document.createElement('div');
        wrap.className = 'gc-msg' + (isMine ? ' is-mine' : '');
        wrap.dataset.msgId = m.id;
        if (!isMine) {
            var av = document.createElement('div'); av.className = 'gc-msg-avatar'; av.textContent = initialesFrom(m.sender);
            wrap.appendChild(av);
        }
        var body = document.createElement('div'); body.className = 'gc-msg-body';
        var sender = document.createElement('div'); sender.className = 'gc-msg-sender'; sender.textContent = m.sender;
        body.appendChild(sender);
        if (m.contenu) {
            var text = document.createElement('div'); text.className = 'gc-msg-text'; text.textContent = m.contenu;
            body.appendChild(text);
        }
        // Pièces jointes
        if (Array.isArray(m.pieces) && m.pieces.length) {
            var pjs = document.createElement('div'); pjs.className = 'gc-msg-pjs';
            m.pieces.forEach(function (p) {
                if (p.categorie === 'image') {
                    var img = document.createElement('img');
                    img.className = 'gc-msg-pj-img'; img.src = p.url; img.loading = 'lazy';
                    img.addEventListener('click', function () { window.open(p.url, '_blank'); });
                    pjs.appendChild(img);
                } else if (p.categorie === 'audio') {
                    var au = document.createElement('audio'); au.className = 'gc-msg-pj-audio';
                    au.controls = true; au.preload = 'metadata'; au.src = p.url;
                    pjs.appendChild(au);
                } else if (p.categorie === 'video') {
                    var vi = document.createElement('video'); vi.className = 'gc-msg-pj-video';
                    vi.controls = true; vi.preload = 'metadata'; vi.src = p.url;
                    pjs.appendChild(vi);
                } else {
                    var a = document.createElement('a'); a.className = 'gc-msg-pj-file';
                    a.href = p.url; a.target = '_blank';
                    a.innerHTML = '<i class="fas fa-file"></i>';
                    var span = document.createElement('span'); span.textContent = (p.nom || 'Fichier').substring(0, 22);
                    a.appendChild(span);
                    pjs.appendChild(a);
                }
            });
            body.appendChild(pjs);
        }
        // Partage
        if (m.shared) {
            var sh = document.createElement('a'); sh.className = 'gc-msg-shared';
            sh.href = m.shared.url || '#'; sh.target = '_blank';
            var ic = document.createElement('div'); ic.className = 'gc-msg-shared-icon';
            ic.innerHTML = '<i class="fas ' + m.shared.icon + '"></i>';
            var b = document.createElement('div'); b.className = 'gc-msg-shared-body';
            var lbl = document.createElement('div'); lbl.className = 'gc-msg-shared-label'; lbl.textContent = m.shared.label;
            var ttl = document.createElement('div'); ttl.className = 'gc-msg-shared-title'; ttl.textContent = m.shared.title;
            b.appendChild(lbl); b.appendChild(ttl);
            if (m.shared.excerpt) {
                var ex = document.createElement('div'); ex.className = 'gc-msg-shared-excerpt'; ex.textContent = m.shared.excerpt.substring(0, 100);
                b.appendChild(ex);
            }
            sh.appendChild(ic); sh.appendChild(b);
            body.appendChild(sh);
        }
        var date = document.createElement('div'); date.className = 'gc-msg-date'; date.textContent = m.date;
        body.appendChild(date);
        wrap.appendChild(body);
        messagesEl.appendChild(wrap);
        var empty = messagesEl.querySelector('.gc-empty'); if (empty) empty.remove();
    }

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
                        row.innerHTML = '<div class="gc-share-result-icon"><i class="fas ' + r.icon + '"></i></div>';
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
            setStatus('Cliquez sur ● pour commencer');
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
        input.value = '';
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
                alert('Message non envoyé.');
            }
        }).catch(function () { alert('Erreur envoi message.'); });
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
