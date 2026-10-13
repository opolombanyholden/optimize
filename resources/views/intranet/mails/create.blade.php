@extends('layouts.app')

@php
    $titres = ['nouveau' => 'Nouveau message', 'repondre' => 'Répondre', 'repondre-tous' => 'Répondre à tous', 'transferer' => 'Transférer'];
    $titre = $titres[$mode] ?? 'Nouveau message';
    $sigDefaut = $signatures->firstWhere('par_defaut', true);
    $sigChoisie = old('signature_id', $sigDefaut?->id);
    $peutCrm = auth()->user()->can('read:contact');
@endphp

@section('title', $titre.' — Mails')

@section('breadcrumb')
<ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mails.index', ['compte' => $compte->id]) }}">Mails professionnels</a></li>
    <li class="breadcrumb-item active">{{ $titre }}</li>
</ol>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" integrity="sha384-VvSC4PGxeMkOaAmyuDGZECjY2dkqdO/IdBYBUK+BCYNc3WIvRxHLUzQ5OSgUaMA7" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet" integrity="sha384-piG3EtH1fBnPi68q4spy+Qgpb0dHK1D1dwk0GaHwFkvmUxYi526bBlk3xJcjEBsD" crossorigin="anonymous">
<style>
.wm-compose { max-width:980px; margin:0 auto; }
.wm-field { display:grid; grid-template-columns:80px 1fr; align-items:center; border-bottom:1px solid #F1F5F9; padding:.15rem 1rem; }
.wm-field label { font-size:.8rem; color:#64748B; margin:0; }
.wm-field .form-control, .wm-field .form-select { border:0; box-shadow:none; font-size:.88rem; padding-left:0; }
.wm-field .form-control:focus { box-shadow:none; }
.wm-field .ts-wrapper .ts-control { border:0; box-shadow:none; padding-left:0; font-size:.88rem; background:transparent; }
.wm-field .ts-wrapper.multi .ts-control > .item { background:#EEF2FF; color:#3730A3; border:0; border-radius:999px; padding:.1rem .55rem; }
.wm-field .ts-wrapper.multi .ts-control > .item.invalide { background:#FEE2E2; color:#991B1B; }
.ts-dropdown { z-index:10060; font-size:.85rem; }
.ts-dropdown .wm-opt-info { font-size:.72rem; color:#94A3B8; }
#wmEditor { min-height:240px; font-size:.92rem; }
.ql-toolbar.ql-snow { border:0; border-bottom:1px solid #F1F5F9; }
.ql-container.ql-snow { border:0; }

/* Signature */
.wm-sig { margin:0 1rem .75rem; border-top:1px dashed #E2E8F0; padding-top:.6rem; }
.wm-sig-head { display:flex; align-items:center; gap:.5rem; font-size:.78rem; color:#64748B; }
.wm-sig-head .form-select { width:auto; font-size:.78rem; padding-top:.15rem; padding-bottom:.15rem; padding-right:2.25rem; }
.wm-sig-apercu { font-size:.85rem; color:#334155; margin-top:.5rem; }
.wm-sig-apercu p { margin:0; }
.wm-sig-apercu img { max-width:220px; height:auto; }

.wm-citation { margin:0 1rem 1rem; border:1px dashed #CBD5E1; border-radius:8px; }
.wm-citation summary { font-size:.8rem; color:#64748B; padding:.5rem .75rem; cursor:pointer; }
.wm-citation iframe { width:100%; height:320px; border:0; border-top:1px dashed #E2E8F0; }

/* Pièces jointes */
.wm-pj-zone { margin:0 1rem 1rem; border:1.5px dashed #CBD5E1; border-radius:10px; padding:.75rem; transition:background .15s, border-color .15s; }
.wm-pj-zone.survol { background:#EEF2FF; border-color:#6366F1; }
.wm-quota { display:flex; align-items:center; gap:.75rem; font-size:.75rem; color:#64748B; margin-bottom:.6rem; }
.wm-quota .progress { flex:1; height:6px; max-width:260px; }
.wm-quota.alerte .progress-bar { background:#F59E0B; }
.wm-quota.depasse { color:#B91C1C; font-weight:600; }
.wm-quota.depasse .progress-bar { background:#DC2626; }
.wm-pj-liste { display:grid; grid-template-columns:repeat(auto-fill, minmax(230px, 1fr)); gap:.5rem; }
.wm-pj { position:relative; display:flex; gap:.6rem; align-items:center; border:1px solid #E2E8F0; border-radius:8px; padding:.45rem .55rem; background:#fff; min-width:0; }
.wm-pj.erreur { border-color:#FCA5A5; background:#FEF2F2; }
.wm-pj.decoche { opacity:.5; }
.wm-pj-vignette { width:38px; height:38px; border-radius:6px; background:#F1F5F9; color:#64748B; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden; font-size:1rem; }
.wm-pj-vignette img { width:100%; height:100%; object-fit:cover; }
.wm-pj-corps { flex:1; min-width:0; }
.wm-pj-nom { font-size:.78rem; font-weight:600; color:#1E293B; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wm-pj-etat { font-size:.68rem; color:#94A3B8; }
.wm-pj.erreur .wm-pj-etat { color:#B91C1C; }
.wm-pj .progress { height:4px; margin-top:.25rem; }
.wm-pj-retirer { border:0; background:transparent; color:#94A3B8; padding:0 .2rem; }
.wm-pj-retirer:hover { color:#DC2626; }

/* CRM */
.wm-classement { margin:0 1rem 1rem; border:1px solid #E2E8F0; border-radius:10px; padding:.6rem .85rem; }
.wm-classement summary { font-size:.85rem; font-weight:600; color:#1E293B; cursor:pointer; }
.wm-crm { margin:0 1rem 1rem; border:1px solid #E0E7FF; background:#F8FAFF; border-radius:10px; padding:.65rem .8rem; font-size:.8rem; }
.wm-crm-titre { font-weight:600; color:#3730A3; }
.wm-crm .form-check { margin:.2rem 1rem .2rem 0; display:inline-flex; align-items:center; gap:.35rem; }
.wm-crm .type { font-size:.65rem; text-transform:uppercase; letter-spacing:.04em; color:#6366F1; font-weight:700; }
</style>
@endpush

@section('content')
<form action="{{ route('intranet.mails.store') }}" method="POST" enctype="multipart/form-data" class="wm-compose" id="wmForm">
    @csrf
    <input type="hidden" name="html" id="wmHtml">
    <input type="hidden" name="citation" value="{{ old('citation', $brouillon['citation']) }}">
    <input type="hidden" name="en_reponse_a" value="{{ old('en_reponse_a', $brouillon['en_reponse_a']) }}">
    <input type="hidden" name="references" value="{{ old('references', $brouillon['references']) }}">
    @if($origine)
        <input type="hidden" name="origine_uid" value="{{ $origine['uid'] }}">
        <input type="hidden" name="origine_dossier" value="{{ $dossier }}">
    @endif
    <div id="wmPiecesTmp"></div>

    <div class="card data-card">
        <div class="card-header d-flex align-items-center gap-2">
            <h5 class="mb-0 me-auto"><i class="fas fa-pen me-2 text-muted"></i>{{ $titre }}</h5>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('intranet.mails.index', ['compte' => $compte->id]) }}" class="btn btn-sm btn-outline-secondary">Annuler</a>
            <button class="btn btn-sm btn-primary" id="wmSend"><i class="fas fa-paper-plane me-1"></i>Envoyer</button>
        </div>

        <div class="wm-field">
            <label for="wmCompte">De</label>
            <select name="compte_id" id="wmCompte" class="form-select">
                @foreach($comptes as $c)
                    <option value="{{ $c->id }}" @selected((int) old('compte_id', $compte->id) === $c->id)>{{ $c->expediteur }} &lt;{{ $c->email }}&gt;</option>
                @endforeach
            </select>
        </div>
        <div class="wm-field">
            <label for="wmA">À</label>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1"><input type="text" name="a" id="wmA" class="form-control" value="{{ old('a', $brouillon['a']) }}" required placeholder="Nom, email ou contact du CRM…" autocomplete="off"></div>
                <button type="button" class="btn btn-sm btn-link text-decoration-none text-nowrap" id="wmToggleCc">Cc / Cci</button>
            </div>
        </div>
        <div class="wm-field {{ old('cc', $brouillon['cc']) || old('cci') ? '' : 'd-none' }}" data-cc>
            <label for="wmCc">Cc</label>
            <input type="text" name="cc" id="wmCc" class="form-control" value="{{ old('cc', $brouillon['cc']) }}" autocomplete="off">
        </div>
        <div class="wm-field {{ old('cc', $brouillon['cc']) || old('cci') ? '' : 'd-none' }}" data-cc>
            <label for="wmCci">Cci</label>
            <input type="text" name="cci" id="wmCci" class="form-control" value="{{ old('cci') }}" autocomplete="off">
        </div>
        <div class="wm-field">
            <label for="wmSujet">Objet</label>
            <input type="text" name="sujet" id="wmSujet" class="form-control" maxlength="255" value="{{ old('sujet', $brouillon['sujet']) }}">
        </div>

        <div id="wmEditor"></div>

        {{-- ══ Signature (choisie à la rédaction, ajoutée à l'envoi sous le texte) ══ --}}
        <div class="wm-sig">
            <div class="wm-sig-head">
                <i class="fas fa-signature"></i> Signature
                <select name="signature_id" id="wmSignature" class="form-select">
                    <option value="">Aucune</option>
                    @foreach($signatures as $sig)
                        <option value="{{ $sig->id }}" @selected((string) $sigChoisie === (string) $sig->id)>{{ $sig->nom }}{{ $sig->par_defaut ? ' (par défaut)' : '' }}</option>
                    @endforeach
                </select>
                <a href="{{ route('mon-compte.index') }}#signatures" class="ms-auto text-decoration-none" target="_blank" style="font-size:.75rem;"><i class="fas fa-gear me-1"></i>Gérer mes signatures</a>
            </div>
            {{-- Contenus nettoyés par liste blanche à l'enregistrement (App\Support\HtmlSur) --}}
            @foreach($signatures as $sig)
                <div class="wm-sig-apercu {{ (string) $sigChoisie === (string) $sig->id ? '' : 'd-none' }}" data-signature="{{ $sig->id }}">-- <br>{!! $sig->html() !!}</div>
            @endforeach
        </div>

        @if($brouillon['citation'])
            <details class="wm-citation" {{ $mode === 'transferer' ? 'open' : '' }}>
                <summary><i class="fas fa-quote-left me-1"></i>{{ $mode === 'transferer' ? 'Message transféré' : 'Message d\'origine cité' }} (ajouté sous votre signature)</summary>
                {{-- Aperçu isolé : contenu externe, aucun script exécuté --}}
                <iframe sandbox srcdoc="{{ $brouillon['citation'] }}" title="Message cité"></iframe>
            </details>
        @endif

        {{-- ══ Pièces jointes : aperçu, progression, quotas ══ --}}
        <div class="wm-pj-zone" id="wmPjZone">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <label class="btn btn-sm btn-outline-secondary mb-0"><i class="fas fa-paperclip me-1"></i>Joindre des fichiers
                    <input type="file" multiple hidden id="wmFiles">
                </label>
                <span class="text-muted" style="font-size:.75rem;">ou glissez-déposez ici</span>
            </div>
            <div class="wm-quota" id="wmQuota">
                <div class="progress" role="progressbar" aria-label="Taille des pièces jointes"><div class="progress-bar" id="wmQuotaBar" style="width:0%"></div></div>
                <span id="wmQuotaTexte"></span>
            </div>
            <div class="wm-pj-liste" id="wmPjListe">
                @foreach($brouillon['pieces_origine'] as $p)
                    {{-- Pièces du message d'origine (transfert) : décochables --}}
                    <label class="wm-pj" data-origine data-taille="{{ $p['taille'] }}">
                        <span class="wm-pj-vignette"><i class="fas fa-share"></i></span>
                        <span class="wm-pj-corps">
                            <span class="wm-pj-nom d-block" title="{{ $p['nom'] }}">{{ $p['nom'] }}</span>
                            <span class="wm-pj-etat">Pièce d'origine · <span data-taille-txt></span></span>
                        </span>
                        <input class="form-check-input" type="checkbox" name="pieces_origine[]" value="{{ $p['partie'] }}" checked title="Inclure cette pièce">
                    </label>
                @endforeach
            </div>
        </div>

        {{-- ══ Classement : liens vers les modules, priorité, suivi ══ --}}
        @php $ouvrirClassement = $classement['liens'] || $classement['priorite'] !== 'normale' || $classement['categorie'] || $errors->any(); @endphp
        <details class="wm-classement" {{ $ouvrirClassement ? 'open' : '' }}>
            <summary>
                <i class="fas fa-folder-tree me-1"></i>Classement &amp; liens
                <span class="text-muted fw-normal ms-1" style="font-size:.75rem;">projet, tâche, courrier, opportunité, dépense… · priorité · échéance</span>
                @if($classement['liens'])<span class="badge rounded-pill text-bg-primary ms-1">{{ count($classement['liens']) }}</span>@endif
            </summary>
            <div class="pt-2">
                @include('intranet.mails._classement')
                <div class="form-check mt-3">
                    <input type="hidden" name="accuse_lecture" value="0">
                    <input class="form-check-input" type="checkbox" name="accuse_lecture" value="1" id="wmAccuse" @checked(old('accuse_lecture'))>
                    <label class="form-check-label" for="wmAccuse" style="font-size:.82rem;"><i class="fas fa-envelope-circle-check me-1 text-muted"></i>Demander un accusé de lecture</label>
                </div>
                <div class="form-text">La priorité est aussi signalée au destinataire (Haute / Urgente / Basse). La note interne et les liens restent dans OptimiZe.</div>
            </div>
        </details>

        {{-- ══ CRM : historique des fiches correspondant aux destinataires ══ --}}
        @if($peutCrm)
        <div class="wm-crm d-none" id="wmCrm">
            <div class="wm-crm-titre mb-1"><i class="fas fa-address-book me-1"></i>Ajouter ce mail à l'historique CRM</div>
            <div id="wmCrmListe"></div>
        </div>
        @endif
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js" integrity="sha384-hcxmSutM10NL6iGBAA0LStIhy+kWJxfrhqWVMRuABZH5Vqztexq2nBz/Xnfllly9" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js" integrity="sha384-cnROoUgVILyibe3J0zhzWoJ9p2WmdnK7j/BOTSWqVDbC1pVw2d+i6Q/1ESKJKCYf" crossorigin="anonymous"></script>
<script>
(function () {
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const QUOTA = @json($quotas);
    const URLS = {
        piece: @json(route('intranet.mails.pieces.store')),
        destinataires: @json(route('intranet.mails.destinataires')),
        crm: @json(route('intranet.mails.crm-correspondants')),
    };
    const form = document.getElementById('wmForm');
    const btnEnvoyer = document.getElementById('wmSend');
    const mo = n => (n / 1048576).toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + ' Mo';
    const taille = n => n >= 1048576 ? mo(n) : Math.max(1, Math.round(n / 1024)) + ' Ko';

    // ── Éditeur ───────────────────────────────────────────
    const quill = new Quill('#wmEditor', {
        theme: 'snow',
        placeholder: 'Rédigez votre message…',
        modules: { toolbar: [[{ header: [false, 2, 3] }], ['bold', 'italic', 'underline', 'strike'], [{ color: [] }, { background: [] }], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link'], ['clean']] }
    });
    const ancien = @json(old('html'));
    if (ancien) quill.clipboard.dangerouslyPasteHTML(ancien);

    // ── Signature : aperçu de la signature choisie ────────
    document.getElementById('wmSignature').addEventListener('change', function () {
        document.querySelectorAll('[data-signature]').forEach(el => el.classList.toggle('d-none', el.dataset.signature !== this.value));
    });

    // ── Destinataires : autocomplétion CRM (Tom Select) ───
    const estEmail = v => /<?[^\s<>@,;]+@[^\s<>@,;]+\.[^\s<>@,;]+>?\s*$/.test(v);
    const adresse = v => ((v.match(/<([^>]+)>/) || [])[1] || v).trim().toLowerCase();
    const champs = ['wmA', 'wmCc', 'wmCci'].map(function (id) {
        return new TomSelect('#' + id, {
            delimiter: ',', persist: false, create: true, createOnBlur: true, maxOptions: 20,
            valueField: 'valeur', labelField: 'valeur', searchField: ['nom', 'email', 'valeur'],
            dropdownParent: 'body', loadThrottle: 250,
            createFilter: v => estEmail(v),
            shouldLoad: q => q.length >= 2,
            load: function (q, cb) {
                fetch(URLS.destinataires + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                    .then(r => r.ok ? r.json() : []).then(cb).catch(() => cb());
            },
            render: {
                option: (d, esc) => '<div><div>' + esc(d.nom || d.valeur) + '</div><div class="wm-opt-info">' + esc(d.email || '') + (d.info ? ' · ' + esc(d.info) : '') + '</div></div>',
                item: (d, esc) => '<div class="' + (estEmail(d.valeur) ? '' : 'invalide') + '" title="' + esc(adresse(d.valeur)) + '">' + esc(d.nom || d.valeur) + '</div>',
                option_create: (d, esc) => '<div class="create">Ajouter <strong>' + esc(d.input) + '</strong></div>',
                no_results: () => '<div class="no-results">Tapez une adresse email complète</div>',
            },
            onChange: () => majCrm(),
        });
    });
    document.getElementById('wmToggleCc').addEventListener('click', function () {
        document.querySelectorAll('[data-cc]').forEach(el => el.classList.remove('d-none'));
        champs[1].focus();
    });

    // ── CRM : fiches correspondant aux destinataires ──────
    const crmZone = document.getElementById('wmCrm');
    const crmListe = document.getElementById('wmCrmListe');
    const crmCoches = new Set(@json(old('crm_cibles', $crmContexte)));
    let crmTimer = null;
    function majCrm() {
        if (!crmZone) return;
        clearTimeout(crmTimer);
        crmTimer = setTimeout(function () {
            crmListe.querySelectorAll('input:checked').forEach(i => crmCoches.add(i.value));
            crmListe.querySelectorAll('input:not(:checked)').forEach(i => crmCoches.delete(i.value));
            const emails = champs.flatMap(ts => ts.items).map(adresse).filter(e => e.includes('@'));
            const params = new URLSearchParams(); emails.forEach(e => params.append('emails[]', e));
            const contexte = @json($crmContexte);
            fetch(URLS.crm + '?' + params, { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : []).then(function (fiches) {
                    crmListe.textContent = '';
                    fiches.filter(f => f.droit).forEach(function (f) {
                        const id = 'crm-' + f.cle.replace(':', '-');
                        const lab = document.createElement('label'); lab.className = 'form-check'; lab.htmlFor = id;
                        const cb = document.createElement('input'); cb.type = 'checkbox'; cb.className = 'form-check-input'; cb.name = 'crm_cibles[]'; cb.value = f.cle; cb.id = id;
                        // Contacts cochés par défaut, organisations / opportunités au choix
                        cb.checked = crmCoches.has(f.cle) || (f.cle.startsWith('contact:') && !crmCoches.has('!' + f.cle));
                        cb.addEventListener('change', () => { if (!cb.checked) crmCoches.add('!' + f.cle); else crmCoches.delete('!' + f.cle); });
                        const type = document.createElement('span'); type.className = 'type'; type.textContent = f.type;
                        const txt = document.createElement('span'); txt.textContent = f.label + (f.info ? ' · ' + f.info : '');
                        lab.append(cb, type, txt); crmListe.appendChild(lab);
                    });
                    // Fiche d'origine (ouverture depuis le CRM) toujours proposée
                    contexte.filter(c => !fiches.some(f => f.cle === c)).forEach(function (c) {
                        const lab = document.createElement('label'); lab.className = 'form-check';
                        const cb = document.createElement('input'); cb.type = 'checkbox'; cb.className = 'form-check-input'; cb.name = 'crm_cibles[]'; cb.value = c; cb.checked = true;
                        const txt = document.createElement('span'); txt.textContent = 'Fiche CRM d\'origine';
                        lab.append(cb, txt); crmListe.appendChild(lab);
                    });
                    crmZone.classList.toggle('d-none', crmListe.children.length === 0);
                }).catch(() => {});
        }, 400);
    }
    majCrm();

    // ── Pièces jointes : téléversement immédiat, aperçu, progression, quotas ──
    const zone = document.getElementById('wmPjZone');
    const liste = document.getElementById('wmPjListe');
    const champTmp = document.getElementById('wmPiecesTmp');
    const pieces = []; // { id, taille, xhr, el, statut: 'envoi'|'ok'|'erreur' }

    liste.querySelectorAll('[data-origine]').forEach(function (el) {
        el.querySelector('[data-taille-txt]').textContent = taille(+el.dataset.taille);
        el.querySelector('input').addEventListener('change', function () { el.classList.toggle('decoche', !this.checked); majQuota(); });
    });

    function totaux() {
        let octets = 0, nombre = 0;
        pieces.filter(p => p.statut !== 'erreur').forEach(p => { octets += p.taille; nombre++; });
        liste.querySelectorAll('[data-origine]').forEach(el => { if (el.querySelector('input').checked) { octets += +el.dataset.taille; nombre++; } });
        return { octets, nombre };
    }

    function majQuota() {
        const { octets, nombre } = totaux();
        const pct = Math.min(100, octets / QUOTA.total * 100);
        const depasse = octets > QUOTA.total || nombre > QUOTA.nombre;
        const q = document.getElementById('wmQuota');
        q.classList.toggle('depasse', depasse);
        q.classList.toggle('alerte', !depasse && pct >= 80);
        document.getElementById('wmQuotaBar').style.width = pct + '%';
        let txt = mo(octets) + ' / ' + mo(QUOTA.total) + ' · ' + nombre + ' / ' + QUOTA.nombre + ' fichier(s)';
        if (octets > QUOTA.total) txt += ' — taille maximale dépassée';
        else if (nombre > QUOTA.nombre) txt += ' — trop de fichiers';
        else if (octets) txt += ' · reste ' + mo(QUOTA.total - octets);
        document.getElementById('wmQuotaTexte').textContent = txt;
        const enCours = pieces.some(p => p.statut === 'envoi');
        btnEnvoyer.disabled = depasse || enCours;
        btnEnvoyer.title = enCours ? 'Téléversement des pièces jointes en cours…' : (depasse ? 'Quota de pièces jointes dépassé' : '');
    }

    function icone(type) {
        if (type.startsWith('image/')) return 'fa-file-image';
        if (type === 'application/pdf') return 'fa-file-pdf';
        // Types Office : « officedocument » apparaît dans les trois, tester tableur / présentation d'abord
        if (/sheet|excel|csv/.test(type)) return 'fa-file-excel';
        if (/presentation|powerpoint/.test(type)) return 'fa-file-powerpoint';
        if (/word|document/.test(type)) return 'fa-file-word';
        if (/zip|compressed|rar|7z/.test(type)) return 'fa-file-zipper';
        if (type.startsWith('video/')) return 'fa-file-video';
        if (type.startsWith('audio/')) return 'fa-file-audio';
        return 'fa-file';
    }

    function ajouter(fichier) {
        const p = { id: null, taille: fichier.size, xhr: null, statut: 'envoi' };
        const el = document.createElement('div'); el.className = 'wm-pj';
        const vig = document.createElement('div'); vig.className = 'wm-pj-vignette';
        if (fichier.type.startsWith('image/') && fichier.size < 15 * 1048576) {
            const img = document.createElement('img'); img.alt = ''; img.src = URL.createObjectURL(fichier);
            img.onload = () => URL.revokeObjectURL(img.src); vig.appendChild(img);
        } else {
            const i = document.createElement('i'); i.className = 'fas ' + icone(fichier.type || ''); vig.appendChild(i);
        }
        const corps = document.createElement('div'); corps.className = 'wm-pj-corps';
        const nom = document.createElement('div'); nom.className = 'wm-pj-nom'; nom.textContent = fichier.name; nom.title = fichier.name;
        const etat = document.createElement('div'); etat.className = 'wm-pj-etat'; etat.textContent = taille(fichier.size) + ' · en attente…';
        const prog = document.createElement('div'); prog.className = 'progress'; const bar = document.createElement('div'); bar.className = 'progress-bar progress-bar-striped progress-bar-animated'; bar.style.width = '0%'; prog.appendChild(bar);
        corps.append(nom, etat, prog);
        const rm = document.createElement('button'); rm.type = 'button'; rm.className = 'wm-pj-retirer'; rm.title = 'Retirer'; rm.innerHTML = '<i class="fas fa-xmark"></i>';
        el.append(vig, corps, rm); liste.appendChild(el);
        p.el = el; pieces.push(p);

        const echec = function (msg) {
            p.statut = 'erreur'; el.classList.add('erreur'); prog.remove();
            etat.textContent = taille(fichier.size) + ' · ' + msg; majQuota();
        };
        rm.addEventListener('click', function () {
            if (p.xhr && p.statut === 'envoi') p.xhr.abort();
            if (p.id) {
                fetch(URLS.piece + '/' + p.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
                champTmp.querySelector('[value="' + p.id + '"]')?.remove();
            }
            pieces.splice(pieces.indexOf(p), 1); el.remove(); majQuota();
        });

        // Contrôles de quota avant téléversement
        if (fichier.size > QUOTA.fichier) { echec('dépasse ' + mo(QUOTA.fichier) + ' par fichier'); return; }
        const { octets, nombre } = totaux();
        if (nombre > QUOTA.nombre) { echec('maximum ' + QUOTA.nombre + ' fichiers'); return; }
        if (octets > QUOTA.total) { echec('quota de ' + mo(QUOTA.total) + ' dépassé'); return; }

        const fd = new FormData(); fd.append('fichier', fichier);
        const xhr = new XMLHttpRequest(); p.xhr = xhr;
        xhr.open('POST', URLS.piece);
        xhr.setRequestHeader('X-CSRF-TOKEN', CSRF); xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = function (e) {
            if (!e.lengthComputable) return;
            const pct = Math.round(e.loaded / e.total * 100);
            bar.style.width = pct + '%'; etat.textContent = taille(fichier.size) + ' · ' + pct + ' %';
        };
        xhr.onload = function () {
            let rep = {}; try { rep = JSON.parse(xhr.responseText); } catch (e) {}
            if (xhr.status >= 200 && xhr.status < 300 && rep.id) {
                p.id = rep.id; p.statut = 'ok'; prog.remove(); etat.textContent = taille(fichier.size) + ' · prêt';
                const h = document.createElement('input'); h.type = 'hidden'; h.name = 'pieces_tmp[]'; h.value = rep.id; champTmp.appendChild(h);
                majQuota();
            } else {
                echec(rep.message || (rep.errors && Object.values(rep.errors)[0][0]) || (xhr.status === 413 ? 'fichier trop volumineux pour le serveur' : 'échec du téléversement'));
            }
        };
        xhr.onerror = () => echec('connexion interrompue');
        xhr.onabort = () => {};
        xhr.send(fd);
        majQuota();
    }

    document.getElementById('wmFiles').addEventListener('change', function () { [...this.files].forEach(ajouter); this.value = ''; });
    ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('survol'); }));
    ['dragleave', 'drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('survol'); }));
    zone.addEventListener('drop', e => [...(e.dataTransfer?.files || [])].forEach(ajouter));
    majQuota();

    // ── Envoi ─────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        if (pieces.some(p => p.statut === 'envoi')) { e.preventDefault(); return; }
        if (!champs[0].items.length) { e.preventDefault(); alert('Indiquez au moins un destinataire.'); champs[0].focus(); return; }
        document.getElementById('wmHtml').value = quill.getLength() > 1 ? quill.getSemanticHTML() : '';
        btnEnvoyer.disabled = true; btnEnvoyer.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Envoi…';
    });

    (champs[0].items.length ? quill : champs[0]).focus();
})();
</script>
@endpush
