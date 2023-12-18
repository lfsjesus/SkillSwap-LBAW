@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')
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
    $partial = 'partials.comment-search-card';
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
                <option value="asc" {{ $popularity == 'asc' ? 'selected' : '' }}>Popularity (asc)</option>
                <option value="desc" {{ $popularity == 'desc' ? 'selected' : '' }}>Popularity (desc)</option>
            </select>
        </div>  
        </div>
        <div class="search-results">
            @if ($results->isEmpty())
                <span>No results found for "{{ $query }}"</span>  
            @else
                <span>Found {{ $results->count() }} results for "{{ $query }}" :</span>
                @each($partial, $results, $type)
            @endif
        </div>
    </div>

</section>

@endsection
