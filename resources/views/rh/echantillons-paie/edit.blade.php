@extends('layouts.app')

@section('title', 'Édition — ' . $echantillon->libelle)

@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item">RH</li>
        <li class="breadcrumb-item"><a href="{{ route('rh.echantillons-paie.index') }}">Échantillons de paie</a></li>
        <li class="breadcrumb-item"><a href="{{ route('rh.echantillons-paie.show', $echantillon) }}">{{ $echantillon->libelle }}</a></li>
        <li class="breadcrumb-item active">Édition</li>
    </ol>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer « {{ $echantillon->libelle }} »</h1>
    <a href="{{ route('rh.echantillons-paie.show', $echantillon) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('rh.echantillons-paie.update', $echantillon) }}" method="POST">
    @csrf @method('PUT')
    @include('rh.echantillons-paie._form')
</form>
@endsection
