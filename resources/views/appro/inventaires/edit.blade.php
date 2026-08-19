@extends('layouts.app')
@section('title', 'Modifier ' . $inventaire->numero)
@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Modifier — {{ $inventaire->numero }}</h1></div>

<form action="{{ route('appro.inventaires.update', $inventaire) }}" method="POST">@csrf @method('PUT')
<div class="card data-card mb-3"><div class="card-body"><div class="row g-3">
    <div class="col-md-8"><label class="form-label">Libellé</label>
        <input type="text" name="libelle" class="form-control" required value="{{ old('libelle', $inventaire->libelle) }}"></div>
    <div class="col-md-4"><label class="form-label">Date prévue</label>
        <input type="date" name="date_prevue" class="form-control" required value="{{ old('date_prevue', $inventaire->date_prevue?->format('Y-m-d')) }}"></div>
    <div class="col-md-6"><label class="form-label">Périmètre — emplacement</label>
        <select name="emplacement_id" class="form-select">
            <option value="">Tous emplacements</option>
            @foreach($emplacements as $e)<option value="{{ $e->id }}" @selected(old('emplacement_id', $inventaire->emplacement_id) == $e->id)>{{ $e->chemin }}</option>@endforeach
        </select></div>
    <div class="col-12"><label class="form-label">Commentaire</label>
        <textarea name="commentaire" class="form-control" rows="2">{{ old('commentaire', $inventaire->commentaire) }}</textarea></div>
</div></div></div>

<div class="alert alert-warning small"><i class="fas fa-triangle-exclamation me-1"></i>
    Modifier le périmètre régénère toutes les lignes existantes.
</div>

<button class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
<a href="{{ route('appro.inventaires.show', $inventaire) }}" class="btn btn-outline-secondary">Annuler</a>
</form>
@endsection
