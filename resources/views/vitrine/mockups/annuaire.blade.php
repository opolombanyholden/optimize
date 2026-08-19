<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;">
    @php $colors = ['#7C3AED','#16A34A','#0891B2','#D97706','#DB2777','#0D9488']; @endphp
    @foreach(['Aminata M.','JP Obame','Sylvie M.','Patrick N.','Linda B.','Claude O.'] as $i => $n)
    <div style="background:#FFF;border:1px solid #E2E8F0;border-radius:8px;padding:.65rem;text-align:center;">
        <div style="width:36px;height:36px;border-radius:50%;background:{{ $colors[$i] }};color:#FFF;display:flex;align-items:center;justify-content:center;margin:0 auto .35rem;font-weight:700;font-size:.75rem;">{{ substr($n,0,2) }}</div>
        <div style="font-size:.7rem;font-weight:700;">{{ $n }}</div>
        <div style="font-size:.58rem;color:#94A3B8;">DSI · Manager</div>
    </div>
    @endforeach
</div>
