@extends('layouts.app')

@section('title', 'Sources de financement')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item">Référentiels</li>
        <li class="breadcrumb-item active">Sources de financement</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-money-check-alt me-2" style="color:#4F46E5;"></i>Sources de financement</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Origines de financement des budgets — ex : Fonds propres, Reports budgétaires, Dotation État, Subventions.</p>
    </div>
    @can('create:budget')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSourceModal">
        <i class="fas fa-plus me-1"></i> Nouvelle source
    </button>
    @endcan
</div>

<div class="card data-card mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label mb-1" style="font-size:.8rem;">Recherche</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Code, libellé, description…">
            </div>
            <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
            @if(request('q'))
            <a href="{{ route('finance.referentiels.sources.index') }}" class="btn btn-outline-secondary">Réinit.</a>
            @endif
        </form>
    </div>
</div>

<div class="card data-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th style="width:15%;">Code</th>
                    <th style="width:30%;">Libellé</th>
                    <th>Description</th>
                    <th style="width:15%; text-align:center;">Utilisée dans</th>
                    <th style="width:12%; text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sources as $s)
                <tr>
                    <td><code style="background:#EEF2FF; color:#4338CA; padding:.15rem .4rem; border-radius:3px;">{{ $s->code }}</code></td>
                    <td><strong>{{ $s->label }}</strong></td>
                    <td class="text-muted" style="font-size:.85rem;">{{ \Illuminate\Support\Str::limit($s->description, 90) ?: '—' }}</td>
                    <td class="text-center">
                        @if($s->budget_sources_count > 0)
                            <span class="badge" style="background:#E0E7FF; color:#4338CA;">{{ $s->budget_sources_count }} budget(s)</span>
                        @else
                            <span class="text-muted" style="font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @can('update:budget')
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSourceModal-{{ $s->id }}" title="Modifier">
                            <i class="fas fa-pen"></i>
                        </button>
                        @endcan
                        @can('delete:budget')
                        <form action="{{ route('finance.referentiels.sources.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer la source « {{ $s->label }} » ? Cette action est irréversible.');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" @disabled($s->budget_sources_count > 0) title="{{ $s->budget_sources_count > 0 ? 'Source utilisée — non supprimable' : 'Supprimer' }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">
                    @if(request('q'))
                        Aucune source ne correspond à votre recherche.
                    @else
                        Aucune source enregistrée. Cliquez sur <strong>« Nouvelle source »</strong> pour commencer.
                    @endif
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═════════════ MODALE CRÉATION ═════════════ --}}
@can('create:budget')
<div class="modal fade" id="createSourceModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('finance.referentiels.sources.store') }}" method="POST" class="modal-content">@csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-money-check-alt me-2" style="color:#4F46E5;"></i>Nouvelle source de financement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" required maxlength="50" placeholder="FP">
                        <div class="form-text">Code court unique (majuscules).</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Libellé <span class="text-danger">*</span></label>
                        <input type="text" name="label" class="form-control" required maxlength="255" placeholder="Fonds propres">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="2000" placeholder="Optionnel — nature de la source, règles d'utilisation…"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary" style="background:#4F46E5; border-color:#4F46E5;">Créer</button>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- ═════════════ MODALES ÉDITION ═════════════ --}}
@can('update:budget')
    @foreach($sources as $s)
    <div class="modal fade" id="editSourceModal-{{ $s->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('finance.referentiels.sources.update', $s) }}" method="POST" class="modal-content">@csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-pen me-2" style="color:#4F46E5;"></i>Modifier la source</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" value="{{ $s->code }}" required maxlength="50">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Libellé <span class="text-danger">*</span></label>
                            <input type="text" name="label" class="form-control" value="{{ $s->label }}" required maxlength="255">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" maxlength="2000">{{ $s->description }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-primary" style="background:#4F46E5; border-color:#4F46E5;">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
@endcan
@endsection
