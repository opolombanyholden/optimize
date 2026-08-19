@extends('layouts.app')

@section('title', 'Grille pointage — ' . $employee->noms . ' ' . $employee->prenoms)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.pointages.index') }}">Pointages</a></li>
        <li class="breadcrumb-item active">Grille {{ $employee->noms }} {{ $employee->prenoms }}</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1">{{ $employee->noms }} {{ $employee->prenoms }}</h1>
        <p class="text-muted mb-0">
            <code>{{ $employee->matricule ?? '—' }}</code> ·
            {{ \Carbon\Carbon::create($annee, $mois, 1)->translatedFormat('F Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.pointages.index', ['mois' => $mois, 'annee' => $annee]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
    </div>
</div>

{{-- Navigation période --}}
@php
    $debut = \Carbon\Carbon::create($annee, $mois, 1);
    $prev  = (clone $debut)->subMonth();
    $next  = (clone $debut)->addMonth();
@endphp
<div class="d-flex justify-content-center align-items-center gap-3 mb-3">
    <a href="{{ route('rh.pointages.grille-employe', ['employee' => $employee->id, 'mois' => $prev->month, 'annee' => $prev->year]) }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-chevron-left me-1"></i> {{ $prev->translatedFormat('F Y') }}
    </a>
    <h2 class="h5 mb-0">{{ $debut->translatedFormat('F Y') }}</h2>
    <a href="{{ route('rh.pointages.grille-employe', ['employee' => $employee->id, 'mois' => $next->month, 'annee' => $next->year]) }}" class="btn btn-sm btn-outline-secondary">
        {{ $next->translatedFormat('F Y') }} <i class="fas fa-chevron-right ms-1"></i>
    </a>
</div>

<form action="{{ route('rh.pointages.grille-employe.store', $employee) }}" method="POST">
    @csrf
    <input type="hidden" name="mois" value="{{ $mois }}">
    <input type="hidden" name="annee" value="{{ $annee }}">

    <div class="card data-card">
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px;">Date</th>
                        <th style="width:60px;">Jour</th>
                        <th class="text-end">H. normales</th>
                        <th class="text-end text-warning">H. sup</th>
                        <th class="text-end">H. nuit</th>
                        <th class="text-end">H. dim.</th>
                        <th class="text-end">Total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jours as $i => $j)
                        @php
                            $p = $pointages->get($j->toDateString());
                            $isWeekend = $j->isWeekend();
                            $verrouille = $p && !$p->est_modifiable;
                        @endphp
                        <tr class="{{ $isWeekend ? 'table-light' : '' }}">
                            <td>
                                <input type="hidden" name="jours[{{ $i }}][date]" value="{{ $j->toDateString() }}">
                                <strong>{{ $j->translatedFormat('d M') }}</strong>
                            </td>
                            <td><small class="text-muted">{{ $j->translatedFormat('D') }}</small></td>
                            <td>
                                <input type="number" step="0.25" min="0" max="24"
                                       name="jours[{{ $i }}][h_normales]"
                                       value="{{ $p?->h_normales ?? '' }}"
                                       class="form-control form-control-sm text-end pointage-cell"
                                       {{ $verrouille ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="number" step="0.25" min="0" max="24"
                                       name="jours[{{ $i }}][h_sup]"
                                       value="{{ $p?->h_sup ?? '' }}"
                                       class="form-control form-control-sm text-end pointage-cell"
                                       {{ $verrouille ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="number" step="0.25" min="0" max="24"
                                       name="jours[{{ $i }}][h_nuit]"
                                       value="{{ $p?->h_nuit ?? '' }}"
                                       class="form-control form-control-sm text-end pointage-cell"
                                       {{ $verrouille ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="number" step="0.25" min="0" max="24"
                                       name="jours[{{ $i }}][h_dimanche]"
                                       value="{{ $p?->h_dimanche ?? '' }}"
                                       class="form-control form-control-sm text-end pointage-cell"
                                       {{ $verrouille ? 'readonly' : '' }}>
                            </td>
                            <td class="text-end fw-bold">
                                <span class="row-total" data-row="{{ $i }}">{{ $p ? number_format($p->total, 2, ',', ' ') : '—' }}</span>
                            </td>
                            <td>
                                @if($p)
                                    <span class="badge bg-{{ $p->statut_couleur }} small">{{ $p->statut_libelle }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td colspan="2">Totaux mensuels</td>
                        <td class="text-end"><span id="totalNormales">0</span></td>
                        <td class="text-end text-warning"><span id="totalSup">0</span></td>
                        <td class="text-end"><span id="totalNuit">0</span></td>
                        <td class="text-end"><span id="totalDim">0</span></td>
                        <td class="text-end text-primary fs-6"><span id="totalGeneral">0</span> h</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('rh.pointages.index', ['employee_id' => $employee->id, 'mois' => $mois, 'annee' => $annee]) }}" class="btn btn-outline-secondary">Annuler</a>
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer la grille</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        function fr(n) { return n.toFixed(2).replace('.', ','); }
        function refresh() {
            const rows = document.querySelectorAll('tbody tr');
            let tNorm = 0, tSup = 0, tNuit = 0, tDim = 0;
            rows.forEach((row, idx) => {
                const inputs = row.querySelectorAll('input.pointage-cell');
                if (inputs.length < 4) return;
                const norm = parseFloat(inputs[0].value || 0);
                const sup  = parseFloat(inputs[1].value || 0);
                const nuit = parseFloat(inputs[2].value || 0);
                const dim  = parseFloat(inputs[3].value || 0);
                const total = norm + sup + nuit + dim;
                const cellTotal = row.querySelector('.row-total');
                if (cellTotal) cellTotal.textContent = total > 0 ? fr(total) : '—';
                tNorm += norm; tSup += sup; tNuit += nuit; tDim += dim;
            });
            document.getElementById('totalNormales').textContent = fr(tNorm);
            document.getElementById('totalSup').textContent      = fr(tSup);
            document.getElementById('totalNuit').textContent     = fr(tNuit);
            document.getElementById('totalDim').textContent      = fr(tDim);
            document.getElementById('totalGeneral').textContent  = fr(tNorm + tSup + tNuit + tDim);
        }
        document.querySelectorAll('input.pointage-cell').forEach(el => el.addEventListener('input', refresh));
        refresh();
    })();
</script>
@endpush
@endsection
