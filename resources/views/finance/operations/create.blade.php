@extends('layouts.app')
@section('title', 'Nouvel ordre')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.operations.index') }}">Opérations</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvel ordre d'opération</h1>
    <a href="{{ route('finance.operations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.operations.store') }}" method="POST">@csrf @include('finance.operations._form')</form>
@endsection
