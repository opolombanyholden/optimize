@extends('layouts.app')
@section('title', 'Nouvelle ligne')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvelle ligne (code analytique)</h1>
    <a href="{{ route('finance.referentiels.lignes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.referentiels.lignes.store') }}" method="POST">@csrf @include('finance.referentiels.lignes._form')</form>
@endsection
