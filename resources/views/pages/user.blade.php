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
        <!-- Friends and Groups Grid -->
        <div class="friends-groups-grid">
            <!-- Friends Box -->
            <div class="friends-box">
                <h2>Friendsssssssssssssssssss</h2>
                <!-- Content for friends list goes here -->
            </div>
            <!-- Groups Box -->
            <div class="groups-box">
                <h2>Groupssssssssssssssssssssssss</h2>
                <!-- Content for groups list goes here -->
            </div>
        </div>
        
        <!-- Posts Section -->
        <section id="posts">
            <h2>Posts</h2>
            @each('partials.post', $posts, 'post')
        </section>
    </div>
</section>

@endsection


