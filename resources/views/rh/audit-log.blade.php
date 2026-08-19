@extends('layouts.app')

@section('title', 'Audit log RH')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rh.dashboard') }}">RH</a></li>
    <li class="breadcrumb-item active">Audit log</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="fas fa-shield-halved me-2"></i> Journal d'audit RH</h1>
        <p class="text-muted mb-0">Traçabilité des modifications sur les données sensibles : employés, paie, paiements, sanctions, départs.</p>
    </div>
</div>

{{-- Filtres --}}
<div class="card data-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Module</label>
                <select name="log_name" class="form-select" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    @foreach(['employee' => 'Employés', 'paie' => 'Bulletins de paie', 'payement' => 'Paiements', 'sanction' => 'Sanctions', 'depart' => 'Départs'] as $key => $label)
                        <option value="{{ $key }}" @selected(request('log_name') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Action</label>
                <select name="event" class="form-select" onchange="this.form.submit()">
                    <option value="">Toutes</option>
                    @foreach(['created' => 'Création', 'updated' => 'Modification', 'deleted' => 'Suppression'] as $key => $label)
                        <option value="{{ $key }}" @selected(request('event') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">ID de l'entité</label>
                <input type="number" name="subject_id" class="form-control" value="{{ request('subject_id') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filtrer</button>
                <a href="{{ route('rh.audit-log') }}" class="btn btn-outline-secondary"><i class="fas fa-xmark"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list-ul me-2 text-muted"></i> Activité enregistrée</h5>
        <span class="text-muted small">{{ $logs->total() }} entrée(s)</span>
    </div>
    <div class="card-body p-0">
        @if($logs->isEmpty())
            <div class="empty-state"><i class="fas fa-shield-halved"></i><p>Aucune activité enregistrée</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Cible</th>
                            <th>Auteur</th>
                            <th>Changements</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td><span class="badge bg-secondary">{{ $log->log_name }}</span></td>
                            <td>
                                @if($log->event === 'created')<span class="badge bg-success">Créé</span>
                                @elseif($log->event === 'updated')<span class="badge bg-warning text-dark">Modifié</span>
                                @elseif($log->event === 'deleted')<span class="badge bg-danger">Supprimé</span>
                                @else<span class="badge bg-info">{{ $log->event }}</span>@endif
                            </td>
                            <td>{{ $log->description }}</td>
                            <td><code>#{{ $log->subject_id }}</code></td>
                            <td>{{ $log->causer?->name ?? '—' }}</td>
                            <td>
                                @if($log->properties->has('attributes') || $log->properties->has('old'))
                                    <details>
                                        <summary class="small text-primary" style="cursor:pointer;">Voir le diff</summary>
                                        <pre class="small mt-2 mb-0" style="background:#F8FAFC;padding:.5rem;border-radius:6px;max-height:200px;overflow:auto;">{{ json_encode($log->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if($logs->hasPages())
<div class="d-flex justify-content-center mt-4">{{ $logs->withQueryString()->links() }}</div>
@endif
@endsection
