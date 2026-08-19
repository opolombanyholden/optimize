@extends('layouts.app')
@section('title', 'Nouvelle opportunité')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.opportunites.index') }}">Opportunités</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-fire-flame-curved"></i></span>
            Nouvelle opportunité
        </h1>
        <p class="page-subtitle">Créez une opportunité commerciale</p>
    </div>
    <form action="{{ route('intranet.opportunites.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.opportunites._form')
    </form>
</div>
@endsection
