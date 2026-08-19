@extends('layouts.app')
@section('title', 'Nouvel événement')

@section('breadcrumb')
<ol class="breadcrumb mb-0" style="font-size:.78rem;">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Intranet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('intranet.evenements.index') }}">Événements</a></li>
    <li class="breadcrumb-item active">Nouveau</li>
</ol>
@endsection

@section('content')
<div class="page-intranet">
    <div class="page-header-intranet mb-4">
        <h1 class="page-title">
            <span class="page-title-icon"><i class="fas fa-calendar-plus"></i></span>
            Nouvel événement
        </h1>
        <p class="page-subtitle">Planifiez un événement et invitez des participants</p>
    </div>

    <form action="{{ route('intranet.evenements.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('intranet.evenements._form')
    </form>
</div>
@endsection
