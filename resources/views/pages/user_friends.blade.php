@extends('layouts.appLogged')

@section('title', 'User')

@section('content')

<div class="friends">
    @each('partials.group', $user->get_friends(), 'user')
</div>
@endsection


