@extends('layouts.appLogged')

@section('title', 'User')

@section('content')

<div class="users">
    @each('partials.user', $user->get_friends(), 'user')
</div>
@endsection


