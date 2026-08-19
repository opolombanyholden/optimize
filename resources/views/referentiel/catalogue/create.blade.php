@extends('layouts.app')
@section('title', 'Nouvel article')
@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Nouvel article — catalogue</h1></div>
<form action="{{ route('referentiel.catalogue.store') }}" method="POST">@include('referentiel.catalogue._form')</form>
@endsection
