@extends('layouts.appLoggedAdmin')

@section('title', 'User')

@section('content')
 
<section id="admin">

    <div class="greeting">
        <h3>Hello, <span class="yellow">{{$admin->username}}</span></h1>
        
    </div>
    <div class="users">

        @foreach ($users as $user)
            <div class="user-card">
                <a href="{{ route('view-user-admin', ['username' => $user->username]) }}">
                    @if($user->profile_picture)
                    <img src="{{stream_get_contents($user->profile_picture)}}"/>
                    @else
                    <img src="{{ url('assets/profile-picture.png') }}"/>
                    @endif
                    {{$user->username}}

                </a>

            </div>
        @endforeach

    </div>


    </div>

</section>


@endsection

