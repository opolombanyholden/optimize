@extends('layouts.app')
@section('title', 'Nouvelle commande interne')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Achats</li>
    <li class="breadcrumb-item"><a href="{{ route('appro.commandes-internes.index') }}">Commandes internes</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Nouvelle demande d'achat</h1></div>
<form action="{{ route('appro.commandes-internes.store') }}" method="POST" enctype="multipart/form-data">
    @include('appro.commandes-internes._form')
</form>
@endsection
