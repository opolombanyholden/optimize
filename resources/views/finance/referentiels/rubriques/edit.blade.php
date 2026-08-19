@extends('layouts.app')
@section('title', 'Éditer ' . $rubrique->libelle)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer « {{ $rubrique->libelle }} »</h1>
    <a href="{{ route('finance.referentiels.rubriques.show', $rubrique) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.referentiels.rubriques.update', $rubrique) }}" method="POST">@csrf @method('PUT') @include('finance.referentiels.rubriques._form')</form>
@endsection
