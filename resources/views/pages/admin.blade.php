@extends('layouts.appLoggedAdmin')

@section('title', 'User')

@section('content')
 
<section id="admin">

    <div>
        <h3>Hello, {{$admin->username}}</h1>
        
    </div>
    <div>
        <h3>Users</h1>
        <ul>
            @foreach ($users as $user)
                <li>
                    <a href="{{ route('view-user-admin', ['username' => $user->username]) }}">
                        {{$user->username}}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

</section>


@endsection

