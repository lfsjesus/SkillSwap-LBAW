@extends('layouts.appLogged')

@section('title', 'User')

@section('content')
 
<!-- Profile Section -->
<section id="profile" class="profile-section">
    <!-- Profile Header with Background Image -->
    <div class="profile-header">       
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
            <p class="user-title">{{ $user->email }}</p>
        </div>
    </div>
    <!-- Profile Content Grid -->
    <div class="profile-content">
        <!-- Intro Box / Friends -->
        <div class="intro-box">
            <h2>Friends</h2>
            <!-- User Intro Info / Friends List -->
            <!-- Content for friends list goes here -->
        </div>
        <!-- Profile Posts -->
        <section id="posts-box">
            <h2>Posts</h2>
            @each('partials.post', $posts, 'post')
            
        </section>
        <!-- Profile Groups -->
        <div class="groups-box">
            <h2>Groups</h2>
            <!-- Content for groups list goes here -->
        </div>
    </div>

</section>

@endsection


