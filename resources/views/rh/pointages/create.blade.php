@extends('layouts.app')

@section('title', 'Nouveau pointage')

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.pointages.index') }}">Pointages</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Nouveau pointage</h1>
    <a href="{{ route('rh.pointages.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('rh.pointages.store') }}" method="POST">
    @csrf
    @include('rh.pointages._form')
</form>
@endsection
