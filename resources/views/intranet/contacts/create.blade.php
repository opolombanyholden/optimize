@extends('layouts.app')
@section('title', 'Nouveau contact')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.contacts.index') }}">Contacts</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-user-plus"></i></span>
            Nouveau contact
        </h1>
        <p class="page-subtitle">Ajoutez une personne à votre carnet d'adresses</p>
    </div>

    <form action="{{ route('intranet.contacts.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.contacts._form')
    </form>
</div>
@endsection
