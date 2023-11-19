@extends('layouts.app')

@section('content')
<div class="login-page">
    <div class="auth-image">
        <img src="{{ url('assets/auth.png') }}"/>
    </div>
    <div class="auth-form">
        <h1> Welcome back Admin! </h1>
        @include('partials.admin-login-form')
    <div>
</div>
@endsection