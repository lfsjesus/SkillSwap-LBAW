@extends('layouts.appLogged')

@section('title', 'Group')

@section('content')
 
<!-- Group Section -->
<section id="profile" class="profile-section">
    <!-- Group Header with Background Image -->
    <div class="profile-header">       
        <div class="header-background">
            <img src="{{url('assets/blob-background.jpg')}}" alt="Background Picture">
        </div>

        <!-- Group Info -->
        <div class="group-info">
            <h1 class="group-name">{{ $group->name }}</h1>
            <p class="group-description">{{ $group->description }}</p>
        </div>        

        @if(Auth::user())
        @if(Auth::user()->is_owner($group))
            <!-- User is the owner of the group -->
            <a href="{{ route('edit_group', ['group_id' => $group->id]) }}" class="button">
                <span class='material-symbols-outlined'>
                    edit
                </span>
                Edit Group
            </a>

        @elseif(Auth::user()->is_member($group))
            <!-- User is a member of the group, but not the owner -->
            <a href="{{ route('leave_group', ['group_id' => $group->id]) }}" class="button">
                <span class="material-symbols-outlined">
                    exit_to_app
                </span>
                Exit Group
            </a>

        @else
            <!-- User is not a member of the group -->
            <a href="{{ route('join_group', ['group_id' => $group->id]) }}" class="button">
                <span class="material-symbols-outlined">
                    group_add
                </span>
                Adhere to Group
            </a>

        @endif
    @endif


    </div>

<!-- Group Content Grid -->
<div class="group-content">
    <!-- Members and Groups Grid -->
    <div class="members-owners-grid">
        <!-- Members Box -->
        <div class="members-box">
            <h2>Members</h2>
                @each('partials.user', $group->get_members(), 'user')
        </div>
        <!-- Groups Box -->
        <div class="owners-box">
            <h2>Owners</h2>
                @each('partials.user', $group->get_owners(), 'user')
        </div>
    </div>

    <!-- Posts Section -->
    <section id="posts">
        <h2>Posts</h2>
        @if(Auth::user())
        @include('partials.create-group-post', ['group' => $group])
        @endif    
        @each('partials.post', $group->posts, 'post')
    </section>
</div>
</section>

@endsection


