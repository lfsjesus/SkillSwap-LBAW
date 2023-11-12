@extends('layouts.app')

@section('content')
<div class="login-page">
  <div class="auth-image">
      <img src="{{ url('assets/auth.png') }}"/>
  </div>
  <div class="auth-form">
  @include('partials.register-form')
</div>
</div>

@endsection