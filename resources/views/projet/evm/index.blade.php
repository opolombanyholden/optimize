@extends('layouts.app')
@section('title', $projet->nom . ' — EVM')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Projets</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projet.overview', $projet) }}">{{ $projet->nom }}</a></li>
    <li class="breadcrumb-item active">EVM</li>
</ol>
@endsection

@section('content')
<div class="page-intranet" style="--accent: #0D9488;">

    @include('projet._partials.projet-header', ['projet' => $projet, 'currentPage' => 'evm'])

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background: linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-chart-line"></i></span>
                Valeur acquise (EVM) — {{ $projet->nom }}
            </h1>
            <p class="page-subtitle">Earned Value Management · Suivi de la performance projet</p>
        </div>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalEvm">
            <i class="fas fa-plus me-2"></i> Nouvelle mesure
        </button>
    </div>

    {{-- Dernière mesure EVM --}}
    @if($evms->count())
    @php $latest = $evms->first(); @endphp
    <div class="form-card mb-4">
        <h5 class="mb-3"><i class="fas fa-tachometer-alt me-2" style="color:#0D9488;"></i> Dernière mesure — {{ $latest->date_mesure->format('d/m/Y') }}</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="courrier-stats" style="border-left:4px solid #0D9488;">
                    <div class="stat-label">SPI (Schedule)</div>
                    <div class="stat-value" style="color:{{ $latest->spi >= 1 ? '#16A34A' : ($latest->spi >= 0.8 ? '#F59E0B' : '#DC2626') }};">
                        {{ number_format($latest->spi, 2) }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="courrier-stats" style="border-left:4px solid #0F766E;">
                    <div class="stat-label">CPI (Cost)</div>
                    <div class="stat-value" style="color:{{ $latest->cpi >= 1 ? '#16A34A' : ($latest->cpi >= 0.8 ? '#F59E0B' : '#DC2626') }};">
                        {{ number_format($latest->cpi, 2) }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="courrier-stats" style="border-left:4px solid {{ $latest->sv >= 0 ? '#16A34A' : '#DC2626' }};">
                    <div class="stat-label">SV (Écart délai)</div>
                    <div class="stat-value">{{ number_format($latest->sv, 0, ',', ' ') }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="courrier-stats" style="border-left:4px solid {{ $latest->cv >= 0 ? '#16A34A' : '#DC2626' }};">
                    <div class="stat-label">CV (Écart coût)</div>
                    <div class="stat-value">{{ number_format($latest->cv, 0, ',', ' ') }}</div>
                </div>
            </div>
        </div>
        <div class="mt-3 text-center">
            @php
                $sante = ($latest->spi >= 1 && $latest->cpi >= 1) ? 'vert' : (($latest->spi >= 0.8 && $latest->cpi >= 0.8) ? 'orange' : 'rouge');
                $santeColors = ['vert' => '#16A34A', 'orange' => '#F59E0B', 'rouge' => '#DC2626'];
                $santeLabels = ['vert' => 'Projet en bonne santé', 'orange' => 'Vigilance requise', 'rouge' => 'Projet en difficulté'];
            @endphp
            <span class="opp-stage-badge" style="background:{{ $santeColors[$sante] }}; font-size:.85rem; padding:.4rem 1rem;">
                <i class="fas fa-{{ $sante === 'vert' ? 'check-circle' : ($sante === 'orange' ? 'exclamation-triangle' : 'times-circle') }} me-1"></i>
                {{ $santeLabels[$sante] }}
            </span>
        </div>
    </div>

    <div class="form-card">
        <table class="opp-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>BAC</th>
                    <th>PV</th>
                    <th>EV</th>
                    <th>AC</th>
                    <th>SV</th>
                    <th>CV</th>
                    <th>SPI</th>
                    <th>CPI</th>
                    <th>ETC</th>
                    <th>EAC</th>
                    <th>Santé</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($evms as $evm)
                @php
                    $s = ($evm->spi >= 1 && $evm->cpi >= 1) ? 'vert' : (($evm->spi >= 0.8 && $evm->cpi >= 0.8) ? 'orange' : 'rouge');
                @endphp
                <tr>
                    <td><strong>{{ $evm->date_mesure->format('d/m/Y') }}</strong></td>
                    <td>{{ number_format($evm->bac, 0, ',', ' ') }}</td>
                    <td>{{ number_format($evm->pv, 0, ',', ' ') }}</td>
                    <td>{{ number_format($evm->ev, 0, ',', ' ') }}</td>
                    <td>{{ number_format($evm->ac, 0, ',', ' ') }}</td>
                    <td style="color:{{ $evm->sv >= 0 ? '#16A34A' : '#DC2626' }};">{{ number_format($evm->sv, 0, ',', ' ') }}</td>
                    <td style="color:{{ $evm->cv >= 0 ? '#16A34A' : '#DC2626' }};">{{ number_format($evm->cv, 0, ',', ' ') }}</td>
                    <td><span class="opp-stage-badge" style="background:{{ $evm->spi >= 1 ? '#16A34A' : ($evm->spi >= 0.8 ? '#F59E0B' : '#DC2626') }};">{{ number_format($evm->spi, 2) }}</span></td>
                    <td><span class="opp-stage-badge" style="background:{{ $evm->cpi >= 1 ? '#16A34A' : ($evm->cpi >= 0.8 ? '#F59E0B' : '#DC2626') }};">{{ number_format($evm->cpi, 2) }}</span></td>
                    <td>{{ number_format($evm->etc, 0, ',', ' ') }}</td>
                    <td>{{ number_format($evm->eac, 0, ',', ' ') }}</td>
                    <td>
                        <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:{{ $santeColors[$s] }};"></span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="ged-action-btn" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end ged-dropdown">
                                <li><form action="{{ route('projet.evm.destroy', [$projet, $evm]) }}" method="POST" onsubmit="return confirm('Supprimer cette mesure ?');">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger"><i class="fas fa-trash"></i> Supprimer</button>
                                </form></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="empty-state-icon" style="background:#F0FDFA;"><i class="fas fa-chart-line" style="color:#0D9488;"></i></div>
        <h3>Aucune mesure EVM</h3>
        <p>Ajoutez une première mesure de valeur acquise pour suivre la performance du projet.</p>
        <button class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);" data-bs-toggle="modal" data-bs-target="#modalEvm">
            <i class="fas fa-plus me-2"></i> Première mesure
        </button>
    </div>
    @endif
</div>

{{-- Modale EVM --}}
<div class="modal fade" id="modalEvm" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('projet.evm.store', $projet) }}" method="POST" class="modal-content">@csrf
            <div class="modal-header"><h5 class="modal-title">Nouvelle mesure EVM</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Date de mesure <span class="text-danger">*</span></label>
                    <input type="date" name="date_mesure" class="form-control" required value="{{ date('Y-m-d') }}"></div>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">BAC (Budget at Completion) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="bac" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">PV (Planned Value) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="pv" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">EV (Earned Value) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="ev" class="form-control" required></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4"><label class="form-label">AC (Actual Cost) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="ac" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">ETC (Estimate to Complete)</label>
                        <input type="number" step="0.01" name="etc" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">EAC (Estimate at Completion)</label>
                        <input type="number" step="0.01" name="eac" class="form-control"></div>
                </div>
                <div class="mb-3 mt-3"><label class="form-label">Commentaire</label>
                    <textarea name="commentaire" rows="2" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-intranet" style="background:linear-gradient(135deg,#0D9488,#0F766E);"><i class="fas fa-chart-line me-2"></i> Enregistrer</button></div>
        </form>
    </div>
</div>
@endsection
