@extends('layouts.app')
@section('title', 'Modifier — ' . $contact->nom_complet)

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.contacts.index') }}">Contacts</a></li>
    <li class="breadcrumb-item active">Modifier</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-pen-to-square"></i></span>
            Modifier le contact
        </h1>
        <p class="page-subtitle">{{ $contact->nom_complet }}</p>
    </div>

    <form action="{{ route('intranet.contacts.update', $contact) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('intranet.contacts._form')
    </form>
</div>
@endsection
