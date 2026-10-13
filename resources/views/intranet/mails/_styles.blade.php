@push('styles')
<style>
.wm-wrap { display:grid; grid-template-columns:230px 1fr; gap:1rem; align-items:start; }
.wm-side { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); padding:.85rem; position:sticky; top:1rem; }
.wm-side .form-select { font-size:.8rem; }
.wm-folders { list-style:none; margin:.75rem 0 0; padding:0; }
.wm-folders a { display:flex; align-items:center; gap:.6rem; padding:.45rem .6rem; border-radius:8px; color:#475569; text-decoration:none; font-size:.83rem; }
.wm-folders a:hover { background:#F1F5F9; color:#1E293B; }
.wm-folders a.active { background:#EEF2FF; color:#4338CA; font-weight:600; }
.wm-folders i { width:16px; text-align:center; font-size:.8rem; }
.wm-main { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); min-width:0; }
.wm-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; padding:.75rem 1rem; border-bottom:1px solid #F1F5F9; }
.wm-row { display:grid; grid-template-columns:22px 210px 1fr auto; gap:.75rem; align-items:center; padding:.6rem 1rem; border-bottom:1px solid #F1F5F9; color:#475569; text-decoration:none; font-size:.84rem; }
.wm-row:hover { background:#F8FAFC; color:#1E293B; }
.wm-row.unread { color:#0F172A; font-weight:600; background:#FCFCFF; }
.wm-row.unread .wm-from::before { content:''; display:inline-block; width:7px; height:7px; border-radius:50%; background:#4F46E5; margin-right:.4rem; vertical-align:middle; }
.wm-from, .wm-subject { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.wm-date { font-size:.74rem; color:#94A3B8; white-space:nowrap; }
.wm-row.unread .wm-date { color:#4338CA; }
.wm-flag { color:#CBD5E1; font-size:.75rem; }
.wm-prio { display:inline-flex; align-items:center; justify-content:center; width:18px; height:18px; border-radius:50%; font-size:.6rem; margin-right:.25rem; vertical-align:middle; }
.wm-tag { display:inline-flex; align-items:center; font-size:.68rem; font-weight:500; background:#F1F5F9; color:#475569; border-radius:999px; padding:.05rem .45rem; margin-left:.3rem; vertical-align:middle; }
.wm-tag.retard { background:#FEE2E2; color:#B91C1C; }
.wm-folders .wm-compteur { margin-left:auto; font-size:.7rem; background:#FEE2E2; color:#B91C1C; border-radius:999px; padding:0 .45rem; font-weight:600; }
.wm-flag.on { color:#F59E0B; }
@media (max-width: 991.98px) {
    .wm-wrap { grid-template-columns:1fr; }
    .wm-side { position:static; }
    .wm-folders { display:flex; flex-wrap:wrap; gap:.25rem; }
    .wm-row { grid-template-columns:18px 1fr auto; }
    .wm-row .wm-subject { grid-column:2 / -1; font-weight:inherit; }
}
</style>
@endpush
