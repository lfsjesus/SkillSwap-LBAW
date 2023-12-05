@extends('layouts.appLoggedAdmin')

@section('title', 'User')

@section('content')
 
<section id="admin">
    <button><a href="{{route('create-user-form-admin')}}"><span class='material-symbols-outlined'>add_circle</span> user</a></button>
    <div class="users">
        @each('partials.group', $groups, 'group')
    </div>
    </div> 
</section>
    {{$groups->links()}} <!-- use Laravel's default pagination mode. -->

@endsection
