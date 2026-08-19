{{-- ============================================================
     LIGHTBOX réutilisable — à inclure dans toute vue qui contient
     des éléments .lightbox-trigger
     ============================================================ --}}
<div id="lightbox" class="lightbox" role="dialog" aria-hidden="true">
    <button type="button" class="lightbox-close" aria-label="Fermer"><i class="fas fa-xmark"></i></button>
    <button type="button" class="lightbox-zoom-in" aria-label="Zoomer"><i class="fas fa-plus"></i></button>
    <button type="button" class="lightbox-zoom-out" aria-label="Dézoomer"><i class="fas fa-minus"></i></button>
    <button type="button" class="lightbox-zoom-reset" aria-label="Réinitialiser"><i class="fas fa-rotate"></i></button>
    <button type="button" class="lightbox-prev" aria-label="Précédent"><i class="fas fa-chevron-left"></i></button>
    <button type="button" class="lightbox-next" aria-label="Suivant"><i class="fas fa-chevron-right"></i></button>
    <div class="lightbox-stage"><img src="" alt="" class="lightbox-img" id="lightboxImg"></div>
    <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lightbox = document.getElementById('lightbox');
    const lbImg    = document.getElementById('lightboxImg');
    const lbCap    = document.getElementById('lightboxCaption');
    const triggers = document.querySelectorAll('.lightbox-trigger');
    if (!lightbox || triggers.length === 0) return;

    const gallery = Array.from(triggers).map(el => ({
        src: el.dataset.src || el.getAttribute('src'),
        name: el.dataset.name || el.getAttribute('alt') || '',
    }));
    let idx = 0, scale = 1, posX = 0, posY = 0, drag = false, sX = 0, sY = 0;

    function open(i) { idx = (i + gallery.length) % gallery.length; lbImg.src = gallery[idx].src; lbCap.textContent = gallery[idx].name; reset(); lightbox.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function close() { lightbox.classList.remove('open'); document.body.style.overflow = ''; reset(); }
    function reset() { scale = 1; posX = 0; posY = 0; apply(); }
    function apply() { lbImg.style.transform = `translate(${posX}px,${posY}px) scale(${scale})`; lbImg.style.cursor = scale > 1 ? (drag ? 'grabbing' : 'grab') : 'zoom-in'; }
    function zoom(d, cx, cy) { const ns = Math.min(Math.max(scale + d, 1), 5); if (ns === scale) return; if (cx !== undefined) { const r = lbImg.getBoundingClientRect(); posX -= (cx - r.left - r.width/2) * (ns/scale - 1); posY -= (cy - r.top - r.height/2) * (ns/scale - 1); } scale = ns; if (scale === 1) { posX = 0; posY = 0; } apply(); }

    triggers.forEach((el, i) => el.addEventListener('click', e => { e.preventDefault(); open(i); }));
    lightbox.querySelector('.lightbox-close').onclick = close;
    lightbox.querySelector('.lightbox-next').onclick = () => open(idx + 1);
    lightbox.querySelector('.lightbox-prev').onclick = () => open(idx - 1);
    lightbox.querySelector('.lightbox-zoom-in').onclick = () => zoom(0.4);
    lightbox.querySelector('.lightbox-zoom-out').onclick = () => zoom(-0.4);
    lightbox.querySelector('.lightbox-zoom-reset').onclick = reset;
    lbImg.onclick = e => { if (scale === 1) zoom(1, e.clientX, e.clientY); else reset(); };
    lightbox.addEventListener('click', e => { if (e.target === lightbox || e.target.classList.contains('lightbox-stage')) close(); });
    lightbox.addEventListener('wheel', e => { if (lightbox.classList.contains('open')) { e.preventDefault(); zoom(e.deltaY < 0 ? 0.2 : -0.2, e.clientX, e.clientY); } }, { passive: false });
    lbImg.addEventListener('mousedown', e => { if (scale > 1) { drag = true; sX = e.clientX - posX; sY = e.clientY - posY; e.preventDefault(); } });
    window.addEventListener('mousemove', e => { if (drag) { posX = e.clientX - sX; posY = e.clientY - sY; apply(); } });
    window.addEventListener('mouseup', () => { if (drag) { drag = false; apply(); } });
    document.addEventListener('keydown', e => { if (!lightbox.classList.contains('open')) return; if (e.key === 'Escape') close(); if (e.key === 'ArrowRight') open(idx + 1); if (e.key === 'ArrowLeft') open(idx - 1); });
});
</script>
@endpush
