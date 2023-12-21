@extends('layouts.appLogged')
@section('title', 'Posts')

@section('content')
<div class="greeting">
    @if(Auth::user())
        <h3>Hello, <span class="yellow">{{Auth::user()->name}}</span></h3>
    
    @else
        <h3>Hello, <span class="yellow">Guest</span></h3>
    @endif

</div>
<section id="posts">
    @if(Auth::user())
        @include('partials.create-post')
    @endif    
    @each('partials.post', $posts, 'post')
</section>
<div class="posts-pagination">
{{ $posts->links()}}
<!--Loader-->
<div class="loader"></div>
</div>


@endsection