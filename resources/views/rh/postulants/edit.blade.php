@extends('layouts.app')

@section('title', 'Édition — ' . $postulant->noms . ' ' . $postulant->prenoms)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.postulants.index') }}">Candidatures</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rh.postulants.show', $postulant) }}">{{ $postulant->noms }}</a></li>
        <li class="breadcrumb-item active">Édition</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer « {{ $postulant->noms }} {{ $postulant->prenoms }} »</h1>
    <a href="{{ route('rh.postulants.show', $postulant) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('rh.postulants.update', $postulant) }}" method="POST">
    @csrf @method('PUT')
    @include('rh.postulants._form')
</form>
@endsection
