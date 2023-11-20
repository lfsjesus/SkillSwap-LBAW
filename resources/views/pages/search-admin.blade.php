@extends('layouts.appLoggedAdmin')
@section('title', 'Search Results')

@section('content')

<section id="user-search-results">
    @each('partials.user-admin', $users, 'user')
</section>

@endsection
