@extends('layouts.app')
@section('title', 'Configuration système')

@section('breadcrumb')
    <ol class="breadcrumb mb-0"><li class="breadcrumb-item">Administration</li><li class="breadcrumb-item active">Configuration</li></ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-1"><i class="fas fa-sliders me-2" style="color:#475569;"></i>Configuration système</h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Paramètres globaux de l'application — s'appliquent à tous les utilisateurs.</p>
    </div>
</div>

<form action="{{ route('admin.config.update') }}" method="POST">@csrf @method('PUT')
    <div class="row g-3">
        @foreach($categoriesLabels as $cat => $meta)
        @php $items = $configs->get($cat, collect()); @endphp
        @if($items->count() > 0)
        <div class="col-md-6">
            <div class="card data-card h-100">
                <div class="card-header d-flex align-items-center gap-2" style="background:#F8FAFC;">
                    <span style="width:32px;height:32px;background:{{ $meta['color'] }}15;color:{{ $meta['color'] }};border-radius:6px;display:inline-flex;align-items:center;justify-content:center;">
                        <i class="fas {{ $meta['icon'] }}"></i>
                    </span>
                    <h6 class="mb-0" style="font-size:.9rem; font-weight:700;">{{ $meta['label'] }}</h6>
                </div>
                <div class="card-body">
                    @foreach($items as $c)
                    <div class="mb-3">
                        <label class="form-label" style="font-size:.82rem;">{{ $c->libelle }}
                            <code class="text-muted" style="font-size:.68rem; margin-left:.35rem;">{{ $c->cle }}</code>
                        </label>
                        @switch($c->type)
                            @case('bool')
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="valeurs[{{ $c->cle }}]" value="1" id="cfg-{{ $c->cle }}" @checked($c->valeur_castee) @disabled(!$c->editable)>
                                    <label class="form-check-label" for="cfg-{{ $c->cle }}">Activé</label>
                                </div>
                                @break
                            @case('int')
                                <input type="number" name="valeurs[{{ $c->cle }}]" class="form-control" value="{{ $c->valeur }}" @disabled(!$c->editable)>
                                @break
                            @case('color')
                                <div class="d-flex gap-2 align-items-center">
                                    <input type="color" name="valeurs[{{ $c->cle }}]" class="form-control form-control-color" value="{{ $c->valeur ?: '#0A66C2' }}" style="width:60px;" @disabled(!$c->editable)>
                                    <input type="text" class="form-control" value="{{ $c->valeur }}" readonly style="font-family:monospace; max-width:120px;">
                                </div>
                                @break
                            @case('image')
                            @case('url')
                                <input type="url" name="valeurs[{{ $c->cle }}]" class="form-control" value="{{ $c->valeur }}" placeholder="https://…" @disabled(!$c->editable)>
                                @break
                            @default
                                <input type="text" name="valeurs[{{ $c->cle }}]" class="form-control" value="{{ $c->valeur }}" @disabled(!$c->editable)>
                        @endswitch
                        @if($c->description)<div class="form-text">{{ $c->description }}</div>@endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Enregistrer toutes les modifications</button>
    </div>
</form>
@endsection
