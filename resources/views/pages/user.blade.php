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
                @if($user->isPublic() || (Auth::user() instanceof App\Models\User && (Auth::user()->id == $user->id || $user->isFriendWith(Auth::user()))))
                <p class="user-email">
                    <span class="material-symbols-outlined">
                    mail
                    </span>
                    {{ $user->email }}
                </p>
                @endif        
            </div>
            
            @if(Auth::user() instanceof App\Models\User && !$user->deleted)

                @if(Auth::user()->id == $user->id)

                    <a href="{{ route('edit_profile', ['username' => Auth::user()->username]) }}" class="button">
                        <span class='material-symbols-outlined'>
                            edit
                        </span>
                        Edit Profile
                    </a>
                    
                @else
                    
                    @if(Auth::user()->isFriendWith($user))
                        <!-- Button for removing a friend -->
                        <a class="button remove-friend">
                            <input type="hidden" name="friend_id" value="{{ $user->id }}">
                            <span class="material-symbols-outlined">person_remove</span>
                            Remove Friend
                        </a>

                    @else


                        @if(Auth::user()->sentFriendRequestTo($user))
                            <!-- Button for cancelling a sent friend request -->
                            <a class="button cancel-friend-request">
                                <input type="hidden" name="friend_id" value="{{ $user->id }}">
                                <span class="material-symbols-outlined">done</span>
                                Request Sent
                            </a>

                        @elseif($user->sentFriendRequestTo(Auth::user()))
                            <!-- Button for accepting a received friend request -->
                            <a class="button accept-friend-request">
                                <input type="hidden" name="sender_id" value="{{ $user->id }}">
                                <span class="material-symbols-outlined">person_add</span>
                                Accept Request
                            </a>

                        @else
                            <!-- Button for sending a new friend request -->
                            <a class="button add-friend">
                                <input type="hidden" name="friend_id" value="{{ $user->id }}">
                                <span class="material-symbols-outlined">person_add</span>
                                Add Friend
                            </a>

                        @endif
                        
                    @endif

                @endif

            @endif
        </div>
        <p class="user-description">
            {{ $user->description }}
        </p>
        
    </div>

    <div class="profile-content">
        @if($user->isPublic() || (Auth::user() instanceof App\Models\User && (Auth::user()->id == $user->id || $user->isFriendWith(Auth::user()))))
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
        @else
        <div class="private-profile">
            @if ($user->deleted)
                <span class="material-symbols-outlined">person</span>
                <p> This user no longer exists </p>
                <p> You can only see his public posts </p>
            @else
            <span class="material-symbols-outlined">lock</span> 
            <p> This user's profile is private </p>
            <p> You must be friend of this user to see all the information </p>
            @endif
        </div>
        @endif
        <!-- Posts Section -->
        <section id="posts">
            <h2>Posts</h2>
            @if (count($user->posts) == 0)
            <p> This user does not have posts </p>
            @else
            @php
            $userPosts = ($user->isPublic() || 
                        (Auth::user() instanceof App\Models\User && (Auth::user()->id == $user->id || 
                        $user->isFriendWith(Auth::user())))) ? $user->posts : $user->publicPosts;
        
            $visiblePosts = (Auth::user() instanceof App\Models\User) ? Auth::user()->visiblePosts()->pluck('id')->toArray() : $user->publicPosts()->pluck('id')->toArray();

            $posts = $userPosts->whereIn('id', $visiblePosts);
            @endphp
            @foreach($posts as $post)
                @include('partials.post', ['post' => $post,  'limit' => true, 'limitCommentReplies' => true])
            @endforeach
            @endif
        </section>

    </div>

</section>

@endsection


