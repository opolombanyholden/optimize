@extends('layouts.app')
@section('title', 'Nouveau rapport')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.rapports.index') }}">Rapports & CR</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title"><span class="page-title-icon"><i class="fas fa-file-lines"></i></span> Nouveau rapport</h1>
        <p class="page-subtitle">Rédigez un rapport, compte rendu, PV ou note</p>
    </div>
    <form action="{{ route('intranet.rapports.store') }}" method="POST" enctype="multipart/form-data">
        @csrf @include('intranet.rapports._form')
    </form>
</div>
@endsection
