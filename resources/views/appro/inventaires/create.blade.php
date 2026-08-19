@extends('layouts.app')
@section('title', 'Nouvel inventaire')
@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Nouvel inventaire</h1></div>

<form action="{{ route('appro.inventaires.store') }}" method="POST">@csrf
<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    <div class="col-md-8"><label class="form-label">Libellé <span class="text-danger">*</span></label>
        <input type="text" name="libelle" class="form-control" required value="{{ old('libelle', 'Inventaire ' . now()->format('m/Y')) }}"></div>
    <div class="col-md-4"><label class="form-label">Date prévue <span class="text-danger">*</span></label>
        <input type="date" name="date_prevue" class="form-control" required value="{{ old('date_prevue', now()->format('Y-m-d')) }}"></div>
    <div class="col-md-6"><label class="form-label">Périmètre — emplacement</label>
        <select name="emplacement_id" class="form-select">
            <option value="">Tous emplacements (inventaire global)</option>
            @foreach($emplacements as $e)<option value="{{ $e->id }}" @selected(old('emplacement_id') == $e->id)>{{ $e->chemin }}</option>@endforeach
        </select>
        <small class="text-muted">Choisir un emplacement limite l'inventaire à cette zone et ses sous-emplacements.</small>
    </div>
    <div class="col-12"><label class="form-label">Commentaire</label>
        <textarea name="commentaire" class="form-control" rows="2">{{ old('commentaire') }}</textarea></div>
</div></div></div>

<div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i>
    À l'enregistrement, les lignes sont générées automatiquement à partir du stock actuel du périmètre choisi.
</div>

<button class="btn btn-primary"><i class="fas fa-save me-1"></i> Créer l'inventaire</button>
<a href="{{ route('appro.inventaires.index') }}" class="btn btn-outline-secondary">Annuler</a>
</form>
@endsection
