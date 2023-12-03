@extends('layouts.appLogged')

@section('title', 'Group')

@section('content')
 
<!-- Profile Section -->
<section id="profile" class="profile-section">
    <!-- Profile Header with Background Image -->
    <div class="profile-header">       
        <div class="header-background">
            <img src="{{url('assets/blob-background.jpg')}}" alt="Background Picture">
        </div>

        <!-- Profile Info -->
        <div class="profile-info">
            <h1 class="group-name">{{ $group->name }}</h1>
            <p class="group-description">{{ $group->description }}</p>
        </div>

        <!-- Group Content Grid -->
        <div class="group-content">
            <!-- Friends and Groups Grid -->
                <div class="member-box">
                    <h2>Members</h2>
             
                </div>
            </div>
        

    </div>
    <!-- Profile Content Grid -->
    <div class="profile-content">

       
    </div>

    <!-- Posts Section -->
    <section id="posts">
        <h2>Posts</h2>
        @each('partials.post', $group->posts, 'post')
    </section>
</section>

@endsection


