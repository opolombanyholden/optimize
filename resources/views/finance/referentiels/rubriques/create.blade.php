@extends('layouts.app')
@section('title', 'Nouvelle rubrique')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvelle rubrique d'opération</h1>
    <a href="{{ route('finance.referentiels.rubriques.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.referentiels.rubriques.store') }}" method="POST">@csrf @include('finance.referentiels.rubriques._form')</form>
@endsection
