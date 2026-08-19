@extends('layouts.app')
@section('title', 'Modifier — ' . $courrier->reference)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.courriers.index') }}">Courriers</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span>
            Modifier le courrier
        </h1>
        <p class="page-subtitle">{{ $courrier->reference }} — {{ $courrier->objet }}</p>
    </div>

    <form action="{{ route('intranet.courriers.update', $courrier) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('intranet.courriers._form')
    </form>
</div>
@endsection
