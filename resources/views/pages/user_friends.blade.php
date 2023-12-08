@extends('layouts.appLogged')

@section('title', 'User')

@section('content')
 
<section id="profile" class="profile-section">
    @each('partials.user', $user->get_friends(), 'user')
</section>

@endsection


