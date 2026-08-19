@extends('layouts.app')

@section('title', 'Nouvelle annonce')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.annonces.index') }}">Annonces</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-bullhorn"></i></span>
            Nouvelle annonce
        </h1>
        <p class="page-subtitle">Rédigez et publiez une annonce à l'attention de vos collaborateurs</p>
    </div>

    <form action="{{ route('intranet.annonces.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.annonces._form')
    </form>
</div>
@endsection
