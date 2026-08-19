@extends('layouts.app')
@section('title', 'Modifier — ' . $dysfonctionnement->label)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('mg.dysfonctionnements.index') }}">Dysfonctionnements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('mg.dysfonctionnements.show', $dysfonctionnement) }}">{{ $dysfonctionnement->label }}</a></li>
    <li class="breadcrumb-item active">Édition</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Modifier — {{ $dysfonctionnement->label }}</h1>
</div>

<form action="{{ route('mg.dysfonctionnements.update', $dysfonctionnement) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    @include('mg.dysfonctionnements._form')
</form>
@endsection
