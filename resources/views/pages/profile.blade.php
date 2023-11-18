@extends('layouts.appLogged')

@section('title', 'Profile')

@section('content')

<section id="profile">
    <div class="profile-header">
        <div class="profile-picture">
            <img src="{{ url('assets/profile.png') }}"/>
        </div>
        <div class="profile-info">
            <h1>{{ $user->name }}</h1>
            <h2>{{ $user->email }}</h2>
            <h3>{{ $user->bio }}</h3>
        </div>
    </div>
    <div class="profile-content">
        <div class="profile-posts">
            <h1>Posts</h1>
            @each('partials.post', $posts, 'post')
        </div>
        <div class="profile-groups">
            <h1>Groups</h1>
            @each('partials.group', $groups, 'group')
        </div>
    </div>
</section>

@endsection


