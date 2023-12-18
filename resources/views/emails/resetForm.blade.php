@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Reset Password</h2>
    
    @if ($errors->any())
    <div class="alert alert-danger">
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
            <label for="email">Email:</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="E-mail" autofocus>
        </div>
        
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection

