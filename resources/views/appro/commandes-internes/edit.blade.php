@extends('layouts.app')
@section('title', 'Modifier ' . $commande->numero)
@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Modifier — {{ $commande->numero }}</h1></div>
<form action="{{ route('appro.commandes-internes.update', $commande) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @include('appro.commandes-internes._form')
</form>
@endsection
