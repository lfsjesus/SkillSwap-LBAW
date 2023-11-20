@extends('layouts.appLogged')
@section('title', 'Exact Match Search Results')

@section('content')

<section id="posts">
    @include('partials.create-post')
    @each('partials.post', $posts, 'post')
</section>


@endsection