@extends('layouts.appLogged')

@section('title', 'Group')

@section('content')
 
@if (session('success'))
<p class="success">
    {{ session('success') }}
</p>
@endif
@if (session('error'))
    <p class="error">
        {{ session('error') }}
    </p>
@endif
<!-- Group Section -->
<section id="group" class="group-section">
    <!-- Group Header with Background Image -->
    <div class="group-header">       
        <div class="header-background">
            @if($group->banner)
            <img src="{{stream_get_contents($group->banner)}}"/>
            @else
            <img src="{{url('assets/blob-background.jpg')}}" alt="Background Picture">
            @endif
        </div>

        <!-- Group Info -->
        <div class="group-information">
            <div class="group-info">
                <h1 class="group-name">{{ $group->name }}</h1>
                <p class="group-description">{{ $group->description }}</p>
            </div>        

            <div class="group-buttons">
                @if(Auth::user())
                    @if($group->is_owner(Auth::user()))
                        <!-- User is the owner of the group -->
                        <a href="{{ route('edit_group_form', ['id' => $group->id]) }}" class="button">
                            <span class='material-symbols-outlined'>
                                edit
                            </span>
                            Edit Group
                        </a>
                        <a href="" class="button">
                            <span class="material-symbols-outlined">
                                exit_to_app
                            </span>
                            Exit Group
                        </a>

                    @elseif($group->is_member(Auth::user()))
                        <!-- User is a member of the group, but not the owner -->
                        <a href="" class="button">
                            <span class="material-symbols-outlined">
                                exit_to_app
                            </span>
                            Exit Group
                        </a>

                    @else
                        <!-- User is not a member of the group -->
                        <a href="" class="button">
                            <span class="material-symbols-outlined">
                                group_add
                            </span>
                            Join Group
                        </a>

                    @endif
                @endif
            </div>
        </div>

    </div>

<!-- Group Content Grid -->
<div class="group-content">
    <!-- Members and Groups Grid -->
    <div class="members-owners-grid">
        <!-- Members Box -->
        <div class="members-box">
            <h2>Members</h2>
            @if ($group->get_members()->isEmpty())
                <p>This group has no members.</p>
            @else
                @each('partials.user', $group->get_members()->take(2), 'user')
                <div class="spacer"></div>
                <div class="see-more-container">
                    <a href="{{ route('group_members', ['groupId' => $group->id]) }}" class="see-more-button">See All Members</a>
                </div>
            @endif
        </div>
        <!-- Groups Box -->
        <div class="owners-box">
            <h2>Owners</h2>
            @if ($group->get_owners()->isEmpty())
                <p>This group has no owners.</p>
            @else
                @each('partials.user', $group->get_owners()->take(2), 'user')
                <div class="spacer"></div>
                <div class="see-more-container">
                    <a href="{{ route('group_owners', ['groupId' => $group->id]) }}" class="see-more-button">See All Owners</a>

                </div>
            @endif
        </div>
    </div>

    <!-- Posts Section -->
    <section id="posts">
        <h2>Posts</h2>
        @if(Auth::user())
        @include('partials.create-post', ['group' => $group])
        @endif    
        @each('partials.post', $group->posts, 'post')
    </section>
</div>
</section>

@endsection


