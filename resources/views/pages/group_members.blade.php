@extends('layouts.appLogged')

@section('title', 'User')

@section('content')
@if($errors->has('add_member'))
    <div class="error">
        {{ $errors->first('add_member') }}
    </div>
@endif

@if(session('success'))
    <div class="success">
        {{ session('success') }}
    </div>
@endif
<div class="users">
    @if(Auth::user() && $group->is_owner(Auth::user()))
        <form action="{{ route('add_member') }}" class="add-user-group user-card" method="POST">
            <p> Add user to group </p>
            {{ csrf_field() }}
            <input type="hidden" name="group_id" value="{{ $group->id }}">
            <div class="user-add-input">
                <input type="text" name="user" placeholder="Username or email">
                <input type="submit" class="material-symbols-outlined" value="add_circle_outline">
            </div>
        </form>
    @endif
    @each('partials.user', $group->get_members(), 'user')
</div>

@endsection



