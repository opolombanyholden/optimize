@extends('layouts.app')

@section('title', 'Déclarations sociales')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Déclarations sociales</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Déclarations sociales</h1>
        <p class="text-muted mb-0">CNSS, CNAMGS, FNH, CFP — agrégat des bulletins validés</p>
    </div>
    @can('create:paie')
        <a href="{{ route('rh.declarations-sociales.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle déclaration
        </a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Organisme</label>
                <select name="type_organisme" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\DeclarationSociale::ORGANISMES as $code => $cfg)
                        <option value="{{ $code }}" @selected(request('type_organisme') === $code)>{{ $cfg['libelle'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Année</label>
                <input type="number" name="annee" class="form-control" value="{{ request('annee') }}" placeholder="{{ now()->year }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach(\App\Models\DeclarationSociale::STATUTS as $code => $lib)
                        <option value="{{ $code }}" @selected(request('statut') !== null && request('statut') !== '' && (int)request('statut') === $code)>{{ $lib }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary"><i class="fas fa-filter me-1"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Organisme</th>
                    <th>Période</th>
                    <th class="text-end">Employés</th>
                    <th class="text-end">Total brut</th>
                    <th class="text-end">Cot. patronales</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($declarations as $d)
                    <tr>
                        <td><strong>{{ $d->code }}</strong></td>
                        <td><span class="badge bg-info">{{ $d->organisme_libelle }}</span></td>
                        <td>{{ $d->periode_libelle }}</td>
                        <td class="text-end">{{ $d->nombre_employes }}</td>
                        <td class="text-end">{{ number_format($d->total_brut, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ number_format($d->total_cot_patronale, 0, ',', ' ') }}</td>
                        <td>
                            @php $color = ['secondary','warning','success'][$d->statut] ?? 'secondary'; @endphp
                            <span class="badge bg-{{ $color }}">{{ $d->statut_libelle }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('rh.declarations-sociales.show', $d) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('rh.declarations-sociales.pdf', $d) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf"></i></a>
                            <a href="{{ route('rh.declarations-sociales.csv', $d) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-file-csv"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucune déclaration. <a href="{{ route('rh.declarations-sociales.create') }}">Créer la première →</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($declarations->hasPages())
        <div class="card-footer">{{ $declarations->links() }}</div>
    @endif
</div>
@endsection
