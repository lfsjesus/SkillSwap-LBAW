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

        <!-- Edit Button -->
        

    </div>
    <!-- Profile Content Grid -->
    <div class="profile-content">
       
    </div>
</section>

@endsection


