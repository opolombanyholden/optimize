{{-- Éditeur de signature (visuel ou HTML/CSS) avec aperçu, logo redimensionnable et variables.
     Paramètres : $sourceHtml, $sourceCss, $variables (bool : afficher la barre des variables {{…}}).
     Champs envoyés : source_html, source_css. Initialisé par initEditeurSignature() (cf. @once en bas). --}}
@php $aCss = trim((string) $sourceCss) !== '' || preg_match('/<table|class=/i', (string) $sourceHtml); @endphp
<div class="sig-ed" data-sig-editeur data-mode="{{ $aCss ? 'code' : 'visuel' }}">
    <input type="hidden" name="source_html" value="{{ $sourceHtml }}">

    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <div class="btn-group btn-group-sm" role="group" aria-label="Mode d'édition">
            <button type="button" class="btn btn-outline-secondary" data-mode-btn="visuel"><i class="fas fa-pen-nib me-1"></i>Visuel</button>
            <button type="button" class="btn btn-outline-secondary" data-mode-btn="code"><i class="fas fa-code me-1"></i>HTML / CSS</button>
        </div>
        <label class="btn btn-sm btn-outline-primary mb-0"><i class="fas fa-image me-1"></i>Insérer un logo
            <input type="file" accept="image/png,image/jpeg,image/gif,image/webp" hidden data-logo-input>
        </label>
        @if($variables)
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-tag me-1"></i>Variable</button>
                <ul class="dropdown-menu">
                    @foreach(\App\Services\Mail\Signature::VARIABLES as $cle => $lib)
                        <li><button type="button" class="dropdown-item" data-variable="{{ $cle }}">{{ $lib }} <code class="ms-1">{{ '{'.'{'.$cle.'}'.'}' }}</code></button></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Mode visuel --}}
    <div class="sig-ed-visuel border rounded" data-pane="visuel"><div class="sig-editor"></div></div>

    {{-- Mode HTML / CSS --}}
    <div data-pane="code">
        <div class="row g-2">
            <div class="col-md-7">
                <label class="form-label small mb-1">HTML</label>
                <textarea class="form-control sig-ed-code" rows="9" spellcheck="false" data-code-html></textarea>
            </div>
            <div class="col-md-5">
                <label class="form-label small mb-1">CSS <span class="text-muted">(converti en styles en ligne)</span></label>
                <textarea class="form-control sig-ed-code" name="source_css" rows="9" spellcheck="false" placeholder=".nom { color:#1E293B; font-weight:700; }">{{ $sourceCss }}</textarea>
            </div>
        </div>
    </div>

    {{-- Images : largeur réglable --}}
    <div class="sig-ed-images mt-2 d-none" data-images></div>

    <div class="mt-2">
        <div class="small text-muted mb-1"><i class="fas fa-eye me-1"></i>Aperçu{{ $variables ? ' (avec des valeurs d\'exemple)' : '' }}</div>
        <iframe class="sig-ed-apercu" sandbox title="Aperçu de la signature" data-apercu></iframe>
    </div>
</div>

@once
@push('styles')
<style>
.sig-ed [data-pane] { display:none; }
.sig-ed[data-mode="visuel"] [data-pane="visuel"], .sig-ed[data-mode="code"] [data-pane="code"] { display:block; }
.sig-ed .sig-editor { min-height:160px; font-size:.9rem; }
.sig-ed-code { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:.78rem; line-height:1.45; tab-size:2; }
.sig-ed-apercu { width:100%; height:170px; border:1px solid #E2E8F0; border-radius:8px; background:#fff; }
.sig-ed-images { display:flex; flex-direction:column; gap:.4rem; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; padding:.5rem .65rem; }
.sig-ed-img { display:flex; align-items:center; gap:.65rem; font-size:.78rem; }
.sig-ed-img img { width:40px; height:40px; object-fit:contain; background:#fff; border:1px solid #E2E8F0; border-radius:4px; }
.sig-ed-img input[type=range] { flex:1; max-width:260px; }
.sig-ed-img input[type=number] { width:80px; }
</style>
@endpush

@push('scripts')
<script>
/**
 * Éditeur de signature : bascule visuel ⇄ HTML/CSS, aperçu sandboxé, logo inséré et
 * réduit côté navigateur (≤ 600 px, ~150 Ko), largeur des images réglable, variables entre doubles accolades.
 */
window.initEditeurSignature = function (root) {
    if (root._sigEd) return root._sigEd;
    const champHtml = root.querySelector('[name="source_html"]');
    const champCss = root.querySelector('[name="source_css"]');
    const codeHtml = root.querySelector('[data-code-html]');
    const apercu = root.querySelector('[data-apercu]');
    const zoneImages = root.querySelector('[data-images]');
    const EXEMPLE = { prenom: 'Aline', nom: 'NZE', nom_complet: 'Aline NZE', poste: 'Chargée des ressources humaines', email: 'a.nze@entreprise.ga', telephone: '+241 01 23 45 67', matricule: 'MAT-0042', bureau: 'Bât. A — 2e étage', linkedin: 'https://www.linkedin.com/in/exemple', entreprise: @json(config('app.name')) };
    let quill = null;

    // Les images data: (logo) sont affichées dans le code sous un repère court (#image-1)
    // pour garder le HTML lisible ; elles sont restituées pour l'aperçu et l'enregistrement.
    const blobs = {}; let nbBlobs = 0;
    const compacter = h => (h || '').replace(/data:image\/[a-z+]+;base64,[A-Za-z0-9+\/=]+/g, m => {
        const k = Object.keys(blobs).find(x => blobs[x] === m) || ('#image-' + (++nbBlobs));
        blobs[k] = m; return k;
    });
    const etendre = h => (h || '').replace(/#image-\d+/g, k => blobs[k] || k);
    const codeEtendu = () => etendre(codeHtml.value);

    codeHtml.value = compacter(champHtml.value);

    function initQuill() {
        if (quill) return;
        quill = new Quill(root.querySelector('.sig-editor'), {
            theme: 'snow',
            placeholder: 'Prénom Nom — Fonction\nEntreprise · Téléphone · site web',
            modules: { toolbar: [['bold', 'italic', 'underline'], [{ color: [] }], [{ size: ['small', false, 'large'] }], [{ align: [] }], ['link'], ['clean']] }
        });
        if (codeHtml.value) quill.clipboard.dangerouslyPasteHTML(codeEtendu());
        quill.on('text-change', maj);
    }

    const mode = () => root.dataset.mode;
    const htmlCourant = () => mode() === 'visuel' && quill ? (quill.getLength() > 1 || quill.root.querySelector('img') ? quill.getSemanticHTML() : '') : codeEtendu();

    function basculer(m) {
        if (m === mode() && (m === 'code' || quill)) return majBoutons();
        if (m === 'code') {
            if (quill) codeHtml.value = compacter(htmlCourant());
        } else {
            if (/<table|class=|<style/i.test(codeHtml.value) && !confirm("Le mode visuel ne conserve pas les tableaux ni les classes CSS. Continuer ?")) return;
            if (quill) quill.clipboard.dangerouslyPasteHTML(codeEtendu());
        }
        root.dataset.mode = m;
        if (m === 'visuel') initQuill();
        majBoutons(); maj();
    }
    function majBoutons() {
        root.querySelectorAll('[data-mode-btn]').forEach(b => b.classList.toggle('active', b.dataset.modeBtn === mode()));
    }

    // Aperçu (sandbox sans script) : CSS en <style>, variables remplacées par l'exemple
    function maj() {
        let html = htmlCourant();
        html = html.replace(/\{\{\s*([a-z_]+)\s*\}\}|%7B%7B([a-z_]+)%7D%7D/g, (t, a, b) => EXEMPLE[a || b] ?? t);
        apercu.srcdoc = '<!doctype html><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;font-size:13px;color:#1E293B;margin:8px}p{margin:0}' + (champCss.value || '').replace(/<\/?style/gi, '') + '</style>-- <br>' + html;
        listerImages();
    }

    // ── Images : liste + largeur ─────────────────────────────
    function images() {
        if (mode() === 'visuel' && quill) return [...quill.root.querySelectorAll('img')];
        return [...new DOMParser().parseFromString(codeEtendu(), 'text/html').body.querySelectorAll('img')];
    }
    function largeur(img) {
        const w = parseInt(img.getAttribute('width') || (img.style && img.style.width) || '', 10);
        return isNaN(w) ? 120 : w;
    }
    function definirLargeur(index, w) {
        w = Math.max(16, Math.min(600, parseInt(w, 10) || 120));
        if (mode() === 'visuel' && quill) {
            const img = quill.root.querySelectorAll('img')[index];
            if (img) { Quill.find(img).format('width', String(w)); }
        } else {
            const doc = new DOMParser().parseFromString(codeEtendu(), 'text/html');
            const img = doc.body.querySelectorAll('img')[index];
            if (!img) return;
            img.setAttribute('width', w);
            img.style.width = w + 'px'; img.style.height = 'auto';
            codeHtml.value = compacter(doc.body.innerHTML);
        }
        maj();
    }
    function listerImages() {
        const imgs = images();
        zoneImages.classList.toggle('d-none', imgs.length === 0);
        // Ne reconstruit pas pendant un glissement du curseur
        if (zoneImages.contains(document.activeElement) && zoneImages.children.length === imgs.length) return;
        zoneImages.textContent = '';
        imgs.forEach(function (img, i) {
            const ligne = document.createElement('div'); ligne.className = 'sig-ed-img';
            const vignette = document.createElement('img'); vignette.alt = ''; vignette.src = img.getAttribute('src') || '';
            const lib = document.createElement('span'); lib.textContent = 'Largeur';
            const range = document.createElement('input'); range.type = 'range'; range.min = 16; range.max = 600; range.value = largeur(img); range.className = 'form-range';
            const num = document.createElement('input'); num.type = 'number'; num.min = 16; num.max = 600; num.value = largeur(img); num.className = 'form-control form-control-sm';
            const px = document.createElement('span'); px.textContent = 'px';
            range.addEventListener('input', () => { num.value = range.value; definirLargeur(i, range.value); });
            num.addEventListener('change', () => { range.value = num.value; definirLargeur(i, num.value); });
            ligne.append(vignette, lib, range, num, px); zoneImages.appendChild(ligne);
        });
    }

    // ── Insertion : texte au curseur (variables) et logo ────
    function inserer(texte, html) {
        if (mode() === 'visuel' && quill) {
            const pos = (quill.getSelection(true) || { index: quill.getLength() - 1 }).index;
            if (html) quill.clipboard.dangerouslyPasteHTML(pos, html); else quill.insertText(pos, texte);
        } else {
            const a = codeHtml.selectionStart ?? codeHtml.value.length, b = codeHtml.selectionEnd ?? a;
            const ajout = html ? compacter(html) : texte;
            codeHtml.value = codeHtml.value.slice(0, a) + ajout + codeHtml.value.slice(b);
            codeHtml.focus(); codeHtml.selectionStart = codeHtml.selectionEnd = a + ajout.length;
        }
        maj();
    }
    root.querySelectorAll('[data-variable]').forEach(b => b.addEventListener('click', () => inserer('\u007B\u007B' + b.dataset.variable + '\u007D\u007D')));

    root.querySelector('[data-logo-input]').addEventListener('change', function () {
        const f = this.files[0]; this.value = '';
        if (!f) return;
        if (f.size > 8 * 1048576) { alert('Image trop lourde (8 Mo maximum avant réduction).'); return; }
        const img = new Image();
        img.onload = function () {
            // Réduction : 600 px de large max ; PNG conservé (transparence) sinon JPEG
            const echelle = Math.min(1, 600 / img.width);
            const c = document.createElement('canvas'); c.width = Math.round(img.width * echelle); c.height = Math.round(img.height * echelle);
            c.getContext('2d').drawImage(img, 0, 0, c.width, c.height);
            let url = f.type === 'image/png' || f.type === 'image/gif' ? c.toDataURL('image/png') : c.toDataURL('image/jpeg', 0.88);
            if (url.length > 200000) url = c.toDataURL('image/jpeg', 0.75);
            if (url.length > 250000) { alert('Logo encore trop lourd après réduction : utilisez une image plus simple.'); return; }
            URL.revokeObjectURL(img.src);
            const w = Math.min(160, c.width);
            if (mode() === 'visuel' && quill) {
                const pos = (quill.getSelection(true) || { index: quill.getLength() - 1 }).index;
                quill.insertEmbed(pos, 'image', url, 'user');
                const el = [...quill.root.querySelectorAll('img')].find(i => i.getAttribute('src') === url);
                if (el) Quill.find(el).format('width', String(w));
                maj();
            } else {
                inserer(null, '<img src="' + url + '" alt="Logo" width="' + w + '" style="width:' + w + 'px;height:auto;">');
            }
        };
        img.onerror = () => alert("Image illisible.");
        img.src = URL.createObjectURL(f);
    });

    codeHtml.addEventListener('input', maj);
    champCss.addEventListener('input', maj);
    root.querySelectorAll('[data-mode-btn]').forEach(b => b.addEventListener('click', () => basculer(b.dataset.modeBtn)));

    // Synchronise le champ envoyé
    root.closest('form').addEventListener('submit', function () { champHtml.value = htmlCourant(); });

    if (mode() === 'visuel') initQuill();
    majBoutons(); maj();
    return root._sigEd = { maj, htmlCourant };
};
</script>
@endpush
@endonce
