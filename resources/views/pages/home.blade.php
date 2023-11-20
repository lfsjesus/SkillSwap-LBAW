@extends('layouts.appLogged')
@section('title', 'Posts')

@section('content')

<section id="posts">
    @if(Auth::user())
        @include('partials.create-post')
    @endif    
    @each('partials.post', $posts, 'post')
</section>

@endsection