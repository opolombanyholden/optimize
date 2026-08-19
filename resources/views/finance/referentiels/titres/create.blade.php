@extends('layouts.app')
@section('title', 'Nouveau titre')
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouveau titre</h1>
    <a href="{{ route('finance.referentiels.titres.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.referentiels.titres.store') }}" method="POST">@csrf @include('finance.referentiels.titres._form')</form>
@endsection
