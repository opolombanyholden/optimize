@extends('layouts.app')
@section('title', 'Éditer ' . $operation->numero)
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.operations.index') }}">Opérations</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.operations.show', $operation) }}">{{ $operation->numero }}</a></li>
        <li class="breadcrumb-item active">Édition</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer {{ $operation->numero }}</h1>
    <a href="{{ route('finance.operations.show', $operation) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.operations.update', $operation) }}" method="POST">@csrf @method('PUT') @include('finance.operations._form')</form>
@endsection
