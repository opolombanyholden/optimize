@extends('layouts.app')
@section('title', 'Éditer ' . $ligne->libelle)
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer « {{ $ligne->libelle }} »</h1>
    <a href="{{ route('finance.referentiels.lignes.show', $ligne) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.referentiels.lignes.update', $ligne) }}" method="POST">@csrf @method('PUT') @include('finance.referentiels.lignes._form')</form>
@endsection
