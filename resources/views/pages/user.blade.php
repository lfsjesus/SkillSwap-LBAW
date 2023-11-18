@extends('layouts.appLogged')

@section('title', 'User')

@section('content')
 
<!-- Profile Section -->
<section id="profile" class="profile-section">
    <!-- Profile Header with Background Image -->
    <div class="profile-header">
        <h1>{{ $user->name }}</h1>
        <h2>{{ $user->email }}</h2>
        <h3>{{ $user->bio }}</h3>
       
        <div class="header-background">
            <img src="{{url('assets/blob-background.jpg')}}" alt="Background Picture">
        </div>

        <!-- Profile Picture -->
        <div class="profile-picture">
            @if(Auth::user()->profile_picture)
            <img src="{{stream_get_contents(Auth::user()->profile_picture)}}"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif
        </div>
        <!-- Profile Info -->
        <div class="profile-info">
            <h1 class="user-name">{{ $user->name }}</h1>
            <p class="user-title">{{ $user->position }}</p>
        </div>
    </div>
    <!-- Profile Content Grid -->
    <div class="profile-content">
        <!-- Intro Box -->
        <div class="intro-box">
            <!-- User Intro Info -->
            <div class="user-intro">
                <p class="user-website"><a href="#" target="_blank">{{ $user->website }}</a></p>
                <!-- Additional info like gender, birthday, location -->
            </div>
        </div>
        <!-- Profile Posts -->
        <div class="posts-box">
            <h2>Posts</h2>
            @each('partials.post', $posts, 'post')
        </div>
        <!-- Profile Groups -->
        <div class="groups-box">
            <h2>Groups</h2>
         
        </div>
    </div>
</section>

@endsection


