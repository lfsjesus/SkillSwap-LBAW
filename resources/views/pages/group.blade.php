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

        <!-- Profile Picture -->
        <div class="profile-picture">
            @if($group->banner)
            <img src="{{stream_get_contents($group->banner)}}"/>
            @else
            <img src="{{ url('assets/profile-picture.png') }}"/>
            @endif
        </div>
        <!-- Profile Info -->
        <div class="profile-info">
            <h1 class="user-name">{{ $group->name }}</h1>
        </div>

        <!-- Edit Button -->
        

    </div>
    <!-- Profile Content Grid -->
    <div class="profile-content">
       
    </div>
</section>

@endsection


