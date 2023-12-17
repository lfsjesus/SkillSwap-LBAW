@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Contact Us</h2>
    <form method="POST" action="{{ route('send') }}">
        @csrf
        
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection