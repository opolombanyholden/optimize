@extends('layouts.app')
@section('title', 'Signaler un dysfonctionnement')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item"><a href="{{ route('mg.dysfonctionnements.index') }}">Dysfonctionnements</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Signaler un dysfonctionnement</h1>
    <p class="text-muted mb-0">Décrire un incident lié à un bien ou à un service.</p>
</div>

<form action="{{ route('mg.dysfonctionnements.store') }}" method="POST" enctype="multipart/form-data">
    @include('mg.dysfonctionnements._form')
</form>
@endsection
