@extends('layouts.appLogged')

@section('title', 'User')

@section('content')
 
<!-- Profile Section -->
<section id="profile" class="profile-section">
    <!-- Profile Header with Background Image -->
    @if (session('success'))
    <p class="success">
        {{ session('success') }}
    </p>
    @endif
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
            
            @if(Auth::user())
                @if(Auth::user()->username == $user->username)
                <a href="{{ route('edit_profile', ['username' => Auth::user()->username]) }}" class="button">
                    Edit Profile
                </a>
                @endif

                @if(Auth::user()->username != $user->username)
                    <!-- Add Friend Button -->
                    @if(Auth::user()->is_friend($user))
                        <a href="" class="button">
                            <span class="material-symbols-outlined">
                                person_remove
                            </span>
                            Remove Friend
                        </a>

                    @else

                        <a href="" class="button">
                            <span class="material-symbols-outlined">
                                person_add
                            </span>
                            Add Friend
                        </a>

                    @endif
                @endif    

            @endif
        </div>
        <p class="user-description">
            {{ $user->description }}
        </p>
        <!-- Edit Button -->
        

    </div>
    <!-- Profile Content Grid -->
    <div class="profile-content">
        <!-- Friends and Groups Grid -->
        <div class="friends-groups-grid">
            <!-- Friends Box -->
            <div class="friends-box">
                <h2>Friends</h2>
                @each('partials.user', $user->get_friends(), 'user')
            </div>
            <!-- Groups Box -->
            <div class="groups-box">
                <h2>Groups</h2>
                @foreach ($groups as $group)
                <div class="user-card">
                    <a href="{{ route('group', ['id' => $group->id]) }}">
                        @if($group->banner)
                        <img src="{{stream_get_contents($group->banner)}}"/>
                        @else
                        <img src="{{ url('assets/group.png') }}"/>
                        @endif
                        {{$group->name}}
                    </a>
    
                </div>
                @endforeach
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


