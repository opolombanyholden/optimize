@extends('layouts.app')
@section('title', 'Modifier ' . $compte->nom)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.comptes.index') }}">Comptes</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">{{ $compte->nom }}</h1>
    <p class="text-muted mb-0">
        <span class="badge bg-{{ $compte->type_couleur }}"><i class="fas {{ $compte->type_icone }} me-1"></i>{{ $compte->type_libelle }}</span>
        @if($compte->code)<code class="ms-2">{{ $compte->code }}</code>@endif
    </p>
</div>

<form action="{{ route('finance.comptes.update', $compte) }}" method="POST">
    @csrf @method('PUT')
    @include('finance.comptes._form')
</form>
@endsection
