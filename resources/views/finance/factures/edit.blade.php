@extends('layouts.app')
@section('title', 'Éditer ' . $facture->numero)
@section('breadcrumb')
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('finance.factures.index') }}">Factures</a></li>
        <li class="breadcrumb-item"><a href="{{ route('finance.factures.show', $facture) }}">{{ $facture->numero }}</a></li>
        <li class="breadcrumb-item active">Édition</li>
    </ol>
@endsection
@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Éditer {{ $facture->numero }}</h1>
    <a href="{{ route('finance.factures.show', $facture) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Retour</a>
</div>
@isset($commande)
    @if($commande)
        <div class="alert alert-info small">
            <i class="fas fa-info-circle me-1"></i>
            Facture issue de la consultation <a href="{{ route('appro.commandes.show', $commande) }}"><code>{{ $commande->numero_commande }}</code></a>.
            La liste des fournisseurs est restreinte à ceux ayant fourni un devis pour cette commande.
        </div>
    @endif
@endisset
<form action="{{ route('finance.factures.update', $facture) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT') @include('finance.factures._form')</form>
@endsection
