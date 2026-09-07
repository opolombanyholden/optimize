@extends('layouts.app')

@section('title', 'Réseau social')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════════════
   Réseau social — Design system professionnel
   Palette : Bleu roi (#0A66C2), Or (#F5B800), Neutres (LinkedIn-like)
   Principes : aucun dégradé, bordures 1px, radius 8px max, ombres subtiles
═══════════════════════════════════════════════════════════════ */
:root {
    --sc-primary:      #0A66C2;
    --sc-primary-hover:#004182;
    --sc-primary-soft: #E7F0F9;
    --sc-accent:       #F5B800;
    --sc-accent-soft:  #FFF5D6;
    --sc-bg:           #F4F2EE;
    --sc-card:         #FFFFFF;
    --sc-border:       #E0DFDC;
    --sc-border-soft:  #EDEBE8;
    --sc-text:         #191919;
    --sc-text-soft:    #666666;
    --sc-text-muted:   #9CA3AF;
    --sc-radius:       0;
    --sc-radius-sm:    0;
    --sc-shadow:       none;
}

.sc-wrapper { display:grid; grid-template-columns: 1fr 300px; gap:1.5rem; max-width:1040px; margin:0 auto; }
@media(max-width: 991px) { .sc-wrapper { grid-template-columns: 1fr; } .sc-side { order: 2; } }

.sc-card { background:var(--sc-card); border-radius:var(--sc-radius); border:1px solid var(--sc-border); margin-bottom:.75rem; box-shadow:var(--sc-shadow); }
.sc-card-header { padding:.85rem 1.1rem; border-bottom:1px solid var(--sc-border-soft); font-weight:600; color:var(--sc-text); display:flex; align-items:center; gap:.5rem; }

/* ── Composer ────────────────────────────────────────── */
.sc-composer { padding:1rem 1.15rem; }
#scEditor { border:1px solid var(--sc-border); border-top:0; min-height:100px; font-size:.925rem; }
.ql-toolbar.ql-snow { border:1px solid var(--sc-border); border-bottom:0; background:#FAFAF9; }
.sc-emoji-bar { display:flex; flex-wrap:wrap; gap:.15rem; padding:.4rem .55rem; background:#FAFAF9; border:1px solid var(--sc-border); border-top:0; }
.sc-emoji { background:transparent; border:0; font-size:1.05rem; padding:.2rem .4rem; border-radius:var(--sc-radius-sm); cursor:pointer; line-height:1; transition:background .12s; }
.sc-emoji:hover { background:var(--sc-primary-soft); }
.sc-composer-actions { display:flex; justify-content:space-between; align-items:center; margin-top:.85rem; gap:.5rem; }
.sc-file-label { color:var(--sc-text-soft); cursor:pointer; font-size:.85rem; font-weight:500; display:inline-flex; align-items:center; gap:.4rem; padding:.4rem .65rem; border-radius:var(--sc-radius-sm); transition:background .12s, color .12s; }
.sc-file-label:hover { background:var(--sc-primary-soft); color:var(--sc-primary); }
.sc-file-name { font-size:.75rem; color:var(--sc-text-muted); margin-left:.5rem; }

/* Bouton primaire "Publier" — solide, pas de dégradé */
.sc-btn-primary { background:var(--sc-primary); color:#fff; border:0; padding:.5rem 1.2rem; font-weight:600; font-size:.85rem; letter-spacing:.02em; transition:background .12s; }
.sc-btn-primary:hover { background:var(--sc-primary-hover); color:#fff; }

/* ── Ciblage ─────────────────────────────────────────── */
.sc-target-block { border:1px solid var(--sc-border); border-top:0; padding:.6rem .85rem; background:#FAFAF9; }
.sc-target-tabs { display:flex; gap:1.25rem; flex-wrap:wrap; font-size:.82rem; color:var(--sc-text-soft); }
.sc-target-tab { display:inline-flex; align-items:center; gap:.4rem; cursor:pointer; }
.sc-target-tab input[type=radio] { accent-color:var(--sc-primary); }
.sc-target-picker { margin-top:.6rem; }
.sc-target-picker .ts-control { border-radius:var(--sc-radius-sm); font-size:.82rem; border-color:var(--sc-border); }

/* ── Preview multi-médias ────────────────────────────── */
.sc-preview-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:.4rem; padding:.65rem 0 .25rem; }
.sc-preview-tile { position:relative; aspect-ratio:1/1; overflow:hidden; border-radius:var(--sc-radius-sm); background:#F4F2EE; border:1px solid var(--sc-border-soft); }
.sc-preview-tile img, .sc-preview-tile video { width:100%; height:100%; object-fit:cover; display:block; }
.sc-preview-tile .sc-preview-rm { position:absolute; top:.3rem; right:.3rem; width:22px; height:22px; border-radius:50%; background:rgba(0,0,0,.7); color:#fff; border:0; font-size:.72rem; display:flex; align-items:center; justify-content:center; }

/* ── Galerie médias sur les posts ────────────────────── */
.sc-media-gallery { display:grid; gap:2px; margin:.65rem 0 0; max-height:520px; overflow:hidden; border-radius:var(--sc-radius-sm); border:1px solid var(--sc-border); }
.sc-media-count-1 { grid-template-columns:1fr; }
.sc-media-count-2 { grid-template-columns:1fr 1fr; }
.sc-media-count-3 { grid-template-columns:1fr 1fr; grid-template-rows:1fr 1fr; }
.sc-media-count-3 .sc-media-item:first-child { grid-row:span 2; }
.sc-media-count-4 { grid-template-columns:1fr 1fr; grid-template-rows:1fr 1fr; }
.sc-media-item { position:relative; overflow:hidden; background:#F4F2EE; aspect-ratio:1/1; }
.sc-media-count-1 .sc-media-item { aspect-ratio:16/9; }
.sc-media-item img, .sc-media-item video { width:100%; height:100%; object-fit:cover; display:block; }
.sc-media-play { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:2rem; background:rgba(0,0,0,.35); text-shadow:0 2px 6px rgba(0,0,0,.4); }
.sc-media-more { position:relative; }
.sc-media-more::after { content:''; position:absolute; inset:0; background:rgba(0,0,0,.6); }
.sc-media-more-count { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.4rem; font-weight:700; z-index:1; }
.sc-media-gallery button.sc-media-item { border:0; padding:0; background:transparent; cursor:zoom-in; }
.sc-media-gallery button.sc-media-item:hover img,
.sc-media-gallery button.sc-media-item:hover video { opacity:.92; }

/* ── Lightbox zoom média ─────────────────────────────── */
.sc-lightbox { position:fixed; inset:0; background:rgba(0,0,0,.88); z-index:2000; display:none; align-items:center; justify-content:center; opacity:0; transition:opacity .18s; }
.sc-lightbox.is-open { display:flex; opacity:1; }
.sc-lb-body { display:flex; gap:1rem; max-width:96vw; max-height:88vh; align-items:stretch; }
.sc-lb-stage { flex:1; max-width:70vw; max-height:88vh; display:flex; align-items:center; justify-content:center; }
.sc-lb-stage img, .sc-lb-stage video { max-width:70vw; max-height:88vh; object-fit:contain; border-radius:var(--sc-radius-sm); animation:sc-lb-zoom .2s ease-out; }
.sc-lb-panel { width:340px; background:var(--sc-card); display:flex; flex-direction:column; border-radius:var(--sc-radius-sm); overflow:hidden; }
.sc-lb-actions { display:flex; align-items:center; gap:1.25rem; padding:.85rem 1rem; border-bottom:1px solid var(--sc-border-soft); }
.sc-lb-like { background:transparent; border:0; color:var(--sc-text-soft); font-weight:600; display:inline-flex; align-items:center; gap:.4rem; font-size:.9rem; padding:.4rem .7rem; border-radius:var(--sc-radius-sm); cursor:pointer; transition:background .12s, color .12s; }
.sc-lb-like:hover { background:var(--sc-primary-soft); color:var(--sc-primary); }
.sc-lb-like.is-liked { color:var(--sc-primary); }
.sc-lb-like.is-liked i { font-weight:900; }
.sc-lb-com-count { color:var(--sc-text-soft); font-size:.85rem; display:inline-flex; align-items:center; gap:.4rem; }
.sc-lb-comments { flex:1; overflow-y:auto; padding:.85rem 1rem; }
.sc-lb-com { margin-bottom:.75rem; }
.sc-lb-com-auth { font-weight:600; font-size:.78rem; color:var(--sc-text); }
.sc-lb-com-txt { font-size:.82rem; color:var(--sc-text); background:#F4F2EE; padding:.4rem .75rem; border-radius:var(--sc-radius); display:inline-block; margin-top:.15rem; }
.sc-lb-com-date { font-size:.68rem; color:var(--sc-text-muted); margin-top:.2rem; }
.sc-lb-empty { color:var(--sc-text-muted); font-size:.82rem; text-align:center; padding:1.5rem 0; }
.sc-lb-comment-form { display:flex; gap:.5rem; padding:.75rem 1rem; border-top:1px solid var(--sc-border-soft); background:#FAFAF9; }
.sc-lb-comment-form input { flex:1; border:1px solid var(--sc-border); border-radius:0; padding:.45rem .95rem; font-size:.82rem; background:#fff; }
.sc-lb-comment-form input:focus { outline:0; border-color:var(--sc-primary); }
.sc-lb-comment-form button { background:var(--sc-primary); color:#fff; border:0; border-radius:50%; width:34px; height:34px; font-size:.82rem; transition:background .12s; }
.sc-lb-comment-form button:hover { background:var(--sc-primary-hover); }
@media(max-width:900px) {
    .sc-lb-body { flex-direction:column; max-height:96vh; }
    .sc-lb-stage { max-width:96vw; max-height:60vh; }
    .sc-lb-stage img, .sc-lb-stage video { max-width:96vw; max-height:60vh; }
    .sc-lb-panel { width:96vw; max-height:36vh; }
}
@keyframes sc-lb-zoom { from { transform:scale(.94); opacity:0; } to { transform:scale(1); opacity:1; } }
.sc-lb-close { position:absolute; top:1rem; right:1rem; width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,.12); color:#fff; border:0; font-size:1.4rem; line-height:1; cursor:pointer; transition:background .12s; }
.sc-lb-close:hover { background:rgba(255,255,255,.24); }
.sc-lb-nav { position:absolute; top:50%; transform:translateY(-50%); width:44px; height:44px; border-radius:50%; background:rgba(255,255,255,.1); color:#fff; border:0; font-size:1.15rem; cursor:pointer; transition:background .12s; }
.sc-lb-nav:hover { background:rgba(255,255,255,.24); }
.sc-lb-prev { left:1.5rem; }
.sc-lb-next { right:1.5rem; }
.sc-lb-counter { position:absolute; bottom:1rem; left:50%; transform:translateX(-50%); background:rgba(0,0,0,.5); color:#fff; padding:.3rem .85rem; border-radius:0; font-size:.75rem; font-weight:500; }
@media(max-width:575px) { .sc-lb-nav { width:36px; height:36px; } .sc-lb-prev { left:.5rem; } .sc-lb-next { right:.5rem; } }

/* ── Post ────────────────────────────────────────────── */
.sc-post { padding:1rem 1.15rem; }
.sc-post-head { display:flex; align-items:center; gap:.7rem; margin-bottom:.75rem; }
.sc-avatar { width:44px; height:44px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.sc-avatar-fb { display:flex; align-items:center; justify-content:center; background:var(--sc-primary); color:#fff; font-size:.88rem; font-weight:600; }
.sc-post-author { font-weight:600; color:var(--sc-text); font-size:.9rem; line-height:1.2; }
.sc-post-date { font-size:.75rem; color:var(--sc-text-muted); margin-top:.15rem; display:flex; align-items:center; gap:.35rem; }
.sc-post-vis { display:inline-flex; align-items:center; gap:.2rem; margin-left:.4rem; padding:.05rem .35rem; border-radius:var(--sc-radius-sm); font-size:.6rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; background:var(--sc-accent-soft); color:#8A6D0B; }
.sc-post-vis.pub { background:var(--sc-primary-soft); color:var(--sc-primary); }
.sc-post-content { color:var(--sc-text); font-size:.93rem; line-height:1.55; margin-bottom:.5rem; }
.sc-post-content p { margin:0 0 .5rem; }
.sc-post-content p:last-child { margin-bottom:0; }
.sc-post-content h1, .sc-post-content h2, .sc-post-content h3 { font-weight:600; color:var(--sc-text); margin:.75rem 0 .35rem; }
.sc-post-content h1 { font-size:1.25rem; }
.sc-post-content h2 { font-size:1.1rem; }
.sc-post-content h3 { font-size:1rem; }
.sc-post-content ul, .sc-post-content ol { padding-left:1.4rem; margin:.35rem 0 .5rem; }
.sc-post-content blockquote { border-left:3px solid var(--sc-primary); padding-left:.75rem; color:var(--sc-text-soft); margin:.5rem 0; }
.sc-post-content a { color:var(--sc-primary); text-decoration:none; }
.sc-post-content a:hover { text-decoration:underline; }
.sc-post-media { margin:.6rem 0 0; border-radius:var(--sc-radius-sm); overflow:hidden; max-height:520px; border:1px solid var(--sc-border); }
.sc-post-media img, .sc-post-media video { width:100%; display:block; max-height:520px; object-fit:cover; }

.sc-post-stats { display:flex; gap:1rem; padding:.65rem 0 .5rem; font-size:.75rem; color:var(--sc-text-muted); border-bottom:1px solid var(--sc-border-soft); margin-top:.65rem; margin-bottom:.35rem; }
.sc-post-actions { display:flex; gap:.25rem; margin-top:.25rem; }
.sc-btn-action { flex:1; background:transparent; border:0; padding:.6rem; border-radius:var(--sc-radius-sm); font-size:.85rem; font-weight:500; color:var(--sc-text-soft); cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:.45rem; transition:background .12s, color .12s; }
.sc-btn-action:hover { background:#F4F2EE; color:var(--sc-text); }
.sc-btn-action.is-liked { color:var(--sc-primary); font-weight:600; }
.sc-btn-action.is-liked:hover { background:var(--sc-primary-soft); }

.sc-comments { border-top:1px solid var(--sc-border-soft); margin-top:.35rem; padding-top:.75rem; }
.sc-comment { display:flex; gap:.6rem; margin-bottom:.6rem; }
.sc-comment .sc-avatar { width:32px; height:32px; font-size:.72rem; }
.sc-comment-bubble { background:#F4F2EE; border-radius:var(--sc-radius); padding:.5rem .8rem; font-size:.82rem; line-height:1.4; }
.sc-comment-author { font-weight:600; font-size:.78rem; color:var(--sc-text); margin-right:.35rem; }
.sc-comment-form { display:flex; gap:.5rem; margin-top:.65rem; }
.sc-comment-form input { flex:1; border:1px solid var(--sc-border); border-radius:0; padding:.45rem .95rem; font-size:.82rem; }
.sc-comment-form input:focus { outline:0; border-color:var(--sc-primary); }
.sc-comment-form button { background:var(--sc-primary); color:#fff; border:0; border-radius:0; padding:.4rem 1.1rem; font-size:.78rem; font-weight:600; transition:background .12s; }
.sc-comment-form button:hover { background:var(--sc-primary-hover); }

.sc-post-menu { position:relative; margin-left:auto; display:flex; gap:.15rem; }
.sc-post-menu-btn { background:transparent; border:0; color:var(--sc-text-muted); padding:.4rem .55rem; border-radius:var(--sc-radius-sm); }
.sc-post-menu-btn:hover { background:#F4F2EE; color:var(--sc-text); }

/* ── Sidebar right ───────────────────────────────────── */
.sc-side { display:flex; flex-direction:column; }
.sc-side-card { background:var(--sc-card); border-radius:var(--sc-radius); border:1px solid var(--sc-border); padding:1rem 1.15rem; box-shadow:var(--sc-shadow); }
.sc-side-title { font-size:.75rem; text-transform:uppercase; font-weight:700; letter-spacing:.05em; color:var(--sc-text-soft); margin-bottom:.75rem; display:flex; align-items:center; gap:.4rem; }

/* Anniversaires */
.sc-bday-row { display:flex; align-items:center; gap:.65rem; padding:.55rem 0; border-bottom:1px solid var(--sc-border-soft); }
.sc-bday-row:last-child { border-bottom:0; }
.sc-bday-avatar { width:36px; height:36px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.sc-bday-avatar-fb { display:flex; align-items:center; justify-content:center; background:var(--sc-accent); color:#4A3800; font-size:.75rem; font-weight:700; }
.sc-bday-name { font-size:.82rem; font-weight:600; color:var(--sc-text); line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sc-bday-meta { font-size:.72rem; color:var(--sc-text-muted); margin-top:.15rem; }
.sc-bday-wish-btn { background:var(--sc-accent-soft); border:1px solid var(--sc-accent); color:#8A6D0B; padding:.3rem .55rem; border-radius:0; font-size:.75rem; font-weight:600; flex-shrink:0; transition:background .12s; }
.sc-bday-wish-btn:hover { background:var(--sc-accent); color:#4A3800; }
.sc-bday-sent { width:26px; height:26px; border-radius:50%; background:#E6F4EA; color:#137333; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
.sc-empty { padding:1rem 0; color:var(--sc-text-muted); font-size:.82rem; text-align:center; }

/* Tâches */
.sc-task-row { display:flex; align-items:flex-start; gap:.6rem; padding:.55rem 0; border-bottom:1px solid var(--sc-border-soft); text-decoration:none; color:inherit; }
.sc-task-row:last-child { border-bottom:0; }
.sc-task-row:hover { color:var(--sc-text); }
.sc-task-dot { width:6px; height:6px; border-radius:50%; margin-top:.55rem; flex-shrink:0; }
.sc-task-title { font-size:.82rem; font-weight:600; color:var(--sc-text); line-height:1.25; }
.sc-task-meta { font-size:.72rem; color:var(--sc-text-muted); margin-top:.2rem; }
.sc-task-pill { font-size:.6rem; font-weight:700; padding:.15rem .45rem; border-radius:var(--sc-radius-sm); text-transform:uppercase; letter-spacing:.04em; flex-shrink:0; align-self:flex-start; }

/* Activité groupes */
.sc-group-row { display:flex; align-items:center; gap:.65rem; padding:.6rem 0; border-bottom:1px solid var(--sc-border-soft); text-decoration:none; color:inherit; }
.sc-group-row:last-child { border-bottom:0; }
.sc-group-row:hover { color:var(--sc-text); }
.sc-group-row:hover .sc-group-name { color:var(--sc-primary); }
.sc-group-avatar { width:38px; height:38px; border-radius:50%; object-fit:cover; flex-shrink:0; }
.sc-group-avatar-fb { display:flex; align-items:center; justify-content:center; background:var(--sc-primary); color:#fff; font-size:.72rem; font-weight:700; }
.sc-group-body { flex:1; min-width:0; }
.sc-group-head { display:flex; align-items:baseline; gap:.4rem; }
.sc-group-name { font-size:.82rem; font-weight:600; color:var(--sc-text); flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sc-group-time { font-size:.65rem; color:var(--sc-text-muted); flex-shrink:0; }
.sc-group-preview { font-size:.72rem; color:var(--sc-text-soft); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:.15rem; }
.sc-group-badge { background:var(--sc-primary); color:#fff; font-size:.68rem; font-weight:700; min-width:20px; height:20px; padding:0 .4rem; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

/* Agenda */
.sc-agenda-row { display:flex; align-items:flex-start; gap:.6rem; padding:.55rem 0; border-bottom:1px solid var(--sc-border-soft); text-decoration:none; color:inherit; }
.sc-agenda-row:last-child { border-bottom:0; }
.sc-agenda-row:hover { color:var(--sc-text); }
.sc-agenda-dot { width:8px; height:8px; border-radius:50%; margin-top:.45rem; flex-shrink:0; }
.sc-agenda-title { font-size:.82rem; font-weight:600; color:var(--sc-text); line-height:1.25; }
.sc-agenda-meta { font-size:.72rem; color:var(--sc-text-muted); margin-top:.2rem; }
</style>
@endpush

@section('content')

<div class="sc-wrapper">
    <div>
        {{-- Composer avec éditeur enrichi (Quill) --}}
        <div class="sc-card">
            <form action="{{ route('social.posts.store') }}" method="POST" enctype="multipart/form-data" class="sc-composer" id="scComposerForm">
                @csrf
                <div id="scEditor"></div>
                <input type="hidden" name="contenu" id="scContenu" value="{{ old('contenu') }}">
                <div class="sc-emoji-bar" id="scEmojiBar" title="Cliquez pour insérer">
                    @foreach(['😀','😁','😂','🥰','😎','🤔','👍','🎉','🔥','❤️','🙌','👏','🚀','💡','✅','⚠️'] as $emo)
                    <button type="button" class="sc-emoji" data-emo="{{ $emo }}">{{ $emo }}</button>
                    @endforeach
                </div>

                {{-- Ciblage --}}
                <div class="sc-target-block">
                    <div class="sc-target-tabs">
                        <label class="sc-target-tab">
                            <input type="radio" name="visibilite" value="public" checked>
                            <span><i class="fas fa-globe me-1"></i>Public — tous les collaborateurs</span>
                        </label>
                        <label class="sc-target-tab">
                            <input type="radio" name="visibilite" value="prive">
                            <span><i class="fas fa-lock me-1"></i>Cibler des personnes / groupes / entités</span>
                        </label>
                    </div>
                    <div class="sc-target-picker" id="scTargetPicker" style="display:none;">
                        <select name="cibles[]" multiple class="form-select sc-target-select" placeholder="Ajouter des cibles…">
                            @foreach($usersDispo as $u)
                            <option value="user:{{ $u->id }}">👤 {{ trim(($u->prenoms ?? '').' '.$u->name) }}</option>
                            @endforeach
                            @foreach($groupesDispo as $g)
                            <option value="groupe:{{ $g->id }}">👥 {{ $g->nom }}</option>
                            @endforeach
                            @foreach($entitesDispo as $e)
                            <option value="entite:{{ $e->id }}">🏢 {{ $e->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Multi-médias avec preview --}}
                <div class="sc-composer-actions">
                    <div>
                        <label for="scMedias" class="sc-file-label"><i class="fas fa-image"></i> Ajouter photos/vidéos</label>
                        <input type="file" name="medias[]" id="scMedias" hidden multiple accept="image/*,video/*">
                        <span class="sc-file-name text-muted">jusqu'à 10 fichiers · 20 Mo/fichier</span>
                    </div>
                    <button class="sc-btn-primary"><i class="fas fa-paper-plane me-1"></i>Publier</button>
                </div>
                <div class="sc-preview-grid" id="scPreviewGrid"></div>
            </form>
        </div>

        {{-- Feed --}}
        @forelse($posts as $post)
        @php
            $auteur = $post->auteur;
            $photoPath = $auteur?->profile_photo_path;
            $photoUrl = $photoPath && preg_match('#^https?://#', $photoPath) ? $photoPath
                        : ($photoPath ? asset('storage/'.ltrim($photoPath, '/')) : null);
            $initiales = strtoupper(mb_substr($auteur?->prenoms ?? '', 0, 1).mb_substr($auteur?->name ?? '', 0, 1));
            $isLiked = $post->estLikeParUser();
            $isMine = auth()->id() === $post->created_by;
            $mediaUrlOk = $post->media_url && preg_match('#^https?://[^\s"\'()<>]+$#', $post->media_url);
            $medias = $post->piecesJointes->whereIn('categorie', ['image', 'video'])->values();
            $editable = $isMine && $post->created_at->gte(now()->subHours(8));
            $isPrive = $post->publication?->visibilite === 'prive';
        @endphp
        <div class="sc-card">
            <div class="sc-post">
                <div class="sc-post-head">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="" class="sc-avatar" loading="lazy">
                    @else
                        <div class="sc-avatar sc-avatar-fb">{{ $initiales ?: '?' }}</div>
                    @endif
                    <div style="flex:1; min-width:0;">
                        <div class="sc-post-author">
                            {{ $auteur?->prenoms }} {{ $auteur?->name ?? '—' }}
                            @if($isPrive)<span class="sc-post-vis" title="Publication ciblée"><i class="fas fa-lock"></i> Ciblé</span>
                            @else<span class="sc-post-vis pub" title="Public"><i class="fas fa-globe"></i></span>@endif
                        </div>
                        <div class="sc-post-date"><i class="fas fa-clock me-1"></i>{{ $post->created_at?->diffForHumans() }}</div>
                    </div>
                    @if($isMine)
                    <div class="sc-post-menu d-flex gap-1">
                        @if($editable)
                        <button type="button" class="sc-post-menu-btn" title="Modifier"
                                data-bs-toggle="modal" data-bs-target="#scEditModal-{{ $post->id }}">
                            <i class="fas fa-pen"></i>
                        </button>
                        @endif
                        <form action="{{ route('social.posts.destroy', $post) }}" method="POST" onsubmit="return confirm('Supprimer cette publication ?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="sc-post-menu-btn" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                    @endif
                </div>

                <div class="sc-post-content ql-editor-view">{!! $post->contenu !!}</div>

                @if($medias->count())
                @php
                    $galleryPayload = $medias->map(fn ($x) => [
                        'id'    => $x->id,
                        'url'   => $x->url,
                        'type'  => $x->categorie,
                        'likes' => $x->totalLikes(),
                        'liked' => $x->estLikeParUser(),
                        'comments_count' => $x->totalCommentaires(),
                        'comments' => $x->commentaires()->with('auteur')->latest()->take(5)->get()
                            ->map(fn ($c) => [
                                'auteur'  => trim(($c->auteur?->prenoms ?? '').' '.($c->auteur?->name ?? '')),
                                'contenu' => e($c->contenu),
                                'date'    => $c->created_at?->diffForHumans(),
                            ])->reverse()->values(),
                    ])->values()->toJson();
                @endphp
                <div class="sc-media-gallery sc-media-count-{{ min($medias->count(), 4) }}"
                     data-gallery="{{ $galleryPayload }}">
                    @foreach($medias->take(4) as $i => $m)
                    <button type="button" data-idx="{{ $i }}" class="sc-media-item @if($medias->count() > 4 && $i === 3) sc-media-more @endif">
                        @if($m->categorie === 'video')
                            <video src="{{ $m->url }}" muted></video>
                            <span class="sc-media-play"><i class="fas fa-play"></i></span>
                        @else
                            <img src="{{ $m->url }}" alt="" loading="lazy">
                        @endif
                        @if($medias->count() > 4 && $i === 3)
                            <span class="sc-media-more-count">+{{ $medias->count() - 4 }}</span>
                        @endif
                    </button>
                    @endforeach
                </div>
                @elseif($mediaUrlOk)
                @php $galleryPayload = json_encode([['url' => $post->media_url, 'type' => $post->media_principal_type ?? 'image']]); @endphp
                <div class="sc-post-media sc-media-gallery sc-media-count-1" data-gallery="{{ $galleryPayload }}">
                    <button type="button" data-idx="0" class="sc-media-item" style="border:0; padding:0; width:100%;">
                        @if($post->media_principal_type === 'video')
                            <video src="{{ $post->media_url }}" muted></video>
                            <span class="sc-media-play"><i class="fas fa-play"></i></span>
                        @else
                            <img src="{{ $post->media_url }}" alt="" loading="lazy">
                        @endif
                    </button>
                </div>
                @endif

                <div class="sc-post-stats">
                    <span><i class="fas fa-heart" style="color:#F59E0B;"></i> {{ $post->likes_count ?? 0 }} j'aime</span>
                    <span><i class="fas fa-comment" style="color:#1E40AF;"></i> {{ $post->commentaires_count ?? 0 }} commentaire{{ ($post->commentaires_count ?? 0) > 1 ? 's' : '' }}</span>
                </div>

                <div class="sc-post-actions">
                    <form action="{{ route('social.posts.like', $post) }}" method="POST" style="flex:1;">
                        @csrf
                        <button type="submit" class="sc-btn-action {{ $isLiked ? 'is-liked' : '' }}" style="width:100%;">
                            <i class="fa{{ $isLiked ? 's' : 'r' }} fa-heart"></i> J'aime
                        </button>
                    </form>
                    <button type="button" class="sc-btn-action" style="flex:1;" onclick="document.getElementById('sc-comm-{{ $post->id }}')?.focus();">
                        <i class="far fa-comment"></i> Commenter
                    </button>
                </div>

                @if($post->commentaires->count() || true)
                <div class="sc-comments">
                    @foreach($post->commentaires->sortBy('created_at')->take(5) as $com)
                    <div class="sc-comment">
                        @php
                            $ca = $com->auteur;
                            $caPhoto = $ca?->profile_photo_path;
                            $caUrl = $caPhoto && preg_match('#^https?://#', $caPhoto) ? $caPhoto
                                     : ($caPhoto ? asset('storage/'.ltrim($caPhoto, '/')) : null);
                            $caInit = strtoupper(mb_substr($ca?->prenoms ?? '', 0, 1).mb_substr($ca?->name ?? '', 0, 1));
                        @endphp
                        @if($caUrl)
                            <img src="{{ $caUrl }}" alt="" class="sc-avatar" loading="lazy">
                        @else
                            <div class="sc-avatar sc-avatar-fb">{{ $caInit ?: '?' }}</div>
                        @endif
                        <div class="sc-comment-bubble">
                            <span class="sc-comment-author">{{ $ca?->prenoms }} {{ $ca?->name ?? '—' }}</span>
                            {{ $com->contenu }}
                        </div>
                    </div>
                    @endforeach

                    <form action="{{ route('social.posts.comment', $post) }}" method="POST" class="sc-comment-form">
                        @csrf
                        <input type="text" name="contenu" id="sc-comm-{{ $post->id }}" placeholder="Écrire un commentaire…" maxlength="1000" required>
                        <button type="submit">Envoyer</button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="sc-card">
            <div class="empty-state" style="padding:3rem 1rem; text-align:center;">
                <i class="fas fa-users" style="font-size:2.5rem; color:#CBD5E1; margin-bottom:.75rem; display:block;"></i>
                <p style="color:#64748B; margin:0;">Aucune publication{{ $filter === 'mine' ? ' de votre part' : ($filter === 'liked' ? ' aimée' : '') }} pour l'instant.</p>
                @if($filter !== 'feed')
                <a href="{{ route('social.dashboard') }}" class="btn btn-sm btn-outline-secondary mt-3">Voir le fil d'actualité</a>
                @endif
            </div>
        </div>
        @endforelse

        <div class="d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>

    <aside class="sc-side">
        <div class="sc-side-card mb-3">
            <div class="sc-side-title">
                <i class="fas fa-calendar-days" style="color:#0891B2;"></i> Prochains Event
            </div>
            @forelse($prochainsEvenements as $ev)
            @php
                $badge = $ev->couleur_affichee ?? ($ev->type?->couleur ?? '#0891B2');
                $jourDif = (int) now()->startOfDay()->diffInDays($ev->date_debut->startOfDay(), false);
                $quand = $jourDif === 0 ? "Aujourd'hui" : ($jourDif === 1 ? 'Demain' : $ev->date_debut->isoFormat('ddd D MMM'));
                $heure = $ev->journee_entiere ? '' : ' · '.$ev->date_debut->format('H:i');
            @endphp
            <a href="{{ route('intranet.evenements.show', $ev) }}" class="sc-agenda-row">
                <span class="sc-agenda-dot" style="background:{{ $badge }};"></span>
                <div style="flex:1; min-width:0;">
                    <div class="sc-agenda-title">{{ \Illuminate\Support\Str::limit($ev->titre, 42) }}</div>
                    <div class="sc-agenda-meta">
                        {{ $quand }}{{ $heure }}
                        @if($ev->lieu) · <i class="fas fa-location-dot me-1"></i>{{ \Illuminate\Support\Str::limit($ev->lieu, 20) }}@endif
                        @if($ev->est_visio) · <i class="fas fa-video ms-1" title="Visio"></i>@endif
                    </div>
                </div>
            </a>
            @empty
            <div class="sc-empty"><i class="fas fa-calendar-check me-1"></i>Aucun événement à venir.</div>
            @endforelse
        </div>

        <div class="sc-side-card mb-3">
            <div class="sc-side-title"><i class="fas fa-cake-candles" style="color:#F59E0B;"></i> Anniversaires (7 jours)</div>
            @forelse($anniversaires as $emp)
            @php
                $bday = $emp->date_naissance->copy()->year(now()->year);
                if ($bday->lt(now()->startOfDay())) $bday->addYear();
                $jours = (int) now()->startOfDay()->diffInDays($bday, false);
                $labelDate = $jours === 0 ? "Aujourd'hui" : ($jours === 1 ? 'Demain' : $bday->isoFormat('ddd D MMM'));
                $photoPath = $emp->user?->profile_photo_path;
                $photoUrl = $photoPath && preg_match('#^https?://#', $photoPath) ? $photoPath
                            : ($photoPath ? asset('storage/'.ltrim($photoPath,'/')) : null);
                $initiales = strtoupper(mb_substr($emp->prenoms ?? '',0,1).mb_substr($emp->noms ?? '',0,1));
                $nbWishes = (int) ($wishesData[$emp->id] ?? 0);
                $dejaEnvoye = $wishesDejaEnvoyes->contains($emp->id);
            @endphp
            <div class="sc-bday-row">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="" class="sc-bday-avatar" loading="lazy">
                @else
                    <div class="sc-bday-avatar sc-bday-avatar-fb">{{ $initiales ?: '?' }}</div>
                @endif
                <div style="flex:1; min-width:0;">
                    <div class="sc-bday-name">
                        @if($jours === 0)🎂 @endif{{ $emp->prenoms }} {{ $emp->noms }}
                    </div>
                    <div class="sc-bday-meta">{{ $labelDate }}@if($nbWishes > 0) · <i class="fas fa-heart" style="color:#F59E0B;"></i> {{ $nbWishes }}@endif</div>
                </div>
                @if($dejaEnvoye)
                    <span class="sc-bday-sent" title="Vœux envoyés"><i class="fas fa-check"></i></span>
                @else
                    <button type="button" class="sc-bday-wish-btn" data-bs-toggle="modal" data-bs-target="#scWish-{{ $emp->id }}" title="Souhaiter">🎂</button>
                @endif
            </div>
            @empty
            <div class="sc-empty">Aucun anniversaire dans les 7 jours.</div>
            @endforelse
        </div>

        <div class="sc-side-card mb-3">
            <div class="sc-side-title">
                <i class="fas fa-list-check" style="color:#2563EB;"></i>
                Tâches à faire
                @if($mesTaches->count())
                <span style="font-size:.65rem; color:#94A3B8; font-weight:500;">({{ $mesTaches->count() }})</span>
                @endif
            </div>
            @forelse($mesTaches as $t)
            @php
                $couleurStatut = $t->statut?->couleur ?? '#64748B';
                $couleurPrio   = $t->priorite?->couleur ?? '#94A3B8';
                $enRetard = $t->date_fin && $t->date_fin->isPast();
                $dateLbl = $t->date_fin ? $t->date_fin->format('d/m') : null;
            @endphp
            <a href="{{ route('intranet.taches.show', $t) }}" class="sc-task-row">
                <span class="sc-task-dot" style="background:{{ $couleurPrio }};" title="{{ $t->priorite?->libelle ?? 'Priorité' }}"></span>
                <div style="flex:1; min-width:0;">
                    <div class="sc-task-title">{{ \Illuminate\Support\Str::limit($t->titre, 42) }}</div>
                    <div class="sc-task-meta">
                        @if($t->projet)<i class="fas fa-diagram-project me-1"></i>{{ \Illuminate\Support\Str::limit($t->projet->nom, 22) }}@endif
                        @if($dateLbl)
                            · @if($enRetard)<span style="color:#DC2626; font-weight:600;"><i class="fas fa-triangle-exclamation me-1"></i>{{ $dateLbl }}</span>
                              @else<i class="fas fa-calendar-day me-1"></i>{{ $dateLbl }}
                              @endif
                        @endif
                    </div>
                </div>
                @if($t->statut)
                <span class="sc-task-pill" style="background:{{ $couleurStatut }}22; color:{{ $couleurStatut }};">
                    {{ \Illuminate\Support\Str::limit($t->statut->libelle, 8) }}
                </span>
                @endif
            </a>
            @empty
            <div class="sc-empty"><i class="fas fa-check-double me-1"></i>Aucune tâche à faire. 🎉</div>
            @endforelse
        </div>

        <div class="sc-side-card">
            <div class="sc-side-title">
                <i class="fas fa-comments" style="color:var(--sc-primary);"></i>
                Activité des groupes
                <a href="{{ route('social.groups.index') }}" style="margin-left:auto; font-size:.7rem; color:var(--sc-primary); text-transform:none; letter-spacing:0; font-weight:600; text-decoration:none;">Tous</a>
            </div>
            @forelse($groupesActifs as $g)
            @php
                $lm = $g->last_message;
                $senderName = trim(($lm?->sender?->prenoms ?? '').' '.($lm?->sender?->name ?? ''));
            @endphp
            <a href="{{ route('social.groups.show', $g) }}" class="sc-group-row">
                @if($g->avatar_url)
                    <img src="{{ $g->avatar_url }}" alt="" class="sc-group-avatar">
                @else
                    <div class="sc-group-avatar sc-group-avatar-fb">{{ strtoupper(mb_substr($g->nom, 0, 2)) }}</div>
                @endif
                <div class="sc-group-body">
                    <div class="sc-group-head">
                        <span class="sc-group-name">{{ \Illuminate\Support\Str::limit($g->nom, 22) }}</span>
                        @if($lm)<span class="sc-group-time">{{ $lm->created_at->diffForHumans(null, ['short' => true]) }}</span>@endif
                    </div>
                    <div class="sc-group-preview">
                        @if($lm)
                            <span style="color:var(--sc-text-soft);">{{ $senderName ? mb_substr($senderName, 0, 15).' : ' : '' }}</span>{{ \Illuminate\Support\Str::limit($lm->contenu, 40) }}
                        @else
                            <span style="font-style:italic;">Aucun message</span>
                        @endif
                    </div>
                </div>
                @if($g->unread_count > 0)
                    <span class="sc-group-badge">{{ $g->unread_count }}</span>
                @endif
            </a>
            @empty
            <div class="sc-empty">
                <i class="fas fa-comment-dots me-1"></i>Aucun groupe pour le moment.<br>
                <a href="{{ route('social.groups.index') }}" style="color:var(--sc-primary); font-weight:600;">Créer un groupe →</a>
            </div>
            @endforelse
        </div>
    </aside>
</div>

{{-- Lightbox zoom média (unique, global au feed) --}}
<div class="sc-lightbox" id="scLightbox" aria-hidden="true">
    <button type="button" class="sc-lb-close" aria-label="Fermer">&times;</button>
    <button type="button" class="sc-lb-nav sc-lb-prev" aria-label="Précédent">&#10094;</button>
    <button type="button" class="sc-lb-nav sc-lb-next" aria-label="Suivant">&#10095;</button>
    <div class="sc-lb-body">
        <div class="sc-lb-stage" id="scLightboxStage"></div>
        <aside class="sc-lb-panel" id="scLightboxPanel">
            <div class="sc-lb-actions">
                <button type="button" class="sc-lb-like" id="scLbLikeBtn">
                    <i class="far fa-heart"></i> <span class="sc-lb-like-count">0</span>
                </button>
                <span class="sc-lb-com-count"><i class="far fa-comment"></i> <span id="scLbComCount">0</span></span>
            </div>
            <div class="sc-lb-comments" id="scLbComments"></div>
            <form class="sc-lb-comment-form" id="scLbCommentForm">
                <input type="text" name="contenu" placeholder="Écrire un commentaire…" maxlength="1000" required>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </aside>
    </div>
    <div class="sc-lb-counter" id="scLightboxCounter"></div>
</div>

{{-- Modales d'édition des posts (fenêtre 8h, auteur uniquement) --}}
@foreach($posts as $editPost)
    @php $ep_editable = auth()->id() === $editPost->created_by && $editPost->created_at->gte(now()->subHours(8)); @endphp
    @if($ep_editable)
    <div class="modal fade" id="scEditModal-{{ $editPost->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('social.posts.update', $editPost) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2"></i>Modifier la publication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2"><i class="fas fa-clock me-1"></i>Modification possible jusqu'à {{ $editPost->created_at->addHours(8)->format('d/m/Y H:i') }}.</p>
                    <div class="sc-edit-editor" data-initial="{{ $editPost->contenu }}"></div>
                    <input type="hidden" name="contenu" value="{{ $editPost->contenu }}">
                    <div class="mt-3">
                        <label class="form-label small text-muted">Ajouter d'autres médias (facultatif)</label>
                        <input type="file" name="medias[]" multiple accept="image/*,video/*" class="form-control form-control-sm">
                    </div>
                    <div class="mt-3">
                        @php $vis = $editPost->publication?->visibilite ?? 'public'; @endphp
                        <label class="form-label small text-muted d-block mb-1">Visibilité</label>
                        <label class="me-3"><input type="radio" name="visibilite" value="public" @checked($vis === 'public')> Public</label>
                        <label><input type="radio" name="visibilite" value="prive" @checked($vis === 'prive')> Cibler</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="sc-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

{{-- Modales de vœux d'anniversaire (hors de l'aside) --}}
@foreach($anniversaires as $emp)
@if(!$wishesDejaEnvoyes->contains($emp->id))
<div class="modal fade" id="scWish-{{ $emp->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('intranet.anniversaires.wish', $emp) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header" style="background:#F5B800; color:#4A3800; border:0;">
                <h5 class="modal-title">🎂 Souhaiter à {{ $emp->prenoms }} {{ $emp->noms }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Message personnalisé (facultatif).</p>
                <textarea name="message" class="form-control" rows="3" maxlength="500" placeholder="Joyeux anniversaire ! 🎂"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="sc-btn-primary"><i class="fas fa-paper-plane me-1"></i>Envoyer</button>
            </div>
        </form>
    </div>
</div>
@endif
@endforeach

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css"
      rel="stylesheet"
      integrity="sha384-VvSC4PGxeMkOaAmyuDGZECjY2dkqdO/IdBYBUK+BCYNc3WIvRxHLUzQ5OSgUaMA7"
      crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css"
      rel="stylesheet"
      integrity="sha384-piG3EtH1fBnPi68q4spy+Qgpb0dHK1D1dwk0GaHwFkvmUxYi526bBlk3xJcjEBsD"
      crossorigin="anonymous">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"
        integrity="sha384-hcxmSutM10NL6iGBAA0LStIhy+kWJxfrhqWVMRuABZH5Vqztexq2nBz/Xnfllly9"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"
        integrity="sha384-cnROoUgVILyibe3J0zhzWoJ9p2WmdnK7j/BOTSWqVDbC1pVw2d+i6Q/1ESKJKCYf"
        crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editorEl = document.getElementById('scEditor');
    if (!editorEl || typeof Quill === 'undefined') return;

    var quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: 'Partagez une actualité, une pensée, un projet…',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'link'],
                ['clean'],
            ],
        },
    });

    var hidden = document.getElementById('scContenu');
    // Restauration de old() via l'API Quill : le parseur ne garde QUE les formats
    // déclarés dans la toolbar — les tags/attributs inconnus (script, on*, style)
    // sont ignorés. Défense en profondeur contre un self-XSS via old('contenu').
    if (hidden.value) { quill.clipboard.dangerouslyPasteHTML(0, hidden.value, 'silent'); }
    quill.on('text-change', function () { hidden.value = quill.root.innerHTML; });

    // Insertion d'emoji au curseur
    document.querySelectorAll('#scEmojiBar .sc-emoji').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var range = quill.getSelection(true);
            quill.insertText(range.index, btn.dataset.emo);
            quill.setSelection(range.index + btn.dataset.emo.length);
        });
    });

    // Refuse la soumission si le contenu est vide (Quill met <p><br></p> par défaut)
    var form = document.getElementById('scComposerForm');
    form.addEventListener('submit', function (e) {
        var text = quill.getText().trim();
        if (!text) { e.preventDefault(); alert('Le contenu ne peut pas être vide.'); }
    });

    // ── Toggle Public / Cibler ──────────────────────────
    var targetPicker = document.getElementById('scTargetPicker');
    document.querySelectorAll('input[name="visibilite"]').forEach(function (r) {
        r.addEventListener('change', function () {
            targetPicker.style.display = r.value === 'prive' && r.checked ? 'block' : 'none';
        });
    });

    // ── Tom Select sur le sélecteur de cibles ──────────
    document.querySelectorAll('.sc-target-select').forEach(function (el) {
        new TomSelect(el, {
            plugins: ['remove_button'],
            maxOptions: 500,
            placeholder: 'Rechercher personnes / groupes / entités…',
        });
    });

    // ── Preview multi-uploads ───────────────────────────
    var mediasInput = document.getElementById('scMedias');
    var previewGrid = document.getElementById('scPreviewGrid');
    if (mediasInput && previewGrid) {
        var currentFiles = new DataTransfer();
        mediasInput.addEventListener('change', function (e) {
            Array.from(mediasInput.files).forEach(function (f) { currentFiles.items.add(f); });
            mediasInput.files = currentFiles.files;
            renderPreviews();
        });

        function renderPreviews() {
            previewGrid.innerHTML = '';
            Array.from(currentFiles.files).forEach(function (f, idx) {
                var tile = document.createElement('div');
                tile.className = 'sc-preview-tile';
                var isVideo = f.type.startsWith('video/');
                var media = document.createElement(isVideo ? 'video' : 'img');
                media.src = URL.createObjectURL(f);
                if (isVideo) { media.muted = true; }
                var rm = document.createElement('button');
                rm.type = 'button';
                rm.className = 'sc-preview-rm';
                rm.title = 'Retirer';
                rm.innerHTML = '&times;';
                rm.addEventListener('click', function () {
                    var dt = new DataTransfer();
                    Array.from(currentFiles.files).forEach(function (f2, i) { if (i !== idx) dt.items.add(f2); });
                    currentFiles = dt;
                    mediasInput.files = currentFiles.files;
                    renderPreviews();
                });
                tile.appendChild(media);
                tile.appendChild(rm);
                previewGrid.appendChild(tile);
            });
        }
    }

    // ── Lightbox zoom média ─────────────────────────────
    (function () {
        var lb = document.getElementById('scLightbox');
        if (!lb) return;
        var stage = document.getElementById('scLightboxStage');
        var counter = document.getElementById('scLightboxCounter');

        // Le contenu de #scLightboxPanel est reconstruit à chaque renderPanel() —
        // les refs sont récupérées via document.getElementById dans les handlers.
        var current = null;

        var CSRF = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value || '';

        var URL_LIKE = "{{ url('/social/media') }}/__ID__/like";
        var URL_COMMENT = "{{ url('/social/media') }}/__ID__/comment";

        function renderMedia() {
            var m = current.items[current.idx];
            stage.textContent = '';
            var el;
            if (m.type === 'video') { el = document.createElement('video'); el.controls = true; el.autoplay = true; }
            else { el = document.createElement('img'); el.alt = ''; }
            el.src = m.url;
            stage.appendChild(el);
            counter.textContent = (current.idx + 1) + ' / ' + current.items.length;
            counter.style.display = current.items.length > 1 ? 'block' : 'none';
            var showNav = current.items.length > 1 ? '' : 'none';
            lb.querySelector('.sc-lb-prev').style.display = showNav;
            lb.querySelector('.sc-lb-next').style.display = showNav;
            renderPanel();
        }
        function renderPanel() {
            try {
                var m = current.items[current.idx];
                var panel = document.getElementById('scLightboxPanel');
                if (!m.id) { panel.style.display = 'none'; return; }
                panel.style.display = 'flex';

                var likes = m.likes || 0;
                var comCount = m.comments_count || 0;
                var comments = Array.isArray(m.comments) ? m.comments : [];
                var heartClass = m.liked ? 'fas fa-heart' : 'far fa-heart';
                var likedCls = m.liked ? ' is-liked' : '';

                // Reconstruction complète du panel — élimine toute stale ref
                panel.innerHTML =
                    '<div class="sc-lb-actions">' +
                        '<button type="button" class="sc-lb-like' + likedCls + '" id="scLbLikeBtn">' +
                            '<i class="' + heartClass + '"></i> ' +
                            '<span class="sc-lb-like-count">' + likes + '</span>' +
                        '</button>' +
                        '<span class="sc-lb-com-count">' +
                            '<i class="far fa-comment"></i> <span id="scLbComCount">' + comCount + '</span>' +
                        '</span>' +
                    '</div>' +
                    '<div class="sc-lb-comments" id="scLbComments"></div>' +
                    '<form class="sc-lb-comment-form" id="scLbCommentForm">' +
                        '<input type="text" name="contenu" placeholder="Écrire un commentaire…" maxlength="1000" required>' +
                        '<button type="submit"><i class="fas fa-paper-plane"></i></button>' +
                    '</form>';

                // Injecte les commentaires (DOM safe, buildCommentNode utilise déjà des nodes)
                var _commentsEl = document.getElementById('scLbComments');
                if (!comments.length) {
                    var e = document.createElement('div');
                    e.className = 'sc-lb-empty'; e.textContent = 'Aucun commentaire pour le moment.';
                    _commentsEl.appendChild(e);
                } else {
                    comments.forEach(function (c) { _commentsEl.appendChild(buildCommentNode(c)); });
                    _commentsEl.scrollTop = _commentsEl.scrollHeight;
                }

                // Re-attache les handlers sur les nouveaux boutons
                attachPanelHandlers();
            } catch (err) {
                console.error('[Social] renderPanel error:', err);
            }
        }

        // Handlers attachés à chaque render (car innerHTML remplace les éléments)
        function attachPanelHandlers() {
            var btn = document.getElementById('scLbLikeBtn');
            if (btn) btn.addEventListener('click', onLikeClick);
            var form = document.getElementById('scLbCommentForm');
            if (form) form.addEventListener('submit', onCommentSubmit);
        }

        function onLikeClick() {
            if (!current) return;
            var m = current.items[current.idx];
            if (!m.id) return;
            var btn = document.getElementById('scLbLikeBtn');
            if (btn) btn.disabled = true;
            fetch(URL_LIKE.replace('__ID__', m.id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            }).then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                var ct = (r.headers.get('Content-Type') || '').toLowerCase();
                if (ct.indexOf('application/json') === -1) throw new Error('Response non-JSON');
                return r.json();
            }).then(function (data) {
                m.liked = !!data.liked;
                m.likes = typeof data.total === 'number' ? data.total : (m.likes || 0);
                persistGalleryState();
                renderPanel();
            }).catch(function (err) {
                console.error('[Social] like échec:', err);
                alert('Erreur lors de l\'enregistrement du like : ' + err.message);
                if (btn) btn.disabled = false;
            });
        }

        function onCommentSubmit(e) {
            e.preventDefault();
            if (!current) return;
            var m = current.items[current.idx];
            if (!m.id) return;
            var form = e.target;
            var input = form.querySelector('input');
            var submitBtn = form.querySelector('button');
            var value = input.value.trim();
            if (!value) return;
            var fd = new FormData();
            fd.append('contenu', value);
            fd.append('_token', CSRF);
            submitBtn.disabled = true;
            fetch(URL_COMMENT.replace('__ID__', m.id), {
                method: 'POST',
                body: fd,
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            }).then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                var ct = (r.headers.get('Content-Type') || '').toLowerCase();
                if (ct.indexOf('application/json') === -1) throw new Error('Response non-JSON');
                return r.json();
            }).then(function (data) {
                if (!data.ok) throw new Error('Réponse: ok=false');
                m.comments = (m.comments || []).concat([data.commentaire]);
                m.comments_count = typeof data.total === 'number' ? data.total : ((m.comments_count || 0) + 1);
                persistGalleryState();
                renderPanel();
            }).catch(function (err) {
                console.error('[Social] commentaire échec:', err);
                alert('Erreur lors de l\'envoi du commentaire : ' + err.message);
                submitBtn.disabled = false;
            });
        }
        function buildCommentNode(c) {
            var wrap = document.createElement('div'); wrap.className = 'sc-lb-com';
            var auth = document.createElement('div'); auth.className = 'sc-lb-com-auth'; auth.textContent = c.auteur || '—';
            // c.contenu est DÉJÀ échappé HTML côté serveur (e()) ; on l'insère via innerHTML pour
            // les <br> éventuels, mais aucun tag actif ne peut survivre à l'échappement.
            var txt = document.createElement('div'); txt.className = 'sc-lb-com-txt'; txt.innerHTML = c.contenu || '';
            var d = document.createElement('div'); d.className = 'sc-lb-com-date'; d.textContent = c.date || '';
            wrap.appendChild(auth); wrap.appendChild(txt); wrap.appendChild(d);
            return wrap;
        }
        function open(items, idx, galleryEl) {
            current = {
                items: items.map(function (x) { return Object.assign({}, x, { comments: x.comments ? x.comments.slice() : [] }); }),
                idx: idx,
                galleryEl: galleryEl || null,
            };
            renderMedia();
            lb.classList.add('is-open');
            lb.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        // Persiste l'état courant vers l'attribut data-gallery de la galerie source,
        // pour qu'une fermeture/réouverture sans reload garde les likes/commentaires ajoutés.
        function persistGalleryState() {
            if (!current || !current.galleryEl) return;
            try {
                current.galleryEl.dataset.gallery = JSON.stringify(current.items);
            } catch (_) {}
        }
        function close() {
            lb.classList.remove('is-open');
            lb.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            stage.textContent = '';
            current = null;
        }
        function nav(dir) {
            if (!current) return;
            current.idx = (current.idx + dir + current.items.length) % current.items.length;
            renderMedia();
        }

        // Ouverture via délégation
        document.body.addEventListener('click', function (e) {
            var btn = e.target.closest('.sc-media-item');
            if (!btn) return;
            var gallery = btn.closest('.sc-media-gallery');
            var data = gallery?.dataset.gallery;
            if (!data) return;
            e.preventDefault();
            try {
                var items = JSON.parse(data);
                var idx = parseInt(btn.dataset.idx || '0', 10);
                open(items, idx, gallery);
            } catch (_) {}
        });

        // Fermeture / navigation
        lb.querySelector('.sc-lb-close').addEventListener('click', close);
        lb.querySelector('.sc-lb-prev').addEventListener('click', function (e) { e.stopPropagation(); nav(-1); });
        lb.querySelector('.sc-lb-next').addEventListener('click', function (e) { e.stopPropagation(); nav(1); });
        lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
        document.addEventListener('keydown', function (e) {
            if (!lb.classList.contains('is-open')) return;
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowRight' && e.target.tagName !== 'INPUT') nav(1);
            if (e.key === 'ArrowLeft' && e.target.tagName !== 'INPUT') nav(-1);
        });

        // NOTE : les handlers like/commentaire sont attachés dans renderPanel() via
        // attachPanelHandlers() — ceci parce que le contenu du panel est reconstruit
        // via innerHTML à chaque render, ce qui remplace les nodes DOM.
    })();

    // ── Quill sur les modales d'édition ─────────────────
    document.querySelectorAll('.sc-edit-editor').forEach(function (el) {
        var editorInstance = new Quill(el, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['blockquote', 'link'],
                    ['clean'],
                ],
            },
        });
        var initial = el.dataset.initial || '';
        if (initial) editorInstance.clipboard.dangerouslyPasteHTML(0, initial, 'silent');
        var hiddenInput = el.parentElement.querySelector('input[name="contenu"]');
        editorInstance.on('text-change', function () { hiddenInput.value = editorInstance.root.innerHTML; });
    });
});
</script>
@endpush
@endsection
