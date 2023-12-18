@extends('layouts.app')

@section('content')
<div class="container recover-password-page">
    <h2>Reset Password</h2>

    @if ($errors->any())
    <div class="error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="auth-form">
        <form method="POST" action="{{ route('send') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="E-mail" autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>
   
</div>
@endsection

