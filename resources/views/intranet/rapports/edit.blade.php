@extends('layouts.app')
@section('title', 'Modifier — ' . $rapport->reference)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Gestion Projet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.rapports.index') }}">Rapports & CR</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span> Modifier le rapport</h1>
        <p class="page-subtitle">{{ $rapport->reference }} — {{ $rapport->titre }}</p>
    </div>
    <form action="{{ route('intranet.rapports.update', $rapport) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT') @include('intranet.rapports._form')
    </form>
</div>
@endsection
