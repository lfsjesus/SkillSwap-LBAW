@extends('layouts.appLogged')
@section('title', 'Search Results')

@section('content')

@php

if ($type == 'user') {
    $partial = 'partials.user';
}
else if ($type == 'post') {
    $partial = 'partials.post';
}
else if ($type == 'group') {
    $partial = 'partials.group';
}
else if ($type == 'comment') {
    $partial = 'partials.comment';
}

@endphp

<section id="search">
    <h1>Search Results</h1>
    <div class="search-filters">
        <div class="search-tabs">
            <a href="{{ route('search', ['q' => $query, 'type' => 'user']) }}" {{ $type == 'user' ? 'class=active' : '' }}>Users</a>
            <a href="{{ route('search', ['q' => $query, 'type' => 'post']) }}" {{ $type == 'post' ? 'class=active' : '' }}>Posts</a>
            <a href="{{ route('search', ['q' => $query, 'type' => 'group']) }}" {{ $type == 'group' ? 'class=active' : '' }}>Groups</a>
            <a href="{{ route('search', ['q' => $query, 'type' => 'comment']) }}" {{ $type == 'comment' ? 'class=active' : '' }}>Comments</a>
        </div>
        <div class="search-sort">
            <span>Sort by:</span>
            <select name="date">
                <option value="asc" {{ $date == 'asc' ? 'selected' : '' }}>Date (asc)</option>
                <option value="desc" {{ $date == 'desc' ? 'selected' : '' }}>Date (desc)</option>
            </select>
            <select name="popularity">
                <option value="asc">Popularity (asc)</option>
                <option value="desc">Popularity (desc)</option>
            </select>
        </div>  
        </div>
        <div class="search-results">
            @if (count($results) > 0)
                @each($partial, $results, $type)
            @else
                <p>No results found for "{{ $query }}"</p>
            @endif
        </div>
    </div>

</section>

@endsection
