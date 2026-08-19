@extends('layouts.app')
@section('title', 'Éditer ' . $client->raison_sociale)
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.clients.index') }}">Clients</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.clients.show', $client) }}">{{ $client->raison_sociale }}</a></li>
        <li class="breadcrumb-item active">Édition</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer « {{ $client->raison_sociale }} »</h1>
    <a href="{{ route('finance.clients.show', $client) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
<form action="{{ route('finance.clients.update', $client) }}" method="POST">@csrf @method('PUT') @include('finance.clients._form')</form>
@endsection
