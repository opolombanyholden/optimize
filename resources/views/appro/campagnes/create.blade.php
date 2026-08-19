@extends('layouts.app')
@section('title', 'Nouvelle campagne')
@section('content')
<div class="page-header mb-4"><h1 class="h3 mb-1">Nouvelle campagne d'évaluation</h1></div>
<form action="{{ route('appro.campagnes.store') }}" method="POST">@include('appro.campagnes._form')</form>
@endsection
