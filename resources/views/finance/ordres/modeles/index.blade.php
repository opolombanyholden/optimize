@extends('layouts.app')
@section('title', 'Modèles d\'ordre')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item active">Modèles d'ordre</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-file-invoice text-muted me-2"></i> Modèles d'ordre</h1>
        <p class="text-muted mb-0">Paramétrez ici les gabarits d'ordres de recette et d'ordonnances de paiement — labels, champs, signataires. Ces modèles sont ensuite utilisés pour créer des ordres concrets.</p>
    </div>
    @can('create:rubrique_operation')
    <a href="{{ route('finance.referentiels.ordres-modeles.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau modèle
    </a>
    @endcan
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Sens</th>
                    <th>Format N°</th>
                    <th class="text-center">Champs</th>
                    <th class="text-center">Signataires</th>
                    <th class="text-center">Ordres émis</th>
                    <th>Actif</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($modeles as $m)
                <tr>
                    <td><code>{{ $m->code }}</code></td>
                    <td><strong>{{ $m->libelle }}</strong>
                        @if($m->entete_titre)<br><small class="text-muted">{{ $m->entete_titre }}</small>@endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $m->sens_couleur }}">
                            {{ \App\Models\Finance\OrdreModele::SENS[$m->sens] ?? '—' }}
                        </span>
                    </td>
                    <td><small class="text-muted"><code>{{ $m->numerotation_format ?: '—' }}</code></small></td>
                    <td class="text-center">{{ $m->champs_count }}</td>
                    <td class="text-center">{{ $m->signataires_count }}</td>
                    <td class="text-center">{{ $m->ordres_count }}</td>
                    <td>
                        @if($m->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('finance.referentiels.ordres-modeles.edit', $m) }}" class="btn btn-sm btn-outline-primary" title="Éditer">
                            <i class="fas fa-pen"></i>
                        </a>
                        @can('delete:rubrique_operation')
                        <form action="{{ route('finance.referentiels.ordres-modeles.destroy', $m) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Supprimer ce modèle ? (Impossible s\'il est utilisé)');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer" @disabled($m->ordres_count > 0)>
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">Aucun modèle. Créez-en un pour commencer.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
