@extends('layouts.app')
@section('title', 'Nouvelle organisation')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.organisations.index') }}">Organisations</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-building"></i></span>
            Nouvelle organisation
        </h1>
        <p class="page-subtitle">Créez un compte client ou prospect</p>
    </div>
    <form action="{{ route('intranet.organisations.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.organisations._form')
    </form>
</div>
@endsection
