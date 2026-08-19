@extends('layouts.app')

@section('title', 'Nouvelle déclaration sociale')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.declarations-sociales.index') }}">Déclarations sociales</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Générer une déclaration sociale</h1>
    <a href="{{ route('rh.declarations-sociales.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('rh.declarations-sociales.store') }}" method="POST" id="formDecl">
    @csrf
    <div class="card data-card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Organisme <span class="text-danger">*</span></label>
                    <select name="type_organisme" id="type_organisme" class="form-select" required>
                        <option value="">— Sélectionner —</option>
                        @foreach(\App\Models\DeclarationSociale::ORGANISMES as $code => $cfg)
                            <option value="{{ $code }}" data-periodicite="{{ $cfg['periodicite'] }}">
                                {{ $cfg['libelle'] }} ({{ $cfg['periodicite'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Année <span class="text-danger">*</span></label>
                    <input type="number" name="annee" class="form-control" value="{{ now()->year }}" min="2020" max="2100" required>
                </div>
                <div class="col-md-3" id="grpMois">
                    <label class="form-label">Mois</label>
                    <select name="mois" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($m === now()->month)>{{ str_pad($m, 2, '0', STR_PAD_LEFT) }} — {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3 d-none" id="grpTrimestre">
                    <label class="form-label">Trimestre</label>
                    <select name="trimestre" class="form-select">
                        @for($t = 1; $t <= 4; $t++)
                            <option value="{{ $t }}">T{{ $t }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="button" id="btnApercu" class="btn btn-outline-info"><i class="fas fa-eye me-1"></i> Calculer l'aperçu</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Générer la déclaration</button>
            </div>
        </div>
    </div>
</form>

<div id="apercuZone" class="mt-3"></div>

@push('scripts')
<script>
    const sel = document.getElementById('type_organisme');
    const grpMois = document.getElementById('grpMois');
    const grpTrim = document.getElementById('grpTrimestre');

    function adjustPeriode() {
        const opt = sel.options[sel.selectedIndex];
        const period = opt?.dataset.periodicite;
        if (period === 'trimestrielle') {
            grpMois.classList.add('d-none');
            grpTrim.classList.remove('d-none');
        } else {
            grpMois.classList.remove('d-none');
            grpTrim.classList.add('d-none');
        }
    }
    sel.addEventListener('change', adjustPeriode);

    document.getElementById('btnApercu').addEventListener('click', async () => {
        const fd = new FormData(document.getElementById('formDecl'));
        const res = await fetch('{{ route('rh.declarations-sociales.apercu') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: fd,
        });
        const zone = document.getElementById('apercuZone');
        zone.replaceChildren();
        if (!res.ok) {
            const a = document.createElement('div'); a.className = 'alert alert-danger'; a.textContent = 'Erreur de calcul';
            zone.appendChild(a); return;
        }
        const data = await res.json();
        renderApercu(data);
    });

    function fmtFr(n) { return Number(n || 0).toLocaleString('fr-FR'); }
    function td(text, klass) { const e = document.createElement('td'); if (klass) e.className = klass; e.textContent = text ?? ''; return e; }
    function th(text, klass) { const e = document.createElement('th'); if (klass) e.className = klass; e.textContent = text ?? ''; return e; }

    function renderApercu(data) {
        const t = data.totaux;
        const zone = document.getElementById('apercuZone');
        zone.replaceChildren();

        const card = document.createElement('div'); card.className = 'card data-card';
        const head = document.createElement('div'); head.className = 'card-header bg-info text-white';
        const strong = document.createElement('strong'); strong.textContent = 'Aperçu';
        head.append(strong, ' — ', String(data.periode.type_organisme).toUpperCase(),
            ' · ', data.periode.date_debut, ' → ', data.periode.date_fin);
        card.appendChild(head);

        const body = document.createElement('div'); body.className = 'card-body';
        const summary = document.createElement('div'); summary.className = 'row g-3 mb-3';
        const labels = [
            [`${t.nombre_employes} employés`],
            ['Brut : ', fmtFr(t.total_brut)],
            ['Brut plafonné : ', fmtFr(t.total_brut_plafonne)],
            ['Cot. sal. : ', fmtFr(t.total_cot_salariale)],
            ['Cot. pat. : ', fmtFr(t.total_cot_patronale)],
        ];
        labels.forEach(parts => {
            const col = document.createElement('div'); col.className = 'col';
            parts.forEach((p, i) => {
                if (i === 1) { const s = document.createElement('strong'); s.textContent = p; col.appendChild(s); }
                else col.append(p);
            });
            summary.appendChild(col);
        });
        body.appendChild(summary);

        const wrap = document.createElement('div'); wrap.className = 'table-responsive';
        const table = document.createElement('table'); table.className = 'table table-sm';
        const thead = document.createElement('thead'); const trh = document.createElement('tr');
        ['Matricule', 'Nom', 'Mat. organisme', 'Jours', 'Brut', 'Plafonné', 'Cot. sal.', 'Cot. pat.']
            .forEach((h, i) => trh.appendChild(th(h, i >= 3 ? 'text-end' : '')));
        thead.appendChild(trh); table.appendChild(thead);

        const tbody = document.createElement('tbody');
        if (!data.lignes || !data.lignes.length) {
            const tr = document.createElement('tr');
            const ttd = document.createElement('td'); ttd.colSpan = 8; ttd.className = 'text-center text-muted';
            ttd.textContent = 'Aucun bulletin trouvé sur la période';
            tr.appendChild(ttd); tbody.appendChild(tr);
        } else {
            data.lignes.forEach(l => {
                const tr = document.createElement('tr');
                tr.appendChild(td(l.matricule_employeur || '—'));
                tr.appendChild(td(`${l.noms || ''} ${l.prenoms || ''}`.trim()));
                tr.appendChild(td(l.matricule_organisme || '—'));
                tr.appendChild(td(l.nb_jours_travailles, 'text-end'));
                tr.appendChild(td(fmtFr(l.brut), 'text-end'));
                tr.appendChild(td(fmtFr(l.brut_plafonne), 'text-end'));
                tr.appendChild(td(fmtFr(l.cot_salariale), 'text-end'));
                tr.appendChild(td(fmtFr(l.cot_patronale), 'text-end'));
                tbody.appendChild(tr);
            });
        }
        table.appendChild(tbody);
        wrap.appendChild(table); body.appendChild(wrap); card.appendChild(body);
        zone.appendChild(card);
    }
</script>
@endpush
@endsection
