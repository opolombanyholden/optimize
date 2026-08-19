@extends('layouts.app')
@section('title', 'Éditer ' . $titre->libelle)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer « {{ $titre->libelle }} »</h1>
    <a href="{{ route('finance.referentiels.titres.show', $titre) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.referentiels.titres.update', $titre) }}" method="POST">@csrf @method('PUT') @include('finance.referentiels.titres._form')</form>
@endsection
