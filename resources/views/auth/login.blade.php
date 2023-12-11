@extends('layouts.app')

@section('content')
<div class="login-page">
    <div class="auth-image">
        <img src="{{ url('assets/auth.png') }}"/>
    </div>
    <div class="auth-form">
        <h1> Welcome back! </h1>
        @include('partials.login-form')
        <a href="{{ route('home') }}"> Continue as Guest</a>
    </div>
@endsection

