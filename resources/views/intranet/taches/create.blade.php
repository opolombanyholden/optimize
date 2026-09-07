@extends('layouts.app')
@section('title', 'Nouvelle tâche')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('projet.dashboard') }}">Gestion Projet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.taches.index') }}">Tâches</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-list-check"></i></span> Nouvelle tâche</h1>
    </div>
    <form action="{{ route('intranet.taches.store') }}" method="POST" enctype="multipart/form-data">
        @csrf @include('intranet.taches._form')
    </form>
</div>
@endsection
