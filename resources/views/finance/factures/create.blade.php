@extends('layouts.app')
@section('title', 'Nouvelle facture')
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.factures.index') }}">Factures</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouvelle facture</h1>
    <a href="{{ route('finance.factures.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.factures.store') }}" method="POST" enctype="multipart/form-data">@csrf @include('finance.factures._form')</form>
@endsection
