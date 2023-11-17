@extends('layouts.appLogged')
@section('title', 'Posts')

@section('content')

<section id="posts">
    @include('partials.create-post')
    @each('partials.post', $posts, 'post')
</section>

@endsection