@extends('layouts.app')
@section('title', 'Nouveau modèle d\'ordre')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.referentiels.ordres-modeles.index') }}">Modèles d'ordre</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Nouveau modèle d'ordre</h1>
    <p class="text-muted mb-0">Après création, vous pourrez ajouter les champs et signataires.</p>
</div>

<form action="{{ route('finance.referentiels.ordres-modeles.store') }}" method="POST">
    @csrf
    @include('finance.ordres.modeles._form')
</form>
@endsection
