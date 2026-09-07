@extends('layouts.app')
@section('title', 'Permissions')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item active">Permissions</li></ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-key me-2" style="color:#475569;"></i>Matrice des permissions</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Toutes les permissions du système ({{ $totalCount }}) affichées par entité. Lecture seule — les permissions sont figées par le seeder.</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary"><i class="fas fa-shield-halved me-1"></i>Affecter aux rôles</a>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:.82rem;">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th class="ps-3">Entité</th>
                    @foreach($actions as $a)<th class="text-center" style="text-transform:capitalize;">{{ $a }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($matrice as $entite => $actionsData)
                <tr>
                    <td class="ps-3"><strong style="text-transform:capitalize;">{{ str_replace('_', ' ', $entite) }}</strong></td>
                    @foreach($actions as $a)
                    <td class="text-center">
                        @if(isset($actionsData[$a]))
                            <span class="badge" style="background:#DBEAFE;color:#1E40AF;" title="{{ $actionsData[$a]['name'] }} — attribuée à {{ $actionsData[$a]['nb_roles'] }} rôle(s)">
                                <i class="fas fa-check"></i> {{ $actionsData[$a]['nb_roles'] }}
                            </span>
                        @else
                            <span class="text-muted" style="opacity:.3;">—</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
