@extends('layouts.app')
@section('title', 'Nouveau courrier')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.courriers.index') }}">Courriers</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-envelope-circle-check"></i></span>
            Enregistrer un courrier
        </h1>
        <p class="page-subtitle">Scannez et indexez un courrier reçu, envoyé ou interne</p>
    </div>

    <form action="{{ route('intranet.courriers.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.courriers._form')
    </form>
</div>
@endsection
