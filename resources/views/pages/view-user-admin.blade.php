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
            <img src="{{stream_get_contents($user->profile_picture)}}" alt="profile picture"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}" alt="profile picture"/>
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
            <div class="profile-buttons">
                <a href="{{ route('edit-user-form-admin', ['username' => $user->username]) }}" class="button">
                    <span class='material-symbols-outlined'>
                        edit
                    </span>
                    Edit Profile
                </a>
                @if ($user->isBanned())
                <a class="button unban-user" href="javascript:void(0);">
                    <span class='material-symbols-outlined'>
                        person_add_disabled
                    </span>
                    Unban
                </a>
                @else
                <a class="button ban-user" href="javascript:void(0);">
                    <span class='material-symbols-outlined'>
                        block
                    </span>
                    Ban
                </a>
                @endif
            </div>
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
                @if ($user->get_friends()->isEmpty())
                <p> This user does not have friends </p>
                @else 
                @each('partials.user', $user->get_friends()->take(2), 'user')
                <div class="spacer"></div>
                <div class="see-more-container">
                    <a href="{{ route('user_friends', ['username' => $user->username]) }}" class="see-more-button">See All Friends</a>
                </div>
                @endif
            </div>
            <!-- Groups Box -->
            <div class="groups-box">
                <h2>Groups</h2>
                @if ($user->get_groups()->isEmpty())
                <p> This user does not belong to any group </p>
                @else
                @each('partials.group', $user->get_groups()->take(2), 'group')
                <div class="spacer"></div>
                <div class="see-more-container">
                    <a href="{{ route('user_groups', ['username' => $user->username]) }}" class="see-more-button">See All Groups</a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Posts Section -->
        <section id="posts">
            <h2>Posts</h2>
            @if (count($posts) == 0)
            <p> This user does not have posts </p>
            @else
            @foreach($posts as $post)
            @include('partials.post', ['post' => $post,  'limit' => true, 'limitCommentReplies' => true])
            @endforeach
            @endif
        </section>
    </div>
</section>

@endsection


