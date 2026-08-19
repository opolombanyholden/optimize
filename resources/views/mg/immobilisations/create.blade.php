@extends('layouts.app')
@section('title', 'Nouvelle immobilisation')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item">Moyens Généraux</li>
    <li class="breadcrumb-item"><a href="{{ route('mg.immobilisations.index') }}">Immobilisations</a></li>
    <li class="breadcrumb-item active">Nouvelle</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Nouvelle immobilisation</h1>
    <p class="text-muted mb-0">Enregistrer un bien avec sa base amortissable.</p>
</div>

<form action="{{ route('mg.immobilisations.store') }}" method="POST" enctype="multipart/form-data">
    @include('mg.immobilisations._form')
</form>
@endsection
