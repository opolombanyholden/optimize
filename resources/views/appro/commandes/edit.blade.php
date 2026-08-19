@extends('layouts.app')
@section('title', 'Modifier ' . $commande->numero_commande)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('appro.commandes.index') }}">Commandes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('appro.commandes.show', $commande) }}">{{ $commande->numero_commande }}</a></li>
    <li class="breadcrumb-item active">Édition</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Modifier {{ $commande->numero_commande }}</h1>
    <p class="text-muted mb-0">
        <span class="badge bg-{{ $commande->statut_couleur }}">{{ $commande->statut_libelle }}</span>
    </p>
</div>

<form action="{{ route('appro.commandes.update', $commande) }}" method="POST">
    @csrf @method('PUT')
    @include('appro.commandes._form')
</form>
@endsection
