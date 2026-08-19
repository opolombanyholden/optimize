@extends('layouts.app')

@section('title', 'Échantillons de paie')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item active">Échantillons de paie</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1">Échantillons de paie</h1>
        <p class="text-muted mb-0">Groupes prédéfinis d'employés à réutiliser pour vos campagnes de paie.</p>
    </div>
    @can('create:paie')
        <a href="{{ route('rh.echantillons-paie.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouvel échantillon
        </a>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Rechercher</label>
                <input type="text" name="q" class="form-control" placeholder="Libellé ou code…" value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous</option>
                    <option value="1" @selected(request('statut') === '1')>Actif</option>
                    <option value="0" @selected(request('statut') === '0')>Archivé</option>
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
                    <th>Échantillon</th>
                    <th>Code</th>
                    <th class="text-end">Employés</th>
                    <th>Créateur</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($echantillons as $e)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($e->icone)
                                    <span class="rounded-circle d-inline-flex align-items-center justify-content-center"
                                          style="width:34px;height:34px;background:{{ $e->couleur ?? '#0EA5E9' }}20;color:{{ $e->couleur ?? '#0EA5E9' }};">
                                        <i class="fas {{ $e->icone }}"></i>
                                    </span>
                                @endif
                                <div>
                                    <strong>{{ $e->libelle }}</strong>
                                    @if($e->description)
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($e->description, 80) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><code class="small">{{ $e->code }}</code></td>
                        <td class="text-end"><span class="badge bg-info">{{ $e->employes_count }}</span></td>
                        <td>{{ $e->createur?->name ?? '—' }}</td>
                        <td>
                            @if($e->statut)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Archivé</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('rh.echantillons-paie.show', $e) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            @can('update:paie')
                                <a href="{{ route('rh.echantillons-paie.edit', $e) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                            @endcan
                            @can('delete:paie')
                                <form action="{{ route('rh.echantillons-paie.destroy', $e) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer cet échantillon ? Les campagnes existantes ne seront pas affectées.');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        Aucun échantillon. <a href="{{ route('rh.echantillons-paie.create') }}">Créer le premier →</a>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($echantillons->hasPages())
        <div class="card-footer">{{ $echantillons->links() }}</div>
    @endif
</div>
@endsection
