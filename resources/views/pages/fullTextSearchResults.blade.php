@extends('layouts.appLogged')
@section('title', 'Full Text Search Results')

@section('content')

<section id="user-search-results">
    @each('partials.user', $users, 'user')
</section>

@endsection
