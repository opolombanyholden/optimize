@extends('layouts.app')
@section('title', 'Nouvelle commande')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.commandes.index') }}">Commandes</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Nouvelle commande</h1>
    <p class="text-muted mb-0">Créer une commande fournisseur — brouillon éditable.</p>
</div>

<form action="{{ route('appro.commandes.store') }}" method="POST">
    @csrf
    @include('appro.commandes._form')
</form>
@endsection
