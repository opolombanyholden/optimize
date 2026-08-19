@extends('layouts.app')

@section('title', 'Édition pointage')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer un pointage</h1>
    <a href="{{ route('rh.pointages.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>

<form action="{{ route('rh.pointages.update', $pointage) }}" method="POST">
    @csrf @method('PUT')
    @include('rh.pointages._form')
</form>
@endsection
