@extends('layouts.app')
@section('title', 'Nouveau compte de trésorerie')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('finance.comptes.index') }}">Comptes</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-header mb-4">
    <h1 class="h3 mb-1">Nouveau compte de trésorerie</h1>
    <p class="text-muted mb-0">Bancaire, caisse ou wallet électronique.</p>
</div>

<form action="{{ route('finance.comptes.store') }}" method="POST">
    @csrf
    @include('finance.comptes._form')
</form>
@endsection
