@extends('layouts.app')
@section('title', 'Modifier — ' . $devis->numero)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.devis-fournisseur.index') }}">Devis fournisseur</a></li>
    <li class="breadcrumb-item"><a href="{{ route('appro.devis-fournisseur.show', $devis) }}">{{ $devis->numero }}</a></li>
    <li class="breadcrumb-item active">Édition</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Modifier — {{ $devis->numero }}</h1></div>

<form action="{{ route('appro.devis-fournisseur.update', $devis) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @php $commande = null; $commandes = collect(); @endphp
    @include('appro.devis-fournisseur._form')
</form>
@endsection
