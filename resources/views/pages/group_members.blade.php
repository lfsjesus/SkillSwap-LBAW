@extends('layouts.appLogged')

@section('title', 'User')

@section('content')

<div class="users">
    @each('partials.user', $group->get_members(), 'user')
</div>
@endsection



