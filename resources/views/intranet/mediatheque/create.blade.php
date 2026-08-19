@extends('layouts.app')
@section('title', 'Ajouter un média')
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.mediatheque.index') }}">Médiathèque</a></li>
    <li class="breadcrumb-item active">Ajouter</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-cloud-arrow-up"></i></span>
            Ajouter un média
        </h1>
    </div>
    <form action="{{ route('intranet.mediatheque.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.mediatheque._form')
    </form>
</div>
@endsection
