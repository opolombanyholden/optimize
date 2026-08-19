@extends('layouts.app')
@section('title', 'Modifier — ' . $opportunite->titre)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.opportunites.index') }}">Opportunités</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span>
            Modifier l'opportunité
        </h1>
        <p class="page-subtitle">{{ $opportunite->titre }}</p>
    </div>
    <form action="{{ route('intranet.opportunites.update', $opportunite) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('intranet.opportunites._form')
    </form>
</div>
@endsection
