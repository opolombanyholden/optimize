@extends('layouts.app')
@section('title', 'Modifications budgétaires V2')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.dashboard') }}">Finance V2</a></li>
        <li class="breadcrumb-item active">Modifications</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-exchange-alt me-2 text-muted"></i>Modifications budgétaires</h1>
        <p class="text-muted mb-0">Transferts entre allocations (BudgetSource). Modèle initial CdC.</p>
    </div>
    <a href="{{ route('finance.v2.modifs.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle modif</a>
</div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Date</th><th>Objet</th><th>Émission → Réception</th><th class="text-end">Montant</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        @forelse($modifs as $m)
            <tr>
                <td><small>{{ $m->date?->format('d/m/Y') }}</small></td>
                <td>{{ \Illuminate\Support\Str::limit($m->comment, 50) }}</td>
                <td>
                    <small>
                        @if($m->budgetEmission)
                            <span class="text-danger">{{ $m->budgetEmission->source?->code ?? '?' }} / {{ $m->budgetEmission->ligne?->code ?? '?' }}</span> →
                        @endif
                        <span class="text-success">{{ $m->budgetReception?->source?->code ?? '?' }} / {{ $m->budgetReception?->ligne?->code ?? '?' }}</span>
                    </small>
                </td>
                <td class="text-end fw-bold">{{ number_format((float) $m->montant, 0, ',', ' ') }}</td>
                <td><span class="badge bg-{{ $m->status_couleur }}">{{ $m->status_libelle }}</span></td>
                <td><a href="{{ route('finance.v2.modifs.show', $m) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Aucune modification.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($modifs->hasPages())<div class="card-footer">{{ $modifs->links() }}</div>@endif
</div>
@endsection
