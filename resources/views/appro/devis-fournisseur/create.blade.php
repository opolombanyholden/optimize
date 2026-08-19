@extends('layouts.app')
@section('title', 'Nouveau devis fournisseur')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.devis-fournisseur.index') }}">Devis fournisseur</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Nouveau devis fournisseur</h1>
    @if($commande)<p class="text-muted mb-0">Rattaché à la commande <code>{{ $commande->numero_commande }}</code></p>@endif
</div>

<form action="{{ route('appro.devis-fournisseur.store') }}" method="POST" enctype="multipart/form-data">
    @include('appro.devis-fournisseur._form')
</form>
@endsection
