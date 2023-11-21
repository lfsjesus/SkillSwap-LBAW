@extends('layouts.appLoggedAdmin')
@section('title', 'Search Results')

@section('content')

<section id="user-search-results">
    <h1>Search Results</h1>
    @if ($users->isEmpty())
        <p>No results found for "{{ $query }}"</p>
    @else
        <p>Found {{ $users->count() }} results for "{{ $query }}"</p>
    @endif
    @each('partials.user-admin', $users, 'user')
</section>

@endsection
