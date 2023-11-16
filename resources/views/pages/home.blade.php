@extends('layouts.appLogged')
@section('title', 'Posts')

@section('content')

<section id="posts">
    @each('partials.post', $posts, 'post')
</section>

@endsection