@extends('layouts.app')
@section('title', 'Modifier — ' . $organisation->nom)
@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.organisations.index') }}">Organisations</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection
@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span>
            Modifier l'organisation
        </h1>
        <p class="page-subtitle">{{ $organisation->nom }}</p>
    </div>
    <form action="{{ route('intranet.organisations.update', $organisation) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('intranet.organisations._form')
    </form>

    {{-- Bloc fonctionnel documents (upload/delete) — hors form pour éviter form imbriqués --}}
    @include('intranet.organisations._partials.documents-attendus')
</div>
@endsection
