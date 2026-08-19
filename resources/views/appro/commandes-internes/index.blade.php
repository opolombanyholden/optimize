@extends('layouts.app')
@section('title', 'Commandes internes')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item active">Commandes internes</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-inbox text-muted me-2"></i> Commandes internes</h1>
        <p class="text-muted mb-0">Demandes des agents → validation N+1 → traitement Appro.</p>
    </div>
    <a href="{{ route('appro.commandes-internes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle demande</a>
</div>

<ul class="nav nav-tabs mb-3">
    @foreach(['mes' => 'Mes demandes', 'a_valider' => 'À valider (N+1)', 'appro' => 'Traitement Appro', 'toutes' => 'Toutes'] as $k => $v)
        <li class="nav-item"><a class="nav-link @if($onglet === $k) active @endif" href="{{ route('appro.commandes-internes.index', ['onglet' => $k]) }}">{{ $v }}</a></li>
    @endforeach
</ul>

<div class="card data-card mb-3"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="onglet" value="{{ $onglet }}">
        <div class="col-md-4"><label class="form-label small mb-1">Recherche</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Objet, numéro…"></div>
        <div class="col-md-4"><label class="form-label small mb-1">Statut</label>
            <select name="statut" class="form-select form-select-sm">
                <option value="">Tous</option>
                @foreach($statuts as $k => $v)<option value="{{ $k }}" @selected(request('statut') !== null && request('statut') !== '' && (int) request('statut') === $k)>{{ $v }}</option>@endforeach
            </select></div>
        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-filter"></i></button></div>
    </form>
</div></div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr>
            <th>N°</th><th>Objet</th><th>Demandeur</th><th>N+1</th><th>Date demande</th>
            <th>Statut</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($commandes as $c)
            <tr>
                <td><code>{{ $c->numero }}</code></td>
                <td>{{ $c->objet }}</td>
                <td><small>{{ $c->demandeur?->name ?? '—' }}</small></td>
                <td><small>{{ $c->superieur?->name ?? '—' }}</small></td>
                <td><small>{{ $c->date_demande?->format('d/m/Y') }}</small></td>
                <td><span class="badge bg-{{ $c->statut_couleur }}">{{ $c->statut_libelle }}</span></td>
                <td class="text-end"><a href="{{ route('appro.commandes-internes.show', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Aucune demande.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($commandes->hasPages())<div class="card-footer">{{ $commandes->links() }}</div>@endif
</div>
@endsection
