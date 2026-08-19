@extends('layouts.app')

@section('title', 'Dashboard RH')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">RH & Paiement</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">

    <div class="page-header-intranet d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="page-title">
                <span class="page-title-icon" style="background:linear-gradient(135deg,#059669,#0891B2);"><i class="fas fa-users"></i></span>
                RH & Paiement
            </h1>
            <p class="page-subtitle">Tableau de bord consolidé des Ressources Humaines</p>
        </div>
        <div class="d-flex gap-2">
            @can('read:employee')
            <a href="{{ route('rh.audit-log') }}" class="btn btn-light"><i class="fas fa-shield-halved me-2"></i> Audit log</a>
            @endcan
            @can('create:employee')
            <a href="{{ route('rh.employees.create') }}" class="btn btn-intranet" style="background:linear-gradient(135deg,#059669,#0891B2);">
                <i class="fas fa-user-plus me-2"></i> Nouvel employé
            </a>
            @endcan
        </div>
    </div>

    {{-- KPI principaux --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:2rem;color:#059669;"><i class="fas fa-id-card"></i></div><div class="h3 mb-0">{{ $kpis['employes_actifs'] }}</div><div class="text-muted small">Employés actifs</div></div></div>
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:2rem;color:#D97706;"><i class="fas fa-user-clock"></i></div><div class="h3 mb-0">{{ $kpis['absences_en_attente'] }}</div><div class="text-muted small">Absences à valider</div></div></div>
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:2rem;color:#0891B2;"><i class="fas fa-file-invoice-dollar"></i></div><div class="h3 mb-0">{{ $kpis['bulletins_mois'] }}</div><div class="text-muted small">Bulletins ce mois</div></div></div>
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:2rem;color:#DC2626;"><i class="fas fa-coins"></i></div><div class="h3 mb-0">{{ number_format($masseSalarialeMois, 0, ',', ' ') }}</div><div class="text-muted small">Masse salariale (XAF)</div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.5rem;color:#7C3AED;"><i class="fas fa-user-plus"></i></div><div class="h4 mb-0">{{ $kpis['recrutements_ouverts'] }}</div><div class="text-muted small">Recrutements ouverts</div></div></div>
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.5rem;color:#DC2626;"><i class="fas fa-gavel"></i></div><div class="h4 mb-0">{{ $kpis['sanctions_actives'] }}</div><div class="text-muted small">Sanctions actives</div></div></div>
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.5rem;color:#475569;"><i class="fas fa-door-open"></i></div><div class="h4 mb-0">{{ $kpis['departs_en_cours'] }}</div><div class="text-muted small">Départs en cours</div></div></div>
        <div class="col-6 col-md-3"><div class="card data-card text-center p-3"><div style="font-size:1.5rem;color:#F59E0B;"><i class="fas fa-star-half-stroke"></i></div><div class="h4 mb-0">{{ $kpis['evaluations_en_cours'] }}</div><div class="text-muted small">Évaluations à clôturer</div></div></div>
    </div>

    <div class="row g-3">
        {{-- Évolution masse salariale --}}
        <div class="col-12 col-lg-8">
            <div class="card data-card p-3">
                <h5 class="mb-3"><i class="fas fa-chart-line me-2 text-primary"></i> Évolution masse salariale brute (12 mois)</h5>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Mois</th><th class="text-end">Brut (XAF)</th><th></th></tr></thead>
                    <tbody>
                    @php $maxMasse = max(1, $evolutionMasse->max('brut')); @endphp
                    @foreach($evolutionMasse as $row)
                        <tr>
                            <td>{{ $row['mois'] }}</td>
                            <td class="text-end fw-bold">{{ number_format($row['brut'], 0, ',', ' ') }}</td>
                            <td style="width:50%;">
                                <div class="tache-progress-bar" style="background:#F1F5F9;">
                                    <div class="tache-progress-fill" style="width:{{ ($row['brut'] / $maxMasse) * 100 }}%;background:linear-gradient(90deg,#059669,#0891B2);"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Effectif par département --}}
        <div class="col-12 col-lg-4">
            <div class="card data-card p-3 mb-3">
                <h6 class="mb-3"><i class="fas fa-sitemap me-2 text-success"></i> Effectif par département</h6>
                @forelse($effectifParDept as $dept => $n)
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span>{{ $dept ?? '—' }}</span><span class="badge bg-success">{{ $n }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">Aucune donnée</p>
                @endforelse
            </div>

            <div class="card data-card p-3">
                <h6 class="mb-3"><i class="fas fa-file-contract me-2 text-info"></i> Répartition contrats</h6>
                @forelse($repartitionContrats as $type => $n)
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <span>{{ $type ?? '—' }}</span><span class="badge bg-info">{{ $n }}</span>
                    </div>
                @empty
                    <p class="text-muted small text-center py-2">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Dernière activité sensible --}}
    <div class="card data-card p-3 mt-3">
        <h5 class="mb-3"><i class="fas fa-shield-halved me-2 text-warning"></i> Dernières actions sensibles (audit trail)</h5>
        @if($derniereActivite->isEmpty())
            <p class="text-muted text-center small py-3">Aucune action enregistrée</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead><tr><th>Date</th><th>Module</th><th>Action</th><th>Description</th><th>Auteur</th></tr></thead>
                    <tbody>
                    @foreach($derniereActivite as $a)
                        <tr>
                            <td class="text-nowrap small">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-secondary">{{ $a->log_name }}</span></td>
                            <td><span class="badge bg-light text-dark">{{ $a->event }}</span></td>
                            <td class="small">{{ $a->description }}</td>
                            <td class="small">{{ $a->causer?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-end mt-2"><a href="{{ route('rh.audit-log') }}" class="small">Voir tout le journal →</a></div>
        @endif
    </div>

</div>
@endsection
