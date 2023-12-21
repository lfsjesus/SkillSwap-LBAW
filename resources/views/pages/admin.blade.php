@extends('layouts.appLoggedAdmin')

@section('title', 'User')

@section('content')
 

@if (session('success'))
<p class="success">
    {{ session('success') }}
</p>
@endif
@if (session('error'))
<p class="error">
    {{ session('error') }}
</p>
@endif
<section id="admin">
    <div class="greeting">
        <h3>Hello, <span class="yellow">{{$admin->username}}</span></h3>
    </div>
    <button><a href="{{route('create-user-form-admin')}}"><span class='material-symbols-outlined'>add_circle</span> user</a></button>
    <div class="users">
        @each('partials.user-admin', $users, 'user')
    </div>
    </div> 
</section>
    {{$users->links()}} <!-- use Laravel's default pagination mode. -->

@endsection

