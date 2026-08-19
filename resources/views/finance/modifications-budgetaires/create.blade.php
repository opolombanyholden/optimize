@extends('layouts.app')

@section('title', 'Nouvelle modification budgétaire')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.modifications-budgetaires.index') }}">Modifications budgétaires</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvelle modification budgétaire</h1>
    <a href="{{ route('finance.modifications-budgetaires.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('finance.modifications-budgetaires.store') }}" method="POST">
    @csrf
    @include('finance.modifications-budgetaires._form')
</form>
@endsection
