@extends('layouts.app')
@section('title', 'Modifier — ' . $tache->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Gestion Projet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.taches.index') }}">Tâches</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span> Modifier la tâche</h1>
        <p class="page-subtitle">{{ $tache->titre }}</p>
    </div>
    <form action="{{ route('intranet.taches.update', $tache) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT') @include('intranet.taches._form')
    </form>
</div>
@endsection
