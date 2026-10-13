{{-- Styles communs aux vues des discussions de groupe (mobile d'abord). Inclus une seule fois. --}}
@once
@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --sg-encre: #1E1B4B;      /* titres, texte fort */
    --sg-violet: #6D28D9;     /* action principale */
    --sg-indigo: #4F46E5;
    --sg-brume: #F5F3FF;      /* surfaces teintées */
    --sg-trait: #E9E6F5;      /* séparateurs */
    --sg-gris: #64748B;       /* texte secondaire */
    --sg-menthe: #10B981;     /* non-lus, activité */
}
.sg-titre { font-family: 'Sora', 'Inter', sans-serif; font-weight: 700; letter-spacing: -.02em; color: var(--sg-encre); }

/* ── Pastille d'identité de groupe ── */
.sg-pastille { --s: 48px; position: relative; width: var(--s); height: var(--s); flex-shrink: 0; display: inline-flex; }
.sg-pastille--lg { --s: 64px; }
.sg-pastille--sm { --s: 38px; }
.sg-pastille-ini, .sg-pastille-img {
    width: 100%; height: 100%; border-radius: 30%;
    background: linear-gradient(145deg, color-mix(in srgb, var(--teinte) 78%, #fff), var(--teinte));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-family: 'Sora', sans-serif; font-weight: 700; font-size: calc(var(--s) * .34); letter-spacing: -.02em;
    object-fit: cover; box-shadow: 0 6px 16px -8px var(--teinte);
}
.sg-visages { position: absolute; right: -6px; bottom: -6px; display: flex; }
.sg-visages > * {
    width: calc(var(--s) * .44); height: calc(var(--s) * .44); border-radius: 50%; border: 2px solid #fff;
    margin-left: calc(var(--s) * -.2); background: var(--sg-encre); color: #fff; object-fit: cover;
    display: flex; align-items: center; justify-content: center; font-size: calc(var(--s) * .22); font-weight: 700; line-height: 1;
}
.sg-visages > *:first-child { margin-left: 0; }
.sg-actif { position: absolute; top: -3px; right: -3px; width: 13px; height: 13px; border-radius: 50%; background: var(--sg-menthe); border: 2.5px solid #fff; }
.sg-actif::after { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 2px solid var(--sg-menthe); opacity: 0; animation: sg-pouls 2.2s ease-out infinite; }
@keyframes sg-pouls { 0% { transform: scale(.6); opacity: .7; } 100% { transform: scale(1.5); opacity: 0; } }
@media (prefers-reduced-motion: reduce) { .sg-actif::after { animation: none; } }

/* ── Boutons ── */
.sg-btn { display: inline-flex; align-items: center; gap: .45rem; border: 0; border-radius: 999px; padding: .6rem 1.1rem; font-weight: 600; font-size: .86rem; text-decoration: none; transition: transform .15s, box-shadow .15s, background .15s; }
.sg-btn-primaire { background: linear-gradient(135deg, var(--sg-violet), var(--sg-indigo)); color: #fff; box-shadow: 0 8px 20px -10px var(--sg-indigo); }
.sg-btn-primaire:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 10px 24px -10px var(--sg-indigo); }
.sg-btn-doux { background: var(--sg-brume); color: var(--sg-violet); }
.sg-btn-doux:hover { background: #EDE9FE; color: var(--sg-violet); }
.sg-btn:focus-visible, .sg-ligne:focus-visible, .sg-carte:focus-visible, .sg-onglet:focus-visible { outline: 3px solid color-mix(in srgb, var(--sg-violet) 45%, transparent); outline-offset: 2px; }
</style>
@endpush
@endonce
