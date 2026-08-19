@extends('layouts.app')
@section('title', 'Modifier ' . $campagne->libelle)
@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Modifier — {{ $campagne->libelle }}</h1></div>
<form action="{{ route('appro.campagnes.update', $campagne) }}" method="POST">@method('PUT')
    @include('appro.campagnes._form')
</form>
@endsection
