@extends('layouts.appLoggedAdmin')

@section('title', 'User')

@section('content')


 
<!-- Profile Section -->
<section id="profile" class="profile-section">
    @if (session('success'))
    <p class="success">
        {{ session('success') }}
    </p>
    @endif
    <!-- Profile Header with Background Image -->
    <div class="profile-header">       
        <div class="header-background">
            <img src="{{url('assets/blob-background.jpg')}}" alt="Background Picture">
        </div>

        <!-- Profile Picture -->
        <div class="profile-picture">
            @if($user->profile_picture)
            <img src="{{stream_get_contents($user->profile_picture)}}"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif
        </div>

        <div class="profile-information">
            <!-- Profile Info -->
            <div class="profile-info">
                <div class="user-flex">
                    <h1 class="user-name">{{ $user->name }}</h1>
                    <span class="username"> &#64{{$user->username}} </span>
                    
                </div>
                <p class="user-email">
                    <span class="material-symbols-outlined">
                    mail
                    </span>
                    {{ $user->email }}
                </p>

        
            </div>
            
            <a href="{{ route('edit-user-form-admin', ['username' => $user->username]) }}" class="button">
                <span class='material-symbols-outlined'>
                    edit
                </span>
                Edit Profile
            </a>
        </div>
        <p class="user-description">
            {{ $user->description }}
        </p>

    </div>
    <!-- Profile Content Grid -->
    <div class="profile-content">
        <!-- Friends and Groups Grid -->
        <div class="friends-groups-grid">
            <!-- Friends Box -->
            <div class="friends-box">
                <h2>Friends</h2>
                @if (count($user->get_friends()) == 0)
                <p> This user does not have friends </p>
                @else
                @each('partials.user', $user->get_friends(), 'user')
                @endif
            </div>
            <!-- Groups Box -->
            <div class="groups-box">
                <h2>Groups</h2>
                @if (count($user->get_groups()) == 0)
                <p> This user does not belong to any group </p>
                @else
                @each('partials.group', $user->get_groups(), 'group') 
                @endif
            </div>
        </div>
        
        <!-- Posts Section -->
        <section id="posts">
            <h2>Posts</h2>
            @if (count($posts) == 0)
            <p> This user does not have posts </p>
            @else
            @each('partials.post-admin', $posts, 'post')
            @endif
        </section>
    </div>
</section>

@endsection


