@extends('layouts.app')
@section('title', 'Modifier — ' . $article->designation)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Référentiels</li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.catalogue.index') }}">Catalogue</a></li>
    <li class="breadcrumb-item"><a href="{{ route('referentiel.catalogue.show', $article) }}">{{ $article->designation }}</a></li>
    <li class="breadcrumb-item active">Édition</li>
</ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">
            Modifier — {{ $article->designation }}
            @if($article->code)<code class="small text-muted ms-2">{{ $article->code }}</code>@endif
        </h1>
        <p class="text-muted mb-0">Formulaire dédié à la fiche produit. Les opérations sur le stock sont accessibles depuis la <a href="{{ route('referentiel.catalogue.show', $article) }}">fiche article</a>.</p>
    </div>
    <a href="{{ route('referentiel.catalogue.show', $article) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour à la fiche</a>
</div>

<form action="{{ route('referentiel.catalogue.update', $article) }}" method="POST">
    @method('PUT')
    @include('referentiel.catalogue._form')
</form>
@endsection
