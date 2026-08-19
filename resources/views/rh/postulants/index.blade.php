@extends('layouts.app')

@section('title', 'Candidatures')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Candidatures</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Candidatures</h1>
        <p class="text-muted mb-0">Postulants liés aux campagnes de recrutement.</p>
    </div>
    @can('create:postulant')
        <a href="{{ route('rh.postulants.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvelle candidature
        </a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Rechercher</label>
                <input type="text" name="q" class="form-control" placeholder="Nom, prénom, email…" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Campagne</label>
                <select name="recrutement_id" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($recrutements as $r)
                        <option value="{{ $r->id }}" @selected(request('recrutement_id') == $r->id)>{{ $r->label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    @foreach($statuts as $code => $lib)
                        <option value="{{ $code }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $code)>{{ $lib }}</option>
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
                    <th>Candidat</th>
                    <th>Email / Contact</th>
                    <th>Campagne / Profil</th>
                    <th>Statut</th>
                    <th>Reçu le</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($postulants as $p)
                    <tr>
                        <td><strong>{{ $p->noms }} {{ $p->prenoms }}</strong></td>
                        <td>
                            <small>{{ $p->email }}</small>
                            @if($p->contact)<br><small class="text-muted">{{ $p->contact }}</small>@endif
                        </td>
                        <td>
                            @if($p->recrutement)
                                <a href="{{ route('rh.recrutements.show', $p->recrutement) }}">{{ $p->recrutement->label }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                            @if($p->profil)
                                <br><small class="text-muted">{{ $p->profil->label }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $statutCouleurs[$p->statut] ?? 'secondary' }}">{{ $statuts[$p->statut] ?? '—' }}</span>
                            @if($p->embauche)
                                <br><small class="text-success"><i class="fas fa-user-check me-1"></i>Embauché</small>
                            @endif
                        </td>
                        <td>{{ $p->created_at?->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('rh.postulants.show', $p) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            @can('update:postulant')
                                <a href="{{ route('rh.postulants.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                            @endcan
                            @can('delete:postulant')
                                @if(!$p->embauche)
                                    <form action="{{ route('rh.postulants.destroy', $p) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette candidature ?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        Aucune candidature. <a href="{{ route('rh.postulants.create') }}">Enregistrer la première →</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($postulants->hasPages())
        <div class="card-footer">{{ $postulants->links() }}</div>
    @endif
</div>
@endsection
