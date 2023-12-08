@extends('layouts.appLogged')

@section('title', 'User')

@section('content')

<div class="friends">
    @each('partials.user', $group->get_members(), 'user')
</div>
@endsection



