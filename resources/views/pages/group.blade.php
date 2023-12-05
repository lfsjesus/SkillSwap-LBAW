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

    </div>

<!-- Group Content Grid -->
<div class="group-content">
    <!-- Friends and Groups Grid -->
    <div class="members-owners-grid">
        <!-- Members Box -->
        <div class="members-box">
            <h2>Members</h2>
        </div>
        <!-- Groups Box -->
        <div class="owners-box">
            <h2>Owners</h2>
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


