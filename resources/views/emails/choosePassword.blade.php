@extends('layouts.app')

@section('content')
<div class="container recover-password-page">
    <div class="auth-image">
        <img src="{{ url('assets/auth.png') }}"/>
    </div>

    <div class="auth-form">
        <h1>Choose Password</h1>
        <br>
        
        @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <form method="POST" action="{{ route('send') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">New Password:</label>
                <input id="password" type="password" name="password" required placeholder="New Password" autofocus>
            </div>

            <div class="form-group">
                <label for="email">Confirm Password:</label>
                <input id="password" type="password" name="password" required placeholder="Confirm Password" autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>   
</div>
@endsection

