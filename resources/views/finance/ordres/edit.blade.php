@extends('layouts.app')
@section('title', 'Modifier ordre ' . $ordre->numero_ordre)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.ordres.index') }}">Dépenses & Recettes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.ordres.show', $ordre) }}">{{ $ordre->numero_ordre }}</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Modifier {{ $ordre->numero_ordre }}</h1>
    <p class="text-muted mb-0">{{ $modele->libelle }}</p>
</div>

<form action="{{ route('finance.ordres.update', $ordre) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <input type="hidden" name="modele_id" value="{{ $modele->id }}">
    @include('finance.ordres._form', ['modele' => $modele, 'ordre' => $ordre, 'exercices' => $exercices])
</form>
@endsection
