@extends('layouts.app')
@section('title', 'Sources de financement')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.v2.dashboard') }}">Finance V2</a></li>
        <li class="breadcrumb-item active">Sources</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-money-check-alt me-2 text-muted"></i>Sources de financement</h1>
        <p class="text-muted mb-0">Configurez librement les sources (FP, RB, ETAT, autres…). Les budgets sont ensuite alloués par source.</p>
    </div>
    <a href="{{ route('finance.v2.sources.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvelle source</a>
</div>

<div class="card data-card">
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead><tr><th>Code</th><th>Libellé</th><th>Description</th><th class="text-end">Utilisations</th><th class="text-end"></th></tr></thead>
        <tbody>
        @forelse($sources as $s)
            <tr>
                <td><code>{{ $s->code }}</code></td>
                <td><strong>{{ $s->label }}</strong></td>
                <td>{{ \Illuminate\Support\Str::limit($s->description ?? '—', 60) }}</td>
                <td class="text-end"><span class="badge bg-info">{{ $s->budget_sources_count }}</span></td>
                <td class="text-end">
                    <a href="{{ route('finance.v2.sources.edit', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-pen"></i></a>
                    <form action="{{ route('finance.v2.sources.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-4">Aucune source. <a href="{{ route('finance.v2.sources.create') }}">Créer la première →</a></td></tr>
        @endforelse
        </tbody>
    </table></div>
    @if($sources->hasPages())<div class="card-footer">{{ $sources->links() }}</div>@endif
</div>
@endsection
