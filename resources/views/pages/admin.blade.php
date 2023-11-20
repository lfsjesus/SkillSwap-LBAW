@extends('layouts.appLoggedAdmin')

@section('title', 'User')

@section('content')
 
<section id="admin">

    <div class="greeting">
        <h3>Hello, <span class="yellow">{{$admin->username}}</span></h1>
    </div>
    <button><a href="{{route('create-user-form-admin')}}"><span class='material-symbols-outlined'>add_circle</span> user</a></button>
    <div class="users">
        @each('partials.user-admin', $users, 'user')
    </div>


    </div>

</section>


@endsection

